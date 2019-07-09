<?php

    // Set up RSMS application
    require_once '/var/www/html/rsms/Application.php';

    const EXTRAS_FILE = '/var/rsms/conf/.rsms.erasmus.my.cnf';
    const OPT_EXECUTE = 'EXECUTE';
    const OPT_SKIP    = 'SKIP';
    const OPT_IGNORE  = 'IGNORE';

    $LINE = "\n";

    $whitelisting = false;
    $runScriptNames = null;
    if( $argc > 1 ){
        $runScriptNames = array_slice($argv, 1);
        $whitelisting = !empty($runScriptNames);
    }

    class Migration {
        public $version;
        public $script;
        public $date;

        public function __toString(){
            return "[v$this->version : $this->date : $this->script]";
        }
    }

    function getVersionId( $scriptFileName ){
        $v = array();
        if( !preg_match('/^([\d\.]+)_/', $scriptFileName, $v) ){
            return null;
        }

        return $v[1];
    }

    function run_script( $scriptpath ){
        if( file_exists($scriptpath) ){
            // Read ip/db from app config
            $ip = ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_DB_HOST);
            $db = ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_DB_NAME);
            $EXTRAS_FILE = EXTRAS_FILE;

            $output = null;
            $exitcode = null;
            echo ("Executing '$scriptpath'...\n");
            exec("mysql --defaults-file=$EXTRAS_FILE -h $ip $db < $scriptpath", $output, $exitcode) . "\n";
            echo "\n    ----- OUTPUT -----\n";
            foreach( $output as $ln ){
                echo "    $ln\n";
            }
            echo "    ------------------\n";
            echo ("Completed $scriptpath: exit code '$exitcode'\n");

            return $exitcode == 0;
        }
        throw new Exception("File '$scriptpath' does not exist");
    }

    function save_migration( Migration $migration ){
        $stmt = DBConnection::prepareStatement( "INSERT INTO devops_migration (`version`, `script`, `date`) VALUES (?, ?, ?)" );
        $stmt->bindValue(1, $migration->version);
        $stmt->bindValue(2, $migration->script);
        $stmt->bindValue(3, $migration->date);

        $result = $stmt->execute();
        if( !$result ){
            echo (var_dump( $stmt->errorInfo()));
        }

        return $result;
    }

    $envname = ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_ENV_NAME, '');

    $db_ip   = ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_DB_HOST);
    $db_name = ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_DB_NAME);

    $db_desc = "$db_ip:$db_name";
    $db_padding = str_pad('', strlen($db_desc), '-');

    print "
-------------------------------$db_padding
| RSMS Database Migrations
| $envname
| $db_ip:$db_name
-------------------------------$db_padding
";

    // Run / Check DB migrations

    // Step 0: Ensure migrations table exists
    echo("Verifying migration table... ");
    $VERIFY_OR_CREATE_MIGRATION_TABLE = "CREATE TABLE IF NOT EXISTS `devops_migration` (
        `version` varchar(12) NOT NULL,
        `script` varchar(128) NOT NULL,
        `date` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`version`)
    );";
    $stmt = DBConnection::prepareStatement($VERIFY_OR_CREATE_MIGRATION_TABLE);
    if( !$stmt->execute() ){
        echo ("Unable to verify or create migration table$LINE");
        echo (var_dump($stmt->errorInfo()));
        exit("Unable to verify or create migration table");
    }

    // Step 1: Check migrations table
    echo("Scanning database for executed migrations... ");
    $sql = "SELECT * FROM devops_migration";
    $stmt = DBConnection::prepareStatement($sql);
    if( !$stmt->execute() ){
        echo ("Unable to query migration table$LINE");
        echo (var_dump($stmt->errorInfo()));
        exit("Failed to read from migration table");
    }

    $migrations = $stmt->fetchAll(PDO::FETCH_CLASS, Migration::class);
    echo( count($migrations) . " found$LINE");
    foreach( $migrations as $migration ){
        echo( "  $migration $LINE");
    }

    // Step 2: Look at migration scripts
    $scripts = array();
    $cwd = getcwd();
    echo($LINE . "Scanning $cwd for migration scripts... ");

    $dirContents = scandir( $cwd );
    foreach( $dirContents as $path ){
        if( is_file($path) ){
            $scripts[] = $path;
        }
    }

    sort($scripts);

    if( empty($scripts) ){
        echo("No migration scripts to run in $cwd $LINE");
        exit(0);
    }

    echo( count($scripts) . " found:$LINE");

    // Use $runScriptNames as a whitelist for existing Scripts
    if( !empty($runScriptNames) ){
        $pre_count = count($scripts);
        echo("Filter scripts using parameter whitelist...$LINE");
        $scripts = array_filter($scripts, function($s) use ($runScriptNames){
            return in_array($s, $runScriptNames);
        });

        $post_count = count($scripts);

        echo("Retained $post_count/$pre_count scripts$LINE");
    }

    // Step 3: Determine which scripts have been run
    echo("Filter executed scripts...$LINE");
    $migration_script_names = array_map( function($m){ return $m->script; }, $migrations);
    $scripts_to_execute = array_filter(
        $scripts, function($s) use ($migration_script_names){
            return !in_array($s, $migration_script_names);
        }
    );

    echo($LINE . count($scripts_to_execute) . ($whitelisting ? ' whitelisted' : '') . " migrations need executing:$LINE");
    echo( "  " . implode("$LINE  ", $scripts_to_execute) . $LINE);

    // Validate migration script Version/ID values
    $migration_script_ids = array_map( function($m){ return $m->version; }, $migrations);
    $invalid_script_names = array_filter(
        $scripts_to_execute, function($s) use ($migration_script_ids){
            // If version/id already exists, we can't reliably execute this.
            return in_array(
                getVersionId($s),
                $migration_script_ids
            );
        }
    );

    if( !empty( $invalid_script_names) ){
        echo($LINE . "ERROR: " . count($invalid_script_names) . " unexecuted scripts have conflicting version/id values$LINE");
        echo( "  " . implode("$LINE  ", $invalid_script_names) . $LINE);
        echo($LINE . "ERROR: Cannot perform migrartion operations until script version/ids are made unique$LINE");
        exit("Inavlid migration script numbers");
    }

    if( empty($scripts_to_execute) ){
        // Nothing left to do
        exit(0);
    }

    // Step 4: Execute the scripts and update migration table
    print "
Processing unexecute migrations. Options are:
    [R]un:     Run the migration script against the database and Update the migration table
    [U]pdate:  Ignore the migration script and Update the migration table
    [S]kip:    Ignore the migration script and do not Update the migration table
";

    foreach( $scripts_to_execute as $script ){
        $v = getVersionId( $script );

        if( !isset($v) ){
            echo( "Could not find version number for script '$script'$LINE");
            continue;
        }

        $migration = new Migration();
        $migration->version = $v;
        $migration->script = $script;
        $migration->date = date("Y-m-d H:i:s");

        // Confirm that script should be executed
        // TODO: Allow user to:
        //   [R]un script
        //   [U]pdate without execution
        //   [S]kip script
        $operation = null;
        $response = null;
        while($operation == null){
            echo($LINE . "[R]un, [U]pdate, [S]kip file: $migration->script? ");
            $response = trim( fgets(STDIN) );
            switch( strtoupper($response) ){
                case 'S':
                case 'SKIP': {
                    $operation = OPT_SKIP;
                    break;
                }
                case 'R':
                case 'RUN': {
                    // Confirmed execute script
                    $operation = OPT_EXECUTE;
                    break;
                }
                case 'U':
                case 'UPDATE': {
                    $operation = OPT_IGNORE;
                    break;
                }
                default:{
                    echo "Invalid Option '$response'$LINE";
                    $operation = null;
                    break;
                }
            }
        }

        // Determine operations
        $_execute_script = false;
        $_update_devopts_table = false;

        switch( $operation ){
            case OPT_EXECUTE: {
                $_execute_script = true;
                $_update_devopts_table = true;
                break;
            }
            case OPT_IGNORE: {
                $_execute_script = false;
                $_update_devopts_table = true;
                break;
            }
            case OPT_SKIP: {
                $_execute_script = false;
                $_update_devopts_table = false;
                break;
            }
        }

        if( $_execute_script ){
            echo "Execute $migration->script $LINE";
            // Run the script
            if( !run_script($migration->script) ){
                echo( "Error executing migration file: $migration->script $LINE");
                exit("Error executing migration file: $migration->script");
            }
        }

        if( $_update_devopts_table ){
            // Update the migration table
            echo( "Saving migration entry: $migration...");
            if( save_migration($migration) ){
                echo( "Saved$LINE");
            }
            else{
                echo("UNABLE TO SAVE$LINE");
            }
        }

        // Completed processing of $migration
        echo "Completed processing of $migration->script" . $LINE;
    }

?>
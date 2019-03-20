<?php

    // Set up RSMS application
    require_once '/var/www/html/rsms/Application.php';

    const EXTRAS_FILE = '/var/rsms/conf/.rsms.erasmus.my.cnf';
    $LINE = "\n";

    class Migration {
        public $version;
        public $script;
        public $date;

        public function __toString(){
            return "[v$this->version : $this->date : $this->script]";
        }
    }

    function run_script( $scriptpath ){
        if( file_exists($scriptpath) ){
            // TODO: Read ip/db from app config
            $ip = '127.0.0.1';
            $db = 'usc_ehs_rsms';
            $un = ApplicationConfiguration::get('server.db.username');
            $EXTRAS_FILE = EXTRAS_FILE;

            $output = null;
            $exitcode = null;
            echo ("Executing $scriptpath...\n");
            echo exec("mysql $db < $scriptpath", $output, $exitcode) . "\n";
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

    // Run / Check DB migrations

    // Step 1: Check migrations table
    echo("Scan for executed migrations$LINE");
    $sql = "SELECT * FROM devops_migration";
    $stmt = DBConnection::prepareStatement($sql);
    if( !$stmt->execute() ){
        echo ("Unable to query migration table$LINE");
        echo (var_dump($stmt->errorInfo()));
        die;
    }

    $migrations = $stmt->fetchAll(PDO::FETCH_CLASS, Migration::class);
    echo( count($migrations) . " existing migrations found$LINE");
    foreach( $migrations as $migration ){
        echo( "  $migration $LINE");
    }

    // Step 2: Look at migration scripts
    echo("Scan for migration scripts...$LINE");
    $scripts = array();
    $cwd = getcwd();
    $dirContents = scandir( $cwd );
    foreach( $dirContents as $path ){
        if( is_file($path) ){
            $scripts[] = $path;
        }
    }

    sort($scripts);

    if( empty($scripts) ){
        echo("No migration scripts to run in $cwd $LINE");
        die;
    }

    echo( count($scripts) . " migration scripts found:$LINE");

    // Step 3: Determine which scripts have been run
    $migration_script_names = array_map( function($m){ return $m->script; }, $migrations);
    $scripts_to_execute = array_filter( $scripts, function($s) use ($migration_script_names){ return !in_array($s, $migration_script_names); });

    echo( count($scripts_to_execute) . " migrations need executing:$LINE");
    echo( "  " . implode("$LINE  ", $scripts_to_execute) . $LINE);

    // Step 4: Execute the scripts and update migration table
    foreach( $scripts_to_execute as $script ){
        $v = array();
        if( !preg_match('/^(\d+)_/', $script, $v) ){
            echo( "Could not find version number for script '$script'$LINE");
            continue;
        }

        $migration = new Migration();
        $migration->version = $v[1];
        $migration->script = $script;
        $migration->date = date("Y-m-d H:i:s");

        // Run the script
        if( !run_script($migration->script) ){
            echo( "Error executing migration file: $migration->script $LINE");
            die;
        }

        // Update the migration table
        echo( "Saving migration entry: $migration...");
        if( save_migration($migration) ){
            echo( "Saved$LINE");
        }
        else{
            echo("UNABLE TO SAVE$LINE");
        }
    }

?>
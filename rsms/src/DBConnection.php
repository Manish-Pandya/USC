<?php

class DBConnection {

    private static $STATEMENT_COUNT = 0;
    private static $STATEMENTS = array();

    private static $CONFIG_READY = false;
    private static $DB_CN;
    private static $DB_UN;
    private static $DB_PW;

    public static function &get(){
        if( !isset($GLOBALS['db']) ){
            DBConnection::connect();
        }

        return $GLOBALS['db'];
    }

    private static function configure(){
        // Pull configuration
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->debug("Configuring " . __CLASS__);

        // Construct database connection string
        $dbhost = ApplicationConfiguration::get('server.db.host');
        $dbname = ApplicationConfiguration::get('server.db.name');

        if( isset($dbhost) && isset($dbname) ){
            self::$DB_CN = "mysql:host=$dbhost;dbname=$dbname";
        }
        else {
            // Fallback to supplied connection-string config
            self::$DB_CN = ApplicationConfiguration::get('server.db.connection');
        }

        self::$DB_UN = ApplicationConfiguration::get('server.db.username');

        /* WARNING:
            There exists a bug in PDO (PHP <5.6) whereby if the password is empty or null,
            the construction will cause an out of memory error.
            Sipmly having the variable unset will prevent this issue
        */
        $pw = @ApplicationConfiguration::get('server.db.password');
        if( isset($pw) && $pw !== '' ) {
            self::$DB_PW = $pw;
        }

        self::$CONFIG_READY = true;
    }

    public static function connect(){
        if( !self::$CONFIG_READY ){
            self::configure();
        }

        Logger::getLogger(__CLASS__)->debug("Opening DB connection");

        $GLOBALS['db'] = new PDO(
            self::$DB_CN,
            self::$DB_UN,
            self::$DB_PW
        );

        Logger::getLogger(__CLASS__)->debug("Connection opened.");

        // After everything's done, disconnect the connection
        register_shutdown_function(function(){
            DBConnection::shutdown();
        });
    }

    static function shutdown(){
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->debug("Prepared " . self::$STATEMENT_COUNT . " Queries during this request");

        if( $LOG->isTraceEnabled() ){
            $LOG->trace( $GLOBALS['db'] );
            $LOG->trace( self::$STATEMENTS );
        }

        // Ensure all statements are closed
        foreach(self::$STATEMENTS as &$stmt){
            DBConnection::closeStatement($stmt);
        }

        // Close connection
        if( $GLOBALS['db'] != null ){
            $LOG->debug("Closing DB connection");
            $GLOBALS['db'] = null;
        }

        if( $LOG->isTraceEnabled() ){
            $LOG->trace( $GLOBALS['db'] );
            $LOG->trace( self::$STATEMENTS );
        }
    }

    public static function closeStatement(&$stmt){
        $stmt = null;
    }

    public static function prepareStatement($sql){
        if(!isset($sql)){
            throw new Exception("No statement provided");
        }

        $db = DBConnection::get();
        $stmt = $db->prepare($sql);

        if( $stmt == false ){
            throw new Exception(print_r($db->errorInfo(), true) . PHP_EOL . $sql);
        }

        self::$STATEMENT_COUNT++;

        $LOG = Logger::getLogger(__CLASS__);
        if( $LOG->isTraceEnabled()){
            $LOG->trace("Prepared statement: $sql");
        }

        return $stmt;
    }
}
?>
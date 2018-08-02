<?php

class DBConnection {

    private static $STATEMENTS = array();

    public static function &get(){
        if( !isset($GLOBALS['db']) ){
            DBConnection::connect();
        }

        return $GLOBALS['db'];
    }

    public static function connect(){
        Logger::getLogger(__CLASS__)->debug("Opening DB connection");
        $GLOBALS['db'] = new PDO(
            getDBConnection(),
            getDBUsername(),
            getDBPassword(),
            array(
                // Use Connection pooling
                PDO::ATTR_PERSISTENT => true
            )
        );

        // After everything's done, disconnect the connection
        register_shutdown_function(function(){
            DBConnection::shutdown();
        });
    }

    static function shutdown(){
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->debug("Shutdown " . count(self::$STATEMENTS) . " DB statement(s) and connection");

        $LOG->trace( $GLOBALS['db'] );
        $LOG->trace( self::$STATEMENTS );

        // Ensure all statements are closed
        foreach(self::$STATEMENTS as &$stmt){
            DBConnection::closeStatement($stmt);
        }

        // Close connection
        if( $GLOBALS['db'] != null ){
            $LOG->debug("Closing DB connection");
            $GLOBALS['db'] = null;
        }

        $LOG->trace( $GLOBALS['db'] );
        $LOG->trace( self::$STATEMENTS );
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

        self::$STATEMENTS[] &= $stmt;

        return $stmt;
    }
}
?>
<?php

class DBConnection {

    public static function connect(){
        LOGGER::getLogger(__CLASS__)->debug("Opening DB connection");
        $GLOBALS['db'] = new PDO(
            getDBConnection(),
            getDBUsername(),
            getDBPAssword(),
            array(
                // Use Connection pooling
                PDO::ATTR_PERSISTENT => true
            )
        );

        // After everything's done, disconnect the connection
        register_shutdown_function('DBConnection::disconnect');
    }

    public static function disconnect(){
        global $db;

        if( $db != null ){
            LOGGER::getLogger(__CLASS__)->debug("Closing DB connection");
            $db = null;
        }
    }

    public static function closeStatement(&$stmt){
        LOGGER::getLogger(__CLASS__)->debug("Closing statement");
        $stmt = null;
    }
}
?>
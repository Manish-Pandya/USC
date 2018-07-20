<?php

class DBConnection {

    public static function connect(){
        LOGGER::getLogger(__CLASS__)->info("Opening DB connection");
        $GLOBALS['db'] = new PDO(
            getDBConnection(),
            getDBUsername(),
            getDBPAssword(),
            array(
                PDO::ATTR_PERSISTENT => true
            )
        );
    }

    public static function disconnect(){
        LOGGER::getLogger(__CLASS__)->info("Disconnecting DB connection");
        global $db;
        $db = null;
    }

    public static function closeStatement(&$stmt){
        
    }
}
?>
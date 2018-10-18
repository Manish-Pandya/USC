<?php

class ModuleManager {
    private static $LOG;
    private static $INITIALIZED = false;
    private static $MODULES = array();

    private static $ACTIVE_MODULES;

    public static function registerModules(){
        if( !self::$INITIALIZED ){
            self::$LOG = Logger::getLogger(__CLASS__);
            self::scan_modules();
        }

        return self::getActiveModules();
    }

    static function registerModule( $module ){
        self::$LOG->debug("Registering module " . get_class($module));

        self::$MODULES[] = $module;
    }

    static function scan_modules(){
        self::$INITIALIZED = true;
        $dir = dirname(__FILE__) . '/modules';

        self::$LOG->trace("Scanning $dir for Modules");
        foreach (glob("$dir/*/*Module.php") as $file) {
            self::$LOG->trace("Found '$file'");
            $class = basename($file, '.php');
            if( class_exists($class) ){
                $module = new $class;
                self::registerModule($module);
            }
            else{
                self::$LOG->warn("Class doesn't exist: $class");
            }
        }
    }

    public static function getActiveModules(){
        if( !self::$ACTIVE_MODULES ){
            self::$ACTIVE_MODULES = array();

            foreach(self::$MODULES as $module ){
                if( $module->isEnabled()){
                    self::$ACTIVE_MODULES[] = $module;
                    self::$LOG->debug( get_class($module) . ' is active');
                }
            }
        }

        return self::$ACTIVE_MODULES;
    }

}
?>
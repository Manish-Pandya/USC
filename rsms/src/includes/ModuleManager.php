<?php

class ModuleManager {
    private static $LOG;
    private static $INITIALIZED = false;
    private static $MODULES = array();

    private static $ACTIVE_MODULE;

    public static function registerModules(){
        if( !self::$INITIALIZED ){
            self::$LOG = Logger::getLogger(__CLASS__);
            self::scan_modules();
        }

        return self::getActiveModule();
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

    public static function getActiveModule(){
        if( !self::$ACTIVE_MODULE ){
            foreach(self::$MODULES as $module ){
                if( $module->isEnabled()){
                    self::$ACTIVE_MODULE = $module;
                    break;
                }
            }
        }

        return self::$ACTIVE_MODULE;
    }

}
?>
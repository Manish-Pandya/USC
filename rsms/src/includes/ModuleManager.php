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

    public static function getAllModules(){
        return self::$MODULES;
    }

    public static function getModuleByName( $name ){
        foreach( self::getAllModules() as $module ){
            if( $name == $module->getModuleName() ){
                return $module;
            }
        }

        return null;
    }

    /**
     * Gets magically-mapped 'special' feature classes from a module
     */
    public static function getModuleFeatureClasses( $module, $subdir_name, $class_suffix ){
        $moduleClass = get_class($module);
        self::$LOG->debug("Find $class_suffix feature classes in '$subdir_name/' for $moduleClass");

        $reflector = new ReflectionClass($moduleClass);
        $specialDir = dirname($reflector->getFileName()) . "/$subdir_name";

        // Magically map module subdirectory to special types
        $candidate_types = array();
        if( is_dir($specialDir) ){
            self::$LOG->debug("$moduleClass includes '$subdir_name' directory");

            // Validate each task type
            foreach (glob("$specialDir/*$class_suffix.php") as $file) {
                self::$LOG->trace("Found '$file'");
                $class = basename($file, '.php');

                if( class_exists($class) ){
                    $candidate_types[] = $class;
                }
                else{
                    self::$LOG->warn("$moduleClass includes $subdir_name file '$file', but expected Class '$class' doesn't exist");
                }
            }
        }
        else{
            self::$LOG->debug("$moduleClass does not include feature directory '$subdir_name'");
        }

        return $candidate_types;
    }
}
?>
<?php

class ActionMappingManager  {

    private static $MAPPINGS = array();

    public static function getMappings() {
        return self::$MAPPINGS;
    }

    public static function register_all( $module, $mappings ){
        self::$MAPPINGS = array_merge(self::$MAPPINGS, $mappings);
    }

    public static function register( $module, ActionMapping $mapping, $name = NULL){
        $actionName = $name != null ? $name : $mapping->actionFunctionName;

        self::$MAPPINGS[$actionName] = $mapping;
    }

    static function getModuleMappings($module){
        if( !array_key_exists($module, self::$MAPPINGS) ){
            // Register module mappings
            self::$MAPPINGS[$module] = array();
        }

        return self::$MAPPINGS[$module];
    }
}
?>
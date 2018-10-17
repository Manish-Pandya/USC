<?php

class ActionMappings  {

    private static $MAPPINGS = array();

    public static function getMappings() {
        return self::$MAPPINGS;
    }

    public static function register_all( $mappings ){
        self::$MAPPINGS = array_merge(self::$MAPPINGS, $mappings);
    }

    public static function register(ActionMapping $mapping, $name = NULL){
        $actionName = $name != null ? $name : $mapping->actionFunctionName;

        self::$MAPPINGS[$actionName] = $mapping;
    }
}
?>
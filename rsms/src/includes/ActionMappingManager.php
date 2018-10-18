<?php

class ActionMappingManager  {

    private static $MAPPINGS = array();

    public static function getAction( $actionName ){
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->trace("Map action '$actionName'");

        $actions = array();

        // Check each active module for this action
        foreach( ModuleManager::getActiveModules() as $module ){
            // Get actions for module;
            $config = $module->getActionConfig();
            if( array_key_exists($actionName, $config) ){
                $moduleName = get_class($module);
                $LOG->trace("Module $moduleName contains action mapping for '$actionName'");

                // This module defines an action by this name
                $actions[] = array(
                    'module'  => $moduleName,
                    'mapping' => $config[$actionName],
                    'manager' => $module->getActionManager()
                );
            }
        }

        $matchedActions = count($actions);

        if( $matchedActions > 1 ){
            // Multiple possible actions exist...
            // TODO: What now?
            $LOG->warn("$matchedActions modules define mapping for action '$actionName'");
        }

        if( $matchedActions > 0 ){
            $LOG->debug("Action '$actionName' mapped to '" . $actions[0]['module'] . "'");
            return $actions[0];
        }

        $LOG->error("No modules define mapping for action '$actionName'");
        return null;
    }
}
?>
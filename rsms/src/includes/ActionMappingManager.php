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
            $LOG->warn("$matchedActions modules define mapping for action '$actionName'");
            $LOG->debug("Attempt to find valid mapping from matches...");

            // Sort Modules to identify the correct mapping
            //   Just sort by name, but always put the CoreModule last
            usort( $actions, function($a, $b) {
                $a_mod = $a['module'];
                $b_mod = $b['module'];

                if( $a_mod == CoreModule::class ){
                    return 1;
                }

                if( $b_mod == CoreModule::class ){
                    return -1;
                }

                return strcmp( $a_mod, $b_mod);
            });

            // This seems to stem from intersecting actions and mappings across multiple modules.
            // This will attempt to find the first match with an existing function, but is no guarantee to find the 'right' one
            // Really, we should only have ONE mapping
            $firstLegalMatch = null;
            foreach( $actions as $match ){
                $mod = $match['module'];
                $action_mgr = $match['manager'];
                $mgr_class = get_class($action_mgr);
                $fn_name = $match['mapping']->actionFunctionName;

                if( method_exists( $action_mgr, $fn_name ) ){
                    $LOG->debug("  Matched $mod/$mgr_class#$fn_name");
                    $firstLegalMatch = $match;
                    break;
                }
                else{
                    $LOG->debug("  Method '$mod/$mgr_class#$fn_name' does not exist");
                }
            }

            if( $firstLegalMatch != null ){
                $actions = array($firstLegalMatch);
            }
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
<?php

class HooksManager {

    public static function hook($hookName, $params){
        $LOG = Logger::getLogger(__CLASS__);

        foreach( ModuleManager::getAllModules() as $module ){
            $moduleHooks = ModuleManager::getModuleFeatureClasses($module, 'hooks', '_Hooks');

            foreach( $moduleHooks as $hook ){
                if( method_exists($hook, $hookName) ){
                    try{
                        $LOG->info("Executing hook: " . $module->getModuleName() . "/$hook.$hookName");
                        $hook::$hookName($params);
                    }
                    catch(Exception $e){
                        $LOG->error("Error excecuting hook: $e");
                    }
                }
            }
        }
    }
}

?>
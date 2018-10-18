<?php

class HazardInventoryModule implements RSMS_Module {
    public function getUiRoot(){
        return '/hazard-inventory';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	strstr($_SERVER["HTTP_REFERER"], '/hazard-inventory/' ) || isset($_GET['hazard-inventory']))
            return true;

        return false;
    }

    public function getActionManager(){
        return 'HazardInventoryActionManager';
    }

    public function registerActionMappings(){
        ActionMappingManager::register_all(HazardInventoryActionMappingFactory::readActionConfig());
    }
}
?>
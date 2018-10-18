<?php

class EquipmentModule implements RSMS_Module {
    public function getUiRoot(){
        return '/equipment';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	strstr($_SERVER["HTTP_REFERER"], '/equipment/' ) || isset($_GET['equipment']))
            return true;

        return false;
    }

    public function getActionManager(){
        return 'Equipment_ActionManager';
    }

    public function registerActionMappings(){
        ActionMappingManager::register_all(Equipment_ActionMappingFactory::readActionConfig());
    }
}
?>
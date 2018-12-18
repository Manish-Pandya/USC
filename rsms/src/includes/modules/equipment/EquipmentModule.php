<?php

class EquipmentModule implements RSMS_Module {
    public function getModuleName(){
        return 'Equipment';
    }

    public function getUiRoot(){
        return '/equipment';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	( isset($_SERVER["HTTP_REFERER"]) && strstr($_SERVER["HTTP_REFERER"], '/equipment/' ) ) || isset($_GET['equipment']))
            return true;

        return false;
    }

    public function getActionManager(){
        return new Equipment_ActionManager();
    }

    public function getActionConfig(){
        return Equipment_ActionMappingFactory::readActionConfig();
    }
}
?>
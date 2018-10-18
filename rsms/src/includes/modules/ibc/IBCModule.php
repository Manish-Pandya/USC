<?php

class IBCModule implements RSMS_Module {
    public function getUiRoot(){
        return '/ibc';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	stristr($_SERVER["HTTP_REFERER"], '/ibc/' ) || isset($_GET['ibc']) || isset($_GET['IBC']))
            return true;

        return false;
    }

    public function getActionManager(){
        return 'IBC_ActionManager';
    }

    public function registerActionMappings(){
        ActionMappings::register_all(IBC_ActionMappingFactory::readActionConfig());
    }
}
?>
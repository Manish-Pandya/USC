<?php

class IBCModule implements RSMS_Module {
    public function getModuleName(){
        return 'IBC';
    }

    public function getUiRoot(){
        return '/ibc';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	( isset($_SERVER["HTTP_REFERER"]) && stristr($_SERVER["HTTP_REFERER"], '/ibc/' ) ) || isset($_GET['ibc']) || isset($_GET['IBC']))
            return true;

        return false;
    }

    public function getActionManager(){
        return new IBC_ActionManager();
    }

    public function getActionConfig(){
        return IBC_ActionMappingFactory::readActionConfig();
    }
}
?>
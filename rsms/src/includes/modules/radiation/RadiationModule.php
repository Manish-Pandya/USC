<?php

class RadiationModule implements RSMS_Module {
    public function getModuleName(){
        return 'Radiation';
    }

    public function getUiRoot(){
        return '/rad';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	( isset($_SERVER["HTTP_REFERER"]) && strstr($_SERVER["HTTP_REFERER"], '/rad/' ) ) || isset($_GET['rad']) )
            return true;

        return false;
    }

    public function getActionManager(){
        return new Rad_ActionManager();
    }

    public function getActionConfig(){
        return Rad_ActionMappingFactory::readActionConfig();
    }
}
?>
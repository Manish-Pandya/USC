<?php

class VerificationModule implements RSMS_Module {
    public function getModuleName(){
        return 'Verification';
    }

    public function getUiRoot(){
        return '/verification';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	( isset($_SERVER["HTTP_REFERER"]) && strstr($_SERVER["HTTP_REFERER"], '/verification/' ) ) || isset($_GET['verification']))
            return true;

        return false;
    }

    public function getActionManager(){
        return new Verification_ActionManager();
    }

    public function getActionConfig(){
        //Verfication's server-side controller (VerificationActionManager extends HazardInventory's, so we "extend" the ActionMappings as well)
        return array_merge(
            Verification_ActionMappingFactory::readActionConfig(),
            HazardInventoryActionMappingFactory::readActionConfig()
        );
    }
}
?>
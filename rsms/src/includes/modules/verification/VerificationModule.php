<?php

class VerificationModule implements RSMS_Module {
    public function getUiRoot(){
        return '/verification';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	strstr($_SERVER["HTTP_REFERER"], '/verification/' ) || isset($_GET['verification']))
            return true;

        return false;
    }

    public function getActionManager(){
        return 'Verification_ActionManager';
    }

    public function registerActionMappings(){
        //Verfication's server-side controller (VerificationActionManager extends HazardInventory's, so we "extend" the ActionMappings as well)
        ActionMappings::register_all(Verification_ActionMappingFactory::readActionConfig());
        ActionMappings::register_all(HazardInventoryActionMappingFactory::readActionConfig());
    }
}
?>
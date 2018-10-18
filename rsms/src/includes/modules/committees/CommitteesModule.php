<?php

class CommitteesModule implements RSMS_Module {
    public function getUiRoot(){
        return '/biosafety-committees';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	strstr($_SERVER["HTTP_REFERER"], '/biosafety-committees/' ) || isset($_GET['biosafety-committees']))
            return true;

        return false;
    }

    public function getActionManager(){
        return new Committees_ActionManager();
    }

    public function getActionConfig(){
        return Committees_ActionMappingFactory::readActionConfig();
    }
}
?>
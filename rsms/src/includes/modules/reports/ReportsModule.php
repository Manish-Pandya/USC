<?php

class ReportsModule implements RSMS_Module {

    public static $NAME = 'Reports';

    public function getModuleName(){
        return self::$NAME;
    }

    public function getUiRoot(){
        return '/reports';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	stristr($_SERVER["HTTP_REFERER"], '/reports/' ) || isset($_GET['reports']))
            return true;

        return false;
    }

    public function getActionManager(){
        return new Reports_ActionManager();
    }

    public function getActionConfig(){
        return Reports_ActionMappingFactory::readActionConfig();
    }
}
?>
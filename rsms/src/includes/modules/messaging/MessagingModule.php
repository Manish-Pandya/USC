<?php

class MessagingModule implements RSMS_Module {

    public function getModuleName(){
        return 'Messaging';
    }

    public function getUiRoot(){
        return '/email-hub';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	stristr($_SERVER["HTTP_REFERER"], '/email-hub/' ) || isset($_GET['email-hub']))
            return true;

        return false;
    }

    public function getActionManager(){
        return new Messaging_ActionManager();
    }

    public function getActionConfig(){
        return array();
    }
}
?>
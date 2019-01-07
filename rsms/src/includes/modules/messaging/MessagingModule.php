<?php

class MessagingModule implements RSMS_Module {
    public static $NAME = 'Messaging';
    public static $CONFIG_EMAIL_SUPPRESS_ALL = 'module.Messaging.email.suppress_all';
    public static $CONFIG_EMAIL_SEND_TO_ROLE = 'module.Messaging.email.send_only_to_role';
    public static $CONFIG_EMAIL_DEFAULT_SEND_FROM = 'module.Messaging.email.defaults.send_from';
    public static $CONFIG_EMAIL_DEFAULT_RETURN_PATH = 'module.Messaging.email.defaults.return_path';

    public function getModuleName(){
        return self::$NAME;
    }

    public function getUiRoot(){
        return '/email-hub';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'email-hub' param is set
        if(	( isset($_SERVER["HTTP_REFERER"]) && stristr($_SERVER["HTTP_REFERER"], '/email-hub/' ) ) || isset($_GET['email-hub']))
            return true;

        return false;
    }

    public function getActionManager(){
        return new Messaging_ActionManager();
    }

    public function getActionConfig(){
        // TODO: do we need a factory type?
        $ADMIN_ROLES = array("Admin");
        return array(
            'getAllMessageTypes' => new SecuredActionMapping("getAllMessageTypes", $ADMIN_ROLES),
            'getMessageTemplates' => new SecuredActionMapping("getMessageTemplates", $ADMIN_ROLES),
            'toggleTemplateActive' => new SecuredActionMapping("toggleTemplateActive", $ADMIN_ROLES),
            'createNewTemplate' => new SecuredActionMapping("createNewTemplate", $ADMIN_ROLES),
            'saveTemplate' => new SecuredActionMapping("saveTemplate", $ADMIN_ROLES),
            'getEmails' => new SecuredActionMapping("getEmails", $ADMIN_ROLES),
            'getEmailDisclaimers' => new SecuredActionMapping("getEmailDisclaimers", $ADMIN_ROLES)
        );
    }
}
?>
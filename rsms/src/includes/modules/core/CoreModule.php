<?php

class CoreModule implements RSMS_Module, MyLabWidgetProvider {
    public const CONFIG_SERVER_DB_HOST = 'server.db.host';
    public const CONFIG_SERVER_DB_NAME = 'server.db.name';
    public const CONFIG_SERVER_DB_USERNAME = 'server.db.username';
    public const CONFIG_SERVER_DB_PASSWORD = 'server.db.password';
    public const CONFIG_SERVER_DB_CONNECTION = 'server.db.connection';

    public static $NAME = 'Core';

    public function getModuleName(){
        return self::$NAME;
    }

    public function getUiRoot(){
        return '/';
    }

    public function isEnabled() {
        return true;
    }

    public function getActionManager(){
        return new ActionManager();
    }

    public function getActionConfig(){
        return ActionMappingFactory::readActionConfig();
    }

    public function getMyLabWidgets( User $user ){
        $widgets = array();

        if( CoreSecurity::userHasRoles($user, array('Admin')) ){
            $adminWidget = new MyLabWidgetDto();
            $adminWidget->title = "RSMS Administration";
            $adminWidget->icon = "icon-home";
            $adminWidget->template = "admin";

            $widgets[] = $adminWidget;
        }

        return $widgets;
    }
}
?>
<?php

class EquipmentModule implements RSMS_Module, MyLabWidgetProvider {
    public function getModuleName(){
        return 'Equipment';
    }

    public function getUiRoot(){
        return '/equipment';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	( isset($_SERVER["HTTP_REFERER"]) && strstr($_SERVER["HTTP_REFERER"], '/equipment/' ) ) || isset($_GET['equipment']))
            return true;

        return false;
    }

    public function getActionManager(){
        return new Equipment_ActionManager();
    }

    public function getActionConfig(){
        return Equipment_ActionMappingFactory::readActionConfig();
    }

    public function getMyLabWidgets( User $user ){
        $widgets = array();

        $equipmentWidget = new MyLabWidgetDto();
        $equipmentWidget->title = "Equipment";
        $equipmentWidget->icon = "icon-cog-2";
        $equipmentWidget->template = null;
        $equipmentWidget->data = null;
        $widgets[] = $equipmentWidget;

        return $widgets;
    }
}
?>
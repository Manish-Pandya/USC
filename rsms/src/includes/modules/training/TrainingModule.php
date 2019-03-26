<?php

class TrainingModule implements RSMS_Module, MyLabWidgetProvider {
    public function getModuleName(){
        return 'Training';
    }

    public function getUiRoot(){
        return '/training';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	( isset($_SERVER["HTTP_REFERER"]) && strstr($_SERVER["HTTP_REFERER"], '/training/' ) ) || isset($_GET['training']))
            return true;

        return false;
    }

    public function getActionManager(){
        return null;
    }

    public function getActionConfig(){
        return array();
    }

    public function getMyLabWidgets( User $user ){
        $widgets = array();

        $trainingWidget = new MyLabWidgetDto();
        $trainingWidget->title = "Training Programs";
        $trainingWidget->icon = "icon-bookmark";
        $trainingWidget->template = null;
        $trainingWidget->data = null;
        $widgets[] = $trainingWidget;

        return $widgets;
    }
}
?>
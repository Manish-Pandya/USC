<?php

class StaticTrainingModule implements RSMS_Module, MyLabWidgetProvider {
    public const NAME = 'StaticTraining'
    public function getModuleName(){
        return self::NAME;
    }

    public function getUiRoot(){
        return '/static-training';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	( isset($_SERVER["HTTP_REFERER"]) && strstr($_SERVER["HTTP_REFERER"], '/static-training/' ) ) || isset($_GET['static-training']))
            return true;

        return false;
    }

    public function getActionManager(){
        return null;
    }

    public function getActionConfig(){
        return [];
    }

    public function getMyLabWidgets( User $user ){
        $widgets = array();

        $downloads_group = "stopgap_training_download";
        $download_icon = 'icon-download-3';

        ////////////////////////////////////////////////
        // stopgap training documents
        // TODO: Dynamically load list of files?
        $fileinfo = new GenericDto([
            'text' => "Will you be conducting research involving human-derived materials that may contain bloodborne pathogens?",
            'name' => "Bloodborne Pathogen Training",
            'path' => WEB_ROOT . "/static-training/courses/online-training-bloodborne-pathogens-for-labs.pptx"
        ]);

        // action widget
        $stopgap_training_widget_popup = new MyLabWidgetDto();
        $stopgap_training_widget_popup->template = 'file-download-modal';
        $stopgap_training_widget_popup->icon = $download_icon;
        $stopgap_training_widget_popup->title = $fileinfo->name;

        // standard widget
        $stopgap_training_widget = new MyLabWidgetDto();
        $stopgap_training_widget->template = "file-download";
        $stopgap_training_widget->icon = $download_icon;
        $stopgap_training_widget->title = $fileinfo->name;
        $stopgap_training_widget->group = $downloads_group;
        $stopgap_training_widget->data = $fileinfo;
        $stopgap_training_widget->actionWidgets = [ $stopgap_training_widget_popup ];

        $widgets[] = $stopgap_training_widget;
        ////////////////////////////////////////////////

        return $widgets;
    }
}
?>
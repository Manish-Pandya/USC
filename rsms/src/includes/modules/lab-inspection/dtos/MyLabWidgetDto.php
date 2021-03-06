<?php

class MyLabWidgetDto {

    public $group = "999_ungrouped";
    public $title;
    public $subtitle;
    public $icon;
    public $image;
    public $template;
    public $data;
    public $fullWidth;
    public $toolbar;

    public $alerts;
    public $actionWidgets;

    public function getGroup(){ return $this->group; }
    public function getTitle(){ return $this->title; }
    public function getSubtitle(){ return $this->subtitle; }
    public function getIcon(){ return $this->icon; }
    public function getImage(){ return $this->image; }
    public function getTemplate(){ return $this->template; }
    public function getData(){ return $this->data; }
    public function getFullWidth(){ return $this->fullWidth; }
    public function getToolbar(){ return $this->toolbar; }
    public function getAlerts(){ return $this->alerts; }
    public function getActionWidgets(){ return $this->actionWidgets; }
}


?>

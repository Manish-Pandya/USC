<?php

class MyLabWidgetDto {

    public $group = "999_ungrouped";
    public $title;
    public $icon;
    public $image;
    public $template;
    public $data;

    public function getGroup(){ return $this->group; }
    public function getTitle(){ return $this->title; }
    public function getIcon(){ return $this->icon; }
    public function getImage(){ return $this->image; }
    public function getTemplate(){ return $this->template; }
    public function getData(){ return $this->data; }
}


?>

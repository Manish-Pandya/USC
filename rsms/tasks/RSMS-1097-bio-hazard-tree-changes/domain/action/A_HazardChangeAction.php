<?php
class A_HazardChangeAction {
    public $type;
    public $hazard_id;
    public $desc;

    public function __construct($hazard_id, $type, $desc){
        $this->hazard_id = $hazard_id;
        $this->type = $type;
        $this->desc = $desc;
    }

    public function __toString(){
        return "$this->type #$this->hazard_id : $this->desc";
    }

    public function isPostAction(){
        return false;
    }
}
?>

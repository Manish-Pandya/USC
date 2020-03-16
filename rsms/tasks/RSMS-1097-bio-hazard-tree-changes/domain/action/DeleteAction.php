<?php
class DeleteAction extends A_HazardChangeAction {
    public const DELETE = 'DELETE';
    public $hazard_name;
    public function __construct($hazard_id, $reason, $hazard_name){
        parent::__construct($hazard_id, DeleteAction::DELETE, $reason);
        $this->hazard_name = $hazard_name;
    }

    public function __toString(){
        return "Delete hazard #$this->hazard_id ($this->hazard_name)"
            . ($this->desc == null ? '' : " - $this->desc");
    }
}
?>

<?php
class ReorderAction extends A_HazardChangeAction {
    public const REORDER = 'REORDER';
    public function __construct($hazard_id, $desc = ''){
        parent::__construct($hazard_id, ReorderAction::REORDER, $desc);
    }

    public function isPostAction(){
        return true;
    }
}
?>

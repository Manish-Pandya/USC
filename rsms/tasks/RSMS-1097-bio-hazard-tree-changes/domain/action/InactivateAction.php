<?php
class InactivateAction extends A_HazardChangeAction {
    public const INACTIVATE = 'INACTIVATE';
    public function __construct($hazard_id, $reason){
        parent::__construct($hazard_id, InactivateAction::INACTIVATE, $reason);
    }
}
?>
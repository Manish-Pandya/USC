<?php
class MergeAction extends A_HazardChangeAction {
    public const MERGE = 'MERGE';
    public $constituent_ids;

    public function __construct(int $target_hazard_id, Array $constituent_ids, $desc = NULL){
        parent::__construct($target_hazard_id, MergeAction::MERGE, $desc);
        $this->constituent_ids = $constituent_ids;
    }

    public function __toString(){
        return "Merge hazards [" . implode(',', $this->constituent_ids) . "] into #$this->hazard_id"
            . ($this->desc == null ? '' : " ($this->desc)");
    }
}
?>

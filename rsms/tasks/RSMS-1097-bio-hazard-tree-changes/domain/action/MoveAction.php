<?php
class MoveAction extends A_HazardChangeAction {
    public const MOVE = 'MOVE';
    public $targetName;
    public $targetId;
    public function __construct($hazard_id, $targetName, $targetId = null, $desc = null){
        parent::__construct($hazard_id, MoveAction::MOVE, $desc);

        $this->targetName = $targetName;
        $this->targetId = $targetId;
    }

    public function __toString(){
        $target = !is_array($this->targetName) ? $this->targetName : '[' . implode(' / ', $this->targetName) . ']';
        return "Move hazard #$this->hazard_id to Parent '$target'"
            . ($this->targetId == null ? ' (NEW)' : " (Hazard #$this->targetId)")
            . ($this->desc == null ? '' : " ($this->desc)");
    }
}
?>

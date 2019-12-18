<?php
class AddAction extends A_HazardChangeAction {
    public const ADD = 'ADD';
    public $newHazardName;
    public $subParentName;
    public function __construct($hazard_id, $newHazardName, $subParentName = null, $desc = null){
        parent::__construct($hazard_id, AddAction::ADD, $desc);

        $this->newHazardName = $newHazardName;
        $this->subParentName = $subParentName;
    }

    public function __toString(){
        return "Add new hazard '$this->newHazardName' to [#$this->hazard_id"
            . ($this->subParentName == null ? '' : " / '$this->subParentName'")
            . ']'
            . ($this->desc == null ? '' : " ($this->desc)");
    }
}
?>

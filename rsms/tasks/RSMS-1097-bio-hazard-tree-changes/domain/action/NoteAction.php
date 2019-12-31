<?php
class NoteAction extends A_HazardChangeAction {
    public const NOTE = 'NOTE';

    public function __construct($hazard_id, $note){
        parent::__construct($hazard_id, NoteAction::NOTE, $note);

    }

    public function __toString(){
        return "Note for Hazard #$this->hazard_id: $this->desc";
    }
}
?>

<?php
class RenameAction extends A_HazardChangeAction {
    public const RENAME = 'RENAME';
    public function __construct($hazard_id, $newName){
        parent::__construct($hazard_id, RenameAction::RENAME, $newName);
    }
}
?>

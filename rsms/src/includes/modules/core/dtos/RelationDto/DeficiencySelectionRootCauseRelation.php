<?php

class DeficiencySelectionRootCauseRelation {
	private $deficiency_selection_id;
	private $deficiency_root_cause_id;
	
	public function getDeficiency_selection_id() { return $this->deficiency_selection_id; }
	public function getDeficiency_root_cause_id() { return $this->deficiency_root_cause_id; }
	
	public function setDeficiency_selection_id($newId) { $this->deficiency_selection_id = $newId; }
	public function setDeficiency_root_cause_id($newId) { $this->deficiency_root_cause_id = $newId; }
}
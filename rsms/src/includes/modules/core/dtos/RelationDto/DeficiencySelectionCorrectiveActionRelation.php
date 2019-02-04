<?php

class DeficiencySelectionCorrectiveActionRelation {
	private $deficiency_selection_id;
	private $deficiency_corrective_action_id;
	
	public function getDeficiency_selection_id() { return $this->deficiency_selection_id; }
	public function getDeficiency_selection_corrective_action_id() { return $this->deficiency_corrective_action_id; }

	public function setDeficiency_selection_id($newId) { $this->deficiency_selection_id = $newId; }
	public function setDeficiency_selection_corrective_action_id($newId) { $this->deficiency_corrective_action_id = $newId; }
}
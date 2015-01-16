<?php

class HazardChecklistRelation {
	private $hazard_id;
	private $checklist_id;
	
	public function getHazard_id() { return $this->hazard_id; }
	public function getChecklist_id() { return $this->checklist_id; }

	public function setHazard_id($newId) { $this->hazard_id = $newId; }
	public function setChecklist_id($newId) { $this->checklist_id = $newId; }
}
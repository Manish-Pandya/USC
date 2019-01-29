<?php

class InspectionChecklistRelation {
	private $checklist_id;
	private $inspection_id;

	public function getChecklist_id() { return $this->checklist_id; }
	public function getInspection_id() { return $this->inspection_id; }

	public function setChecklist_id($newId) { $this->checklist_id = $newId; }
	public function setInspection_id($newId) { $this->inspection_id = $newId; }
}
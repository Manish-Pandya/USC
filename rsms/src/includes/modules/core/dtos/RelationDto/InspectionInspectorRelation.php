<?php

class InspectionInspectorRelation {
	private $inspector_id;
	private $inspection_id;

	public function getInspector_id() { return $this->inspector_id; }
	public function getInspection_id() { return $this->inspection_id; }

	public function setInspector_id($newId) { $this->inspector_id = $newId; }
	public function setInspection_id($newId) { $this->inspection_id = $newId; }
}
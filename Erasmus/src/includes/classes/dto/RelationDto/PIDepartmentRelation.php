<?php

class PrincipalInvestigatorDepartmentRelation {
	private $principal_investigator_id;
	private $department_id;

	public function getPrincipal_investigator_id() { return $this->principal_investigator_id; }
	public function getDepartment_id() { return $this->department_id; }

	public function setPrincipal_investigator_id($newId) { $this->principal_investigator_id = $newId; }
	public function setDepartment_id($newId) { $this->department_id = $newId; }
}
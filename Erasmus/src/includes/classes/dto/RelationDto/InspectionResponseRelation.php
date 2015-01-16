<?php

class InspectionResponseRelation {
	private $response_id;
	private $inspection_id;

	public function getResponse_id() { return $this->response_id; }
	public function getInspection_id() { return $this->inspection_id; }

	public function setResponse_id($newId) { $this->response_id = $newId; }
	public function setInspection_id($newId) { $this->inspection_id = $newId; }
}
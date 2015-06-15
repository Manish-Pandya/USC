<?php

class ResponseObservationRelation {
	private $response_id;
	private $observation_id;

	public function getResponse_id() { return $this->response_id; }
	public function getObservation_id() { return $this->observation_id; }

	public function setResponse_id($newId) { $this->response_id = $newId; }
	public function setObservation_id($newId) { $this->observation_id = $newId; }
}
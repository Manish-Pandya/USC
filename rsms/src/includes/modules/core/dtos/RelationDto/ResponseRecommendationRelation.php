<?php

class ResponseRecommendationRelation {
	private $response_id;
	private $recommendation_id;

	public function getResponse_id() { return $this->response_id; }
	public function getRecommendation_id() { return $this->recommendation_id; }

	public function setResponse_id($newId) { $this->response_id = $newId; }
	public function setRecommendation_id($newId) { $this->recommendation_id = $newId; }
}
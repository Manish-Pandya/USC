<?php

class PIAuthorizationRoomRelation {
	private $pi_authorization_id;
	private $room_id;

	public function getPi_authorization_id() { return $this->pi_authorization_id; }
	public function getRoom_id() { return $this->room_id; }

	public function setPrincipal_investigator_id($newId) { $this->principal_investigator_id = $newId; }
	public function setRoom_id($newId) { $this->room_id = $newId; }
}
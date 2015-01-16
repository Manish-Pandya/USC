<?php

class PrincipalInvestigatorRoomRelation {
	private $principal_investigator_id;
	private $room_id;

	public function getPrincipal_investigator_id() { return $this->principal_investigator_id; }
	public function getRoom_id() { return $this->room_id; }

	public function setPrincipal_investigator_id($newId) { $this->principal_investigator_id = $newId; }
	public function setRoom_id($newId) { $this->room_id = $newId; }
}
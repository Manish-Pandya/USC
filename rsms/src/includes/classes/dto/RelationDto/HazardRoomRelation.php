<?php

class HazardRoomRelation {
	private $hazard_id;
	private $room_id;

	public function getHazard_id() { return $this->hazard_id; }
	public function getRoom_id() { return $this->room_id; }
	
	public function setHazard_id($newId) { $this->hazard_id = $newId; }
	public function setRoom_id($newId) { $this->room_id = $newId; }
}
<?php

class InspectionRoomRelation {
	private $room_id;
	private $inspection_id;

	public function getRoom_id() { return $this->room_id; }
	public function getInspection_id() { return $this->inspection_id; }

	public function setRoom_id($newId) { $this->room_id = $newId; }
	public function setInspection_id($newId) { $this->inspection_id = $newId; }
}
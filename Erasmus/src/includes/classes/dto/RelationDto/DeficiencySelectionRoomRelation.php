<?php

class DeficiencySelectionRoomRelation {
	private $deficiency_selection_id;
	private $room_id;
	
	public function getDeficiency_selection_id() { return $this->deficiency_selection_id; }
	public function getRoom_id() { return $this->room_id; }

	public function setDeficiency_selection_id($newId) { $this->deficiency_selection_id = $newId; }
	public function setRoom_id($newId) { $this->room_id = $newId; }
}
<?php
/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class PendingRoomChange extends PendingChange {
	
	private $room;	
	
	public function getRoom() {
		if($this->room === NULL && $this->hasPrimaryKeyValue()) {
			$roomDao = new GenericDAO(new Room());
			$this->room = $roomDao->getById($this->parent_id);
		}
		return $this->room;
	}
}
?>
<?php
/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class PendingRoomChange extends PendingChange {
	
	private $room;
		
	public function __construct(){

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = EntityMap::eager("getParent_id");
		$entityMaps[] = EntityMap::lazy("getRoom");
	
		return $entityMaps;
	}
	
	public function getRoom() {
		if($this->room === NULL && $this->hasPrimaryKeyValue()) {
			$roomDao = new RoomDAO();
			$this->room = $roomDao->getById($this->parent_id);
		}
		return $this->room;
	}
}
?>
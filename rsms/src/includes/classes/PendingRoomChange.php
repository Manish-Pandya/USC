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
	
		// Define which subentities to load
		$entityMaps = array();
		//$entityMaps[] = new EntityMap("lazy","getDeficiencySelection");
		$entityMaps[] = new EntityMap("eager","getParent_id");
		$entityMaps[] = new EntityMap("lazy","getRoom");
	
		$this->setEntityMaps($entityMaps);
	
	}
	
	
	
	public function getRoom() {
		if($this->room === NULL && $this->hasPrimaryKeyValue()) {
			$roomDao = new GenericDAO(new Room());
			$this->room = $roomDao->getById($this->parent_id);
		}
		return $this->room;
	}
}
?>
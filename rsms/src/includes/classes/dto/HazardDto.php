<?php

class HazardDto {
	private $principal_investigator_id;
	private $hazard_id;
	private $hazard_name;
	private $inspectionRooms;
	private $isPresent;
	private $parent_hazard_id;
	private $roomIds;
	private $hasMultiplePis;
	private $stored_only;

	
	public function getPrincipal_investigator_id() { return $this->principal_investigator_id; }
	public function getHazard_id() { return $this->hazard_id; }
	public function getParent_Hazard_id() {	return $this->parent_hazard_id;	}
	public function getHazard_name() { return $this->hazard_name; }
	public function getInspectionRooms(){ return $this->inspectionRooms; }
	public function getIsPresent(){return $this->isPresent;}
	public function getRoomIds(){return $this->roomIds;}
	public function getHasMultiplePis(){return $this->hasMultiplePis;}	
	public function getStored_only(){return $this->stored_only;}
	
	public function setPrincipal_investigator_id($newId) { $this->principal_investigator_id = $newId; }
	public function setHazard_id($newId) { $this->hazard_id = $newId; }
	public function setParent_Hazard_id($newId) { $this->parent_hazard_id = $newId; }
	public function setHazard_name($name) { $this->name = $name; }	
	public function setInspectionRooms($rooms) { $this->inspectionRooms = $rooms; }
	public function setIsPresent($isPresent){$this->isPresent = $isPresent;}
	public function setRoomIds($ids){$this->roomIds = $ids;}
	public function setHasMultiplePis($hasMultiplePis){ $this->hasMultiplePis = $hasMultiplePis; }
	public function setStored_only($stored){$this->stored_only = $stored;}	
	
	public function setAndFilterInspectionRooms($rooms) { 
		$this->inspectionRooms = $rooms; 
		$this->filterRooms();
	}
	
	public function filterRooms(){
		$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
		$this->isPresent = false;
		// Get the db connection
		global $db;
		
		$roomIds = implode (',',$this->roomIds);
		
		//get all the Relationships between this hazard and rooms that this PI has, so we can determine if this PI or ANY PI has the hazard in any of these rooms
		$queryString = "SELECT * FROM principal_investigator_hazard_room WHERE hazard_id = $this->hazard_id AND room_id IN ($roomIds);";
 		$stmt = $db->prepare($queryString);
		$stmt->execute();
		$piHazardRooms = $stmt->fetchAll(PDO::FETCH_CLASS, "PrincipalInvestigatorHazardRoomRelation");
		
		$this->stored_only = true;
		
		$relationHashMap = array();
		//build key_id arrays for fast comparison in the next step
		foreach($piHazardRooms as $relation){
			$relationHashMap[$relation->getRoom_id()] = $relation;
			if($relationHashMap[$relation->getRoom_id()]->getPrincipal_investigator_id() != $this->getPrincipal_investigator_id()){
				$this->hasMultiplePis = true;
				$relationHashMap[$relation->getRoom_id()]->setStatus("OTHER_PI");
			}
		}	
		foreach ($this->inspectionRooms as $room){
			if( isset($relationHashMap[$room->getRoom_id()])) {
				if($relationHashMap[$room->getRoom_id()]->getPrincipal_investigator_id() == $this->principal_investigator_id ){
					$room->setContainsHazard(true);
					$this->isPresent = true;
					if($room->getStatus != "STORED_ONLY"){
						$this->setStored_only(false);
					}
				}
				$room->setHazard_id($this->getHazard_id());
				$room->setStatus($relationHashMap[$room->getRoom_id()]->getStatus());
			}
		}
		
	}
	
}
?>
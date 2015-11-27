<?php

class HazardDto {
    private $principal_investigator_id;
    private $key_id;
    private $hazard_id;
    private $hazard_name;
    private $is_equipment;
    private $inspectionRooms;
    private $isPresent;
    private $parent_hazard_id;
    private $roomIds;
    private $hasMultiplePis;
    private $stored_only;
    private $hasChildren;
    private $order_index;


    public function getPrincipal_investigator_id() { return $this->principal_investigator_id; }
    public function getKey_id() { return $this->key_id; }
    public function getHazard_id() {
        return $this->hazard_id;
    }

    public function getParent_hazard_id() {	return $this->parent_hazard_id;	}
    public function getHazard_name() { return $this->hazard_name; }
    public function getInspectionRooms(){ return $this->inspectionRooms; }
    public function getIsPresent(){return $this->isPresent;}
    public function getRoomIds(){return $this->roomIds;}
    public function getHasMultiplePis(){return $this->hasMultiplePis;}
    public function getStored_only(){return $this->stored_only;}
    public function getHasChildren(){return (bool) $this->hasChildren;}
	public function getIs_equipment(){return (bool) $this->is_equipment;}
	public function getOrder_index(){return (int) $this->order_index;}
	
    
    public function setPrincipal_investigator_id($newId) { $this->principal_investigator_id = $newId; }
    public function setHazard_id($newId) { $this->hazard_id = $newId; }
    public function setKey_id($newId) { $this->key_id = $newId; }

    public function setParent_hazard_id($newId) { $this->parent_hazard_id = $newId; }
    public function setHazard_name($name) { $this->name = $name; }
    public function setInspectionRooms($rooms) { $this->inspectionRooms = $rooms; }
    public function setIsPresent($isPresent){$this->isPresent = $isPresent;}
    public function setRoomIds($ids){$this->roomIds = $ids;}
    public function setHasMultiplePis($hasMultiplePis){ $this->hasMultiplePis = $hasMultiplePis; }
    public function setStored_only($stored){$this->stored_only = $stored;}
    public function setHasChildren($hasChildren){$this->hasChildren = $hasChildren;}
	public function setIs_equipment($is){ $this->is_equipment = $is; }
	public function setOrder_index($idx){ $this->order_index = $idx;}
    
    public function setAndFilterInspectionRooms($rooms) {
    	$LOG = Logger::getLogger( __CLASS__ );
    	 
        $LOG->fatal($this->getHazard_name());
        $this->filterRooms($rooms);
        $this->inspectionRooms = $rooms;
        
    }

    public function filterRooms($rooms){
        $LOG = Logger::getLogger(__CLASS__ );
        $this->isPresent = false;
        // Get the db connection
        global $db;
        $LOG->fatal("filtering rooms for ".$this->getHazard_name());
        
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
        }
        
        foreach ($rooms as &$room){
            if( isset($relationHashMap[$room->getRoom_id()])) {
            	$room->setStatus($relationHashMap[$room->getRoom_id()]->getStatus());
            	$LOG->fatal($relationHashMap[$room->getRoom_id()]->getPrincipal_investigator_id() . " | " . $this->getPrincipal_investigator_id());
                if($relationHashMap[$room->getRoom_id()]->getPrincipal_investigator_id() == $this->getPrincipal_investigator_id() ){
                    $room->setContainsHazard(true);
                    $this->isPresent = true;
                    $this->setStored_only($room->getStatus() == "STORED_ONLY");
                }else{
                	$this->hasMultiplePis = true;
                	$room->setContainsHazard( false );
                	$room->setStatus("OTHER_PI");
                }
            }else{
            	$room->setContainsHazard( false );
            }
        }
    }

}
?>

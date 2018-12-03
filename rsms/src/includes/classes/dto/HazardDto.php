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
    private $belongsToOtherPI;

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
	public function getOrder_index(){return (float) $this->order_index;}
	public function getBelongsToOtherPI(){return (bool) $this->belongsToOtherPI;}

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
	public function setBelongsToOtherPI($belongs){$this->belongsToOtherPI = $belongs;}


    public function setAndFilterInspectionRooms($rooms) {
    	$LOG = Logger::getLogger( __CLASS__ );

        //$LOG->fatal($this->getHazard_name());
        $rooms = $this->filterRooms($rooms);
        $this->inspectionRooms = $rooms;

    }

    public function filterRooms($rooms){
        $LOG = Logger::getLogger(__CLASS__ );
        $this->isPresent = false;
        // Get the db connection
        $db = DBConnection::get();
        $roomIds = implode (',',$this->roomIds);

		$queryString = "SELECT principal_investigator_id, room_id
                        FROM principal_investigator_room a
                        LEFT JOIN principal_investigator b
                        ON a.principal_investigator_id = b.key_id
                        WHERE b.is_active = 1 AND a.room_id IN ( $roomIds ) group by a.principal_investigator_id";

		$stmt = DBConnection::prepareStatement($queryString);

		$stmt->execute();
		$piIds = array();


        /*principal_investigator_id] => 344
            [0] => 344
            [room_id] => 718
            [1] => 718
            */
        foreach($stmt->fetchAll() as $row){
            if(!array_key_exists($row["room_id"], $piIds)){
                $piIds[$row["room_id"]] = array();
            }
            $piIds[$row["room_id"]][] = $row["principal_investigator_id"];
        }

        //get all the Relationships between this hazard and rooms that this PI has, so we can determine if this PI or ANY PI has the hazard in any of these rooms
        $queryString = "SELECT
						a.key_id, a.hazard_id, a.room_id, a.principal_investigator_id, a.status
						FROM principal_investigator_hazard_room a
						LEFT JOIN principal_investigator_room b
						ON a.principal_investigator_id = b.principal_investigator_id
                        LEFT JOIN principal_investigator c
                        ON c.key_id = a.principal_investigator_id
						WHERE (c.is_active = 1 OR c.key_id = $this->principal_investigator_id) AND a.hazard_id = $this->hazard_id AND a.room_id IN ($roomIds) AND b.room_id IN($roomIds)";

        $stmt = DBConnection::prepareStatement($queryString);
        $stmt->execute();
        $piHazardRooms = $stmt->fetchAll(PDO::FETCH_CLASS, "PrincipalInvestigatorHazardRoomRelation");

        $this->stored_only = false;

        $relationHashMap = array();
        //build key_id arrays for fast comparison in the next step
        foreach($piHazardRooms as $relation){
        	//while we're at it, determine if any of these relations belong to other PIs
        	if($relation->getPrincipal_investigator_id() != $this->getPrincipal_investigator_id()
                    && array_key_exists($relation->getRoom_id(), $piIds) && in_array($relation->getPrincipal_investigator_id(), $piIds[$relation->getRoom_id()])){

        		$this->hasMultiplePis = true;
        		$relation->setHasMultiplePis(true);
        	}else{
        		$relation->setHasMultiplePis(false);
        	}

        	if(!isset($relationHashMap[$relation->getRoom_id()])){
        		$relationHashMap[$relation->getRoom_id()] = array();
        	}
        	array_push($relationHashMap[$relation->getRoom_id()], $relation);
        }
        $this->isPresent = false;
        $storedOnly = true;
        foreach ($rooms as &$room){
        	$room->setContainsHazard(false);
        	$room->setHasMultiplePis(false);

            if( isset($relationHashMap[$room->getRoom_id()])) {
            	//see if there's a relation for this room and hazard with this pi id
            	foreach ($relationHashMap[$room->getRoom_id()] as $relation){
            		if($relation->getPrincipal_investigator_id() == $this->getPrincipal_investigator_id() ){
            			$room->setContainsHazard(true);
            			$this->isPresent = true;
            			$room->setStatus($relation->getStatus());
            			if($relation->getStatus() != "STORED_ONLY"){
            				$storedOnly = false;
            			}else{
                            $room->setStored(true);
                        }

            		}
            		if($relation->getHasMultiplePis() == true){
            			$room->setHasMultiplePis(true);
                        if($relation->getPrincipal_investigator_id() != $this->principal_investigator_id && in_array($relation->getPrincipal_investigator_id(), $piIds)){
                            //array_push($this->inspectionRooms, $room);
                            $room->setOtherLab(true);
                        }
                        if($relation->getStatus() != "STORED_ONLY"){
            				$storedOnly = false;
            			}
            		}
            	}
            }
        }

        //if Another PI has this hazard in one of these rooms, but the relevant PI does not
        if($this->getHasMultiplePis() == true && $this->getIsPresent() == false){
        	$this->belongsToOtherPI = true;
        }

        //if the hazard is stored only in every room, and is present, set its stored_only property to true.
        $this->stored_only = (bool) ($storedOnly && ($this->isPresent || $this->belongsToOtherPI));
        return $rooms;
    }

}
?>

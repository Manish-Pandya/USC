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

    /**
     * @deprecated Refactored into PrincipalInvestigatorHazardRoomRelationDAO.
     */
    public function setAndFilterInspectionRooms($rooms) {
    	$LOG = Logger::getLogger( __CLASS__ );
        $LOG->warn("Call to deprecated function " . __FUNCTION__);

        //$LOG->fatal($this->getHazard_name());
        $rooms = $this->filterRooms($rooms);
        $this->inspectionRooms = $rooms;
    }

    /**
     * @param Array $rooms Array of PIHazardRoomDto
     * @deprecated Refactored into PrincipalInvestigatorHazardRoomRelationDAO.
     */
    public function filterRooms($rooms){
        $LOG = Logger::getLogger(__CLASS__ );
        $LOG->warn("Call to deprecated function " . __FUNCTION__);

        $pihrDao = new PrincipalInvestigatorHazardRoomRelationDAO();
        return $pihrDao->determineHazardStatus( $this, $rooms );
    }

}
?>

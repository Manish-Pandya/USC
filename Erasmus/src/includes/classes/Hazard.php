<?php
/**
 * 
 * 
 * 
 * @author Hoke Currie, GraySail LLC
 */
class Hazard extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "hazard";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"	=> "text",
		//parent hazard is a relationship
		//subhazards are relationships
		"parent_hazard_id" => "integer",
		//checklist is a relationship
		//rooms are relationships

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer"
	);
	
	/** Relationships */
	protected static $ROOMS_RELATIONSHIP = array(
			"className"	=>	"Room",
			"tableName"	=>	"hazard_room",
			"keyName"	=>	"room_id",
			"foreignKeyName"	=>	"hazard_id"
	);
	
	public static $HAZARDS_RELATIONSHIP = array(
			"className"	=>	"Hazard",
			"tableName"	=>	"hazard",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"parent_hazard_id"
	);
	
	protected static $CHECKLIST_RELATIONSHIP = array(
			"className"	=>	"Checklist",
			"tableName"	=>	"checklist",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"hazard_id"
	);
	
	/** Name of the hazard */
	private $name;
	
	/** parent Hazard entity */
	private $parent_hazard_id;
	
	/** Array of child Hazard entities */
	private $subHazards;
	
	/** The single Checklist entity associated with this Hazard */
	private $checklist;
	
	/** Array of Room entities in which this Hazard is contained */
	private $rooms;
	
	/** Array of Room entities relevant to a particular inspection */
	private $inspectionRooms;

	/** Array of Room entities relevant to a particular inspection */
	private $isPresent;
	
	/** Array of the parent ids of this hazard */
	private $parentIds;
	
	
	
	//TODO: Room relationship should/may contain information about Equipment, etc
	
	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getSubhazards");
		$entityMaps[] = new EntityMap("lazy","getChecklist");
		$entityMaps[] = new EntityMap("lazy","getRooms");
		$entityMaps[] = new EntityMap("lazy","getInspectionRooms");
		$this->setEntityMaps($entityMaps);
		
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }
	
	public function getParent_hazard_id(){ return $this->parent_hazard_id; }
	public function setParent_hazard_id($parent_hazard_id){ $this->parent_hazard_id = $parent_hazard_id; }
	
	public function getInspectionRooms() { return $this->inspectionRooms; }
	public function setInspectionRooms($inspectionRooms){ $this->inspectionRooms = $inspectionRooms; }
	
	public function getSubHazards(){ 
		if($this->subHazards === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->subHazards = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$HAZARDS_RELATIONSHIP));
		}
		return $this->subHazards;
	}
	public function setSubHazards($subHazards){ $this->subHazards = $subHazards; }
	
	public function getChecklist(){ 
		if($this->checklist === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$checklistArray = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$CHECKLIST_RELATIONSHIP));
			if (isset($checklistArray[0])) {$this->checklist = $checklistArray[0];}
		}
		return $this->checklist;
	}
	public function setChecklist($checklist){ $this->checklist = $checklist; }
	
	public function getRooms(){ 
		if($this->rooms === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->rooms = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$ROOMS_RELATIONSHIP));
		}
		return $this->rooms;
	}
	public function setRooms($rooms){ $this->rooms = $rooms; }
	
	public function getIsPresent() {return $this->isPresent;}
	
	public function getParentIds() {return $this->parentIds;}
	
	public function setParentIds($parentIds){
		if (empty($parentIds)){
			$parentIds = array();
			$parentIds = $this->findParents($this,$parentIds);		
		} 
		$this->parentIds = $parentIds;
	}
		
	public function filterRooms(){
		$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
		$LOG->debug("Filtering rooms for hazard: " . $this->getName() . ", key_id " . $this->getKey_id());
		$this->isPresent = false;
		foreach ($this->inspectionRooms as &$room){
			$LOG->debug("Checking inspection room with key_id " . $room->getKey_id());
			foreach ($this->getRooms() as $hazroom){
				$LOG->debug("Hazard is found in room " . $hazroom->getKey_id() . " ...");
				$room->setContainsHazard(false);
				if ($room->getKey_id() == $hazroom->getKey_id()){
					$LOG->debug(".. which matches this room's key_id, ContainsHazard set to true");
					$room->setContainsHazard(true);
					// if one or more rooms has this hazard, set isPresent to true
					$this->isPresent = true;
				} else {
					$LOG->debug(".. which doesn't match this room's key_id, ContainsHazard set to false");
				}
			}
		}
	}

	private function findParents($hazard,&$parentIds) {
		if (!empty($hazard) && $hazard->getParent_hazard_id() != null){
			$thisDao = new GenericDAO($this);
			$hazard_id = $hazard->getParent_hazard_id();
			array_push($parentIds,$hazard_id);
			$this->findParents($thisDao->getById($hazard_id),$parentIds);
		} 
		return $parentIds;
	}
}
	
?>
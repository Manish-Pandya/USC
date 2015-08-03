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
		"order_index" => "text",
		"is_equipment" => "boolean",

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer"
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

	/** Hazards will be ordered by order_index in hazard hub **/
	private $order_index;
	
	/** The PIs who have relationships with rooms that have relationships with this hazard**/
	private $principalInvestigators;
	
	/** Boolean to indicate whether this Hazard has relationships with any Rooms that have more than 1 PrincipalInvestigator */
	private $hasMultiplePIs;
	
	/** Boolean to indicate whether this Hazard is a piece of equipment **/
	private $is_equipment;

	//TODO: Room relationship should/may contain information about Equipment, etc

	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getSubHazards");
		$entityMaps[] = new EntityMap("lazy","getActiveSubHazards");
		$entityMaps[] = new EntityMap("lazy","getChecklist");
		$entityMaps[] = new EntityMap("lazy","getRooms");
		$entityMaps[] = new EntityMap("lazy","getInspectionRooms");
		$entityMaps[] = new EntityMap("lazy","getHasChildren");
		$entityMaps[] = new EntityMap("lazy","getParentIds");
		$entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
		
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
	public function setInspectionRooms($inspectionRooms){
		$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

		$this->inspectionRooms = array();
		$roomDao = new GenericDAO(new Room());
		//$LOG->debug($roomDao);

		foreach ($inspectionRooms as $rm){
			//if the hazard has been received from an API call, each of its inspection rooms will be an array instead of an object, because PHP\
			//If so, we set the key id by index instead of calling the getter
			if(!is_object($rm)){
				$key_id = $rm['Key_id'];
				if(isset($rm['ContainsHazard']))$containsHazard = $rm['ContainsHazard'];
			}else{
				$key_id = $rm->getKey_id();
			}
			$room = $roomDao->getById($key_id);
			if( isset($containsHazard) )$room->setContainsHazard($containsHazard);
				
			$this->inspectionRooms[] = $room;
		}
	}

	public function getSubHazards(){
		if($this->subHazards === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->subHazards = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$HAZARDS_RELATIONSHIP),"order_index",false);
		}
		return $this->subHazards;
	}

	public function getActiveSubHazards(){
		if($this->subHazards === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->subHazards = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$HAZARDS_RELATIONSHIP),"order_index",true);
		}
		return $this->subHazards;
	}

	public function setSubHazards($subHazards){ $this->subHazards = $subHazards; }

	public function getChecklist(){
		if($this->checklist === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$checklistArray = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$CHECKLIST_RELATIONSHIP));
			if (isset($checklistArray[0])) {$this->checklist = $checklistArray[0];}
		}
		return $this->checklist;
	}
	public function setChecklist($checklist){ $this->checklist = $checklist; }

	public function getRooms(){
		if($this->rooms === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->rooms = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$ROOMS_RELATIONSHIP));
		}
		return $this->rooms;
	}
	public function setRooms($rooms){ $this->rooms = $rooms; }

	public function getIsPresent() {return $this->isPresent;}
	public function setIsPresent($isPresent) {$this->isPresent = $isPresent;}

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
/*		foreach ( $this->inspectionRooms as $room){
			$LOG->debug("Checking inspection room with key_id " . $room->getKey_id());
			foreach ($this->getRooms() as $hazroom){
				$LOG->debug("Hazard is found in room " . $hazroom->getKey_id() . " ...");
				if ($room->getKey_id() == $hazroom->getKey_id()){
					$LOG->debug(".. which matches this room's key_id, ContainsHazard set to true");
					$room->setContainsHazard(true);
					// if one or more rooms has this hazard, set isPresent to true
					$this->isPresent = true;
				} else {
					//if (!$room->getContainsHazard()) {$room->setContainsHazard(false);}
				}
			}
		}
*/
		// Get the db connection
		global $db;


		foreach($this->inspectionRooms as $room){
			$rooms[] = $room->getKey_id();
		}

		$roomIds = implode (',',$rooms);
		$queryString = "SELECT room_id FROM hazard_room WHERE hazard_id =  $this->key_id AND room_id IN ( $roomIds )";
		$LOG->debug("query: " . $queryString);
		$stmt = $db->prepare($queryString);
		$stmt->execute();
		$roomIdsToEval = array();
		while($roomId = $stmt->fetchColumn()){
			$this->isPresent = true;
			array_push($roomIdsToEval,$roomId);
		}

		foreach ($this->inspectionRooms as $room){
			$room->setContainsHazard(false);
			$LOG->debug('roomId:  '.$roomId);
			$LOG->debug('rooms: '. $rooms);
			$LOG->debug(in_array ( $room->getKey_id() , $roomIdsToEval ));
			if(in_array ( $room->getKey_id() , $roomIdsToEval )){
				$room->setContainsHazard(true);
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

	public function getHasChildren(){
		if ($this->getActiveSubHazards() != null) {
			return true;
		}
		return false;
	}

	public function getOrder_index()
	{
	    return $this->order_index;
	}

	public function setOrder_index($order_index)
	{
	    $this->order_index = $order_index;
	}

	public function getPrincipalInvestigators(){
		if($this->principalInvestigators == NULL){
			if($this->inspectionRooms == NULL){
				$this->principalInvestigators = NULL;
			}else{
				$thisDao = new GenericDAO($this);
				$this->principalInvestigators = $thisDao->getPIsByHazard($this->getInspectionRooms());
			}
		}
		return $this->principalInvestigators;
	}
	
	public function getHasMultiplePIs(){
		if($this->hasMultiplePIs == NULL){
			$this->hasMultiplePIs = false;
			if(count($this->getPrincipalInvestigators()) > 1) $this->hasMultiplePIs = true;
		}
		return $this->hasMultiplePIs;
	}
	
	public function getIs_equipment()
	{
		return (bool) $this->is_equipment;
	}	
	public function setIs_equipment(Boolean  $is_equipment)
	{
		$this->is_equipment = $is_equipment;
	}
}

?>
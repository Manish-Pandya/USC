<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Inspection extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "inspection";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//inspectors are a relationship
		"principal_investigator_id" => "integer",
		//responses are a relationship
		//rooms are a relationship
		"date_started"	=> "timestamp",
		"date_closed"	=> "timestamp",
				
		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer"
	);
	
	/** Relationships */
	public static $ROOMS_RELATIONSHIP = array(
			"className"	=>	"Room",
			"tableName"	=>	"inspection_room",
			"keyName"	=>	"room_id",
			"foreignKeyName"	=>	"inspection_id"
	);
	
	public static $RESPONSES_RELATIONSHIP = array(
			"className"	=>	"Response",
			"tableName"	=>	"response",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"inspection_id"
	);
	
	public static $INSPECTORS_RELATIONSHIP = array(
			"className"	=>	"Inspector",
			"tableName"	=>	"inspection_inspector",
			"keyName"	=>	"inspector_id",
			"foreignKeyName"	=>	"inspection_id"
	);
	
	public static $CHECKLISTS_RELATIONSHIP = array(
			"className"	=>	"Checklist",
			"tableName"	=>	"inspection_checklist",
			"keyName"	=>	"checklist_id",
			"foreignKeyName"	=>	"inspection_id"
	);
	
	/** Array of Inspector entities that took part in this Inspection */
	private $inspectors;
	
	/** Reference to the PrincipalInvestigator being inspected */
	private $principalInvestigator;
	private $principal_investigator_id;
	
	/** Array of Response entities */
	private $responses;
	
	/** Array of Checklist entities */
	private $checklists;
	
	/** Date and time this Inspection began */
	private $date_started;
	
	/** Date and time this Inspection was completed */
	private $date_closed;
	
	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getInspectors");
		$entityMaps[] = new EntityMap("eager","getRooms");
		$entityMaps[] = new EntityMap("eager","getResponses");
		$entityMaps[] = new EntityMap("eager","getPrincipalInvestigator");
		$entityMaps[] = new EntityMap("lazy","getChecklists");
		$this->setEntityMaps($entityMaps);
		
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getInspectors(){ 
		$thisDAO = new GenericDAO($this);
		$this->inspectors = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$INSPECTORS_RELATIONSHIP));
		return $this->inspectors;
	}
	public function setInspectors($inspectors){ $this->inspectors = $inspectors; }
	
	public function getChecklists(){ 
		$thisDAO = new GenericDAO($this);
		$this->checklists = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$CHECKLISTS_RELATIONSHIP));
		foreach ($this->checklists as &$checklist){
			$checklist->setInspectionId($this->key_id);
		}
		return $this->checklists;
	}
	public function setChecklists($checklists){ $this->checklists = $checklists; }
	
	public function getPrincipalInvestigator(){
		$piDAO = new GenericDAO(new PrincipalInvestigator());
		$this->principalInvestigator = $piDAO->getById($this->principal_investigator_id);
		return $this->principalInvestigator;
	}
	public function setPrincipalInvestigator($principalInvestigator){
		$this->principalInvestigator = $principalInvestigator; 
	}
	
	public function getPrincipal_investigator_id(){ return $this->principal_investigator_id; }
	public function setPrincipal_investigator_id($principal_investigator_id){ $this->principal_investigator_id = $principal_investigator_id; }
	
	public function getResponses(){ 
		$thisDAO = new GenericDAO($this);
		$this->responses = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$RESPONSES_RELATIONSHIP));
		return $this->responses;
	}
	
	public function getRooms(){ 
		$thisDAO = new GenericDAO($this);
		$this->rooms = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$ROOMS_RELATIONSHIP));
		return $this->rooms;
	}
	public function setResponses($responses){ $this->responses = $responses; }
	
	public function getDate_started(){ return $this->date_started; }
	public function setDate_started($date_started){ $this->date_started = $date_started; }
	
	public function getDate_closed(){ return $this->date_closed; }
	public function setDate_closed($date_closed){ $this->date_closed = $date_closed; }
}
?>
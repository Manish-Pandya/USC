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
	protected static $ROOMS_RELATIONSHIP = array(
			"className"	=>	"Room",
			"tableName"	=>	"inspection_room",
			"keyName"	=>	"room_id",
			"foreignKeyName"	=>	"inspection_id"
	);
	
	protected static $RESPONSE_RELATIONSHIP = array(
			"className"	=>	"Response",
			"tableName"	=>	"inspection_response",
			"keyName"	=>	"response_id",
			"foreignKeyName"	=>	"inspection_id"
	);
	
	protected static $INSPECTORS_RELATIONSHIP = array(
			"className"	=>	"Inspector",
			"tableName"	=>	"inspection_inspector",
			"keyName"	=>	"inspector_id",
			"foreignKeyName"	=>	"inspection_id"
	);
	
	/** Array of Inspector entities that took part in this Inspection */
	private $inspectors;
	
	/** Reference to the PrincipalInvestigator being inspected */
	private $principalInvestigator;
	private $principal_investigator_id;
	
	/** Array of Response entities */
	private $responses;
	
	/** Date and time this Inspection began */
	private $dateStarted;
	
	/** Date and time this Inspection was completed */
	private $dateClosed;
	
	public function __construct(){
	
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getInspectors(){ 
		if($this->inspectors === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->inspectors = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$INSPECTORS_RELATIONSHIP));
		}
		return $this->inspectors;
	}
	public function setInspectors($inspectors){ $this->inspectors = $inspectors; }
	
	public function getPrincipalInvestigator(){
		if($this->principalInvestigator == null) {
			$piDAO = new GenericDAO("PrincipalInvestigator");
			$this->principalInvestigator = $piDAO->getById($this->principal_investigator_id);
		}
		return $this->principalInvestigator;
	}
	public function setPrincipalInvestigator($principalInvestigator){
		$this->principalInvestigator = $principalInvestigator; 
		$this->principal_investigator_id = $principalInvestigator->getKey_id();
	}
	
	public function getPrincipal_investigator_id(){ return $this->principal_investigator_id; }
	public function setPrincipal_investigator_id($principal_investigator_id){ $this->principal_investigator_id = $principal_investigator_id; }
	
	public function getResponses(){ 
		if($this->responses === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->responses = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$RESPONSES_RELATIONSHIP));
		}
		return $this->responses;
	}
	public function setResponses($responses){ $this->responses = $responses; }
	
	public function getRooms(){ 
		if($this->rooms === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->rooms = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$ROOMS_RELATIONSHIP));
		}
		return $this->rooms;
	}
	public function setResponses($responses){ $this->responses = $responses; }
	
	public function getDateStarted(){ return $this->dateStarted; }
	public function setDateStarted($dateStarted){ $this->dateStarted = $dateStarted; }
	
	public function getDateClosed(){ return $this->dateClosed; }
	public function setDateClosed($dateClosed){ $this->dateClosed = $dateClosed; }
}
?>
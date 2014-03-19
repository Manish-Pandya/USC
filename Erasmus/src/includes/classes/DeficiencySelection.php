<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class DeficiencySelection extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "deficiency_selection";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		
		//response is a relationship
		"response_id"	=>	"integer",
		//rooms is a relationship
		//deficiency is a relationship
		"deficiency_id"	=>	"integer",
		//deficiency root causes are relationships
		//corrective actions are relationships

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
			"tableName"	=>	"deficiency_selection_room",
			"keyName"	=>	"room_id",
			"foreignKeyName"	=>	"deficiency_selection_id"
	);
	
	protected static $CAUSES_RELATIONSHIP = array(
			"className"	=>	"DeficiencyRootCause",
			"tableName"	=>	"deficiency_selection_root_cause",
			"keyName"	=>	"deficiency_root_cause_id",
			"foreignKeyName"	=>	"deficiency_selection_id"
	);
	
	protected static $CORRECTIVE_ACTIONS_RELATIONSHIP = array(
			"className"	=>	"CorrectiveAction",
			"tableName"	=>	"corrective_action",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"deficiency_selection_id"
	);
	
	/** Reference to the Response entity to which the associated Deficiency is applied */
	private $response;
	private $response_id;
	
	/** Reference to the Deficiency entity that was selected */
	private $deficiency;
	private $deficiency_id;
	
	/** Array of Room entities in which the associated Deficiency applies */
	private $rooms;
	
	/** Array of DeficiencyRootCause entities that were selected with the associated Deficiency */
	private $deficiencyRootCauses;
	
	/** Array of CorrectiveAction entities describing this Deficiency's resolution */
	private $correctiveActions;
	
	public function __construct(){
	
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getResponse(){
		if($this->response == null) {
			$responseDAO = new GenericDAO("Response");
			$this->response = $responseDAO->getById($this->response_id);
		}
		return $this->inspection;
	}
	public function setResponse($response){
		$this->response = $response; 
	}
	
	public function getResponse_id() { return $this->response_id;	}
	public function setResponse_id($response_id) {$this->response_id = $response_id;}
	
	public function getRooms(){ 
		if($this->rooms === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->rooms = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$ROOMS_RELATIONSHIP));
		}
		return $this->rooms;
	}
	public function setRooms($rooms){ $this->rooms = $rooms; }
	
	public function getDeficiency(){
		if($this->deficiency == null) {
			$deficiencyDAO = new GenericDAO("Deficiency");
			$this->deficiency = $deficiencyDAO->getById($this->deficiency_id);
		}
		return $this->deficiency; 
	}
	public function setDeficiency($deficiency){
		$this->deficiency = $deficiency; 
	}

	public function getDeficiency_id() { return $this->deficiency_id;	}
	public function setDeficiency_id($deficiency_id) {$this->deficiency_id = $deficiency_id;}
	
	public function getDeficiencyRootCauses(){ 
		if($this->deficiencyRootCauses === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->deficiencyRootCauses = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$CAUSES_RELATIONSHIP));
		}
		return $this->deficiencyRootCauses;
	}
	public function setDeficiencyRootCauses($deficiencyRootCauses){ $this->deficiencyRootCauses = $deficiencyRootCauses; }
	
	public function getCorrectiveActions(){ 
		if($this->correctiveActions === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->correctiveActions = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$CORRECTIVE_ACTIONS_RELATIONSHIP));
		}
		return $this->correctiveActions;
	}
	public function setCorrectiveActions($correctiveActions){ $this->correctiveActions = $correctiveActions; }
}
?>
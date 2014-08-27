<?php

include_once 'GenericCrud.php';

/**
 * 
 * 
 * 
 * @author Perry Cate, GraySail LLC
 */
class Pickup extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "pickup";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"pickup_date"					=> "DateTime",
		"room_id"						=> "integer",
		"pickup_user_id"				=> "integer",
		"principal_investigator_id"		=> "integer",
		
		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);
	
	//access information
	
	/** Date (DateTime) that this pickup occurred. */
	private $pickup_date;
	
	/** Reference to the room this pickup was for. */
	private $room;
	/** Integer id of the room this pickup was for. */
	private $room_id;
	
	/** Integer id of the user who picked up the materials. */
	private $pickup_user_id;

	/** Reference to the principal investigator who requested this pickup. */
	private $principal_investigator;
	/** Integer id of the principal investigator who requested this pickup. */
	private $principal_investigator_id;
	

	
	public function __construct() {
		
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getRooms");
		$entityMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
		$this->setEntityMaps($entityMaps);

	}
	
	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}
	
	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	// Accessors / Mutators
	public function getPickup_date() { return $this->pickup_date; }
	public function setPickup_date($newDate) { $this->pickup_date = $newDate; }
	
	public function getRoom() {
		if($this->room == null) {
			$roomDAO = new GenericDAO(new Room());
			$this->room = $roomDAO->getById($this->getRoom_id());
		}
	}
	public function setRoom($newRoom) {
		$this->room = $newRoom;
	}
	
	public function getRoom_id() { return $this->room_id; }
	public function setRoom_id($newId) { $this->room_id = $newId; }

	public function getPickup_user_id() { return $this->pickup_user_id; }
	public function setPickup_user_id($newId) { $this->pickup_user_id = $newId; }
	
	public function getPrincipal_investigator() {
		if($this->principal_investigator == null) {
			$piDAO = new GenericDAO(new PrincipalInvestigator());
			$this->principal_investigator = $piDAO->getById($this->getPrincipal_investigator_id());
		}
		return $this->principal_investigator;
	}
	public function setPrincipal_investigator($newPi) {
		$this->principal_investigator = $newPi;
	}
	
	public function getPrincipal_investigator_id() { return $this->principal_investigator_id; }
	public function setPrincipal_investigator_id($newId) { $this->principal_investigator_id = $newId; }
}
?>
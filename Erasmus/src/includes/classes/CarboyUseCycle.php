<?php

include_once 'GenericCrud.php';

/**
 * 
 * 
 * 
 * @author Perry Cate, GraySail LLC
 */
class CarboyUseCycle extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "carboy_use_cycle";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"carboy_id"						=> "integer",
		"curie_level"					=> "float",
		"principal_investigator_id"		=> "integer",
		"status"						=> "text",
		"lab_date"						=> "timestamp",
		"hotroom_date"					=> "timestamp",
		"pour_date"						=> "timestamp",
		"room_id"						=> "integer",
		"isotope_id"					=> "integer",
		"pickup_id"						=> "integer",
			
		
		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);
	
	//access information
	
	/** Reference to the carboy this use cycle refers to. */
	private $carboy;
	private $carboy_id;
	
	/** Float ammount in curies of the radiation this carboy contains. */
	private $curie_level;
	
	/** Reference to the principal investigator this carboy belongs to. */
	private $principal_investigator;
	private $principal_investigator_id;
	
	/** String describing the current status of this carboy. */
	private $status;
	
	/** DateTime containing the date this carboy was sent to a lab. */
	private $lab_date;
	
	/** DateTime containing the date this carboy was sent to the hotroom. */
	private $hotroom_date;
	
	/** DateTime containing the date this carboy was emptied. */
	private $pour_date;
	
	/** Reference to the room this carboy was sent to. */
	private $room;
	private $room_id;
	
	/** Reference to the isotope this carboy contains. */
	private $isotope;
	private $isotope_id;
	
	/** Reference to the pickup that removed this carboy from the lab. */
	private $pickup;
	private $pickup_id;
	
	
	public function __construct() {
		
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager", "getCarboy");
		$entityMaps[] = new EntityMap("lazy", "getPrincipalInvestigator");
		$entityMaps[] = new EntityMap("eager", "getRoom");
		$entityMaps[] = new EntityMap("eager", "getIsotope");
		$entityMaps[] = new EntityMap("lazy", "getPickup");
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
	public function getCarboy() {
		if($this->carboy = null) {
			$carboyDAO = new GenericDAO(new carboy());
			$this->carboy = $carboyDAO->getById($this->getCarboy_id());
		}
		return $this->carboy;
	}
	public function setCarboy($newCarboy) {
		$this->carboy = $newCarboy;
	}
	
	public function getCarboy_id() { return $this->carboy_id; }
	public function setCarboy_id($newId) { $this->carboy_id = $newId; }
	
	public function getCurie_level() { return $this->curie_level; }
	public function setCurie_level($newLevel) { $this->curie_level = $newLevel; }
	
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
	
	public function getStatus() { return $this->status; }
	public function setStatus($newStatus) { $this->status = $newStatus; }
	
	public function getLab_date() { return $this->lab_date; }
	public function setLab_date($newDate) { $this->lab_date = $newDate; }
	
	public function getHotroom_date() { return $this->hotroom_date; }
	public function setHotroom_date($newDate) { $this->hotroom_date = $newDate; }
	
	public function getPour_date() { return $this->pour_date; }
	public function setPour_date($newDate) { $this->pour_date = $newDate; }
	
	public function getRoom() {
		if($this->room == null) {
			$roomDAO = new GenericDAO(new Room());
			$this->room = $roomDAO->getById($this->getRoom_id());
		}
		return $this->room;
	}
	public function setRoom($newRoom) {
		$this->room = $newRoom;
	}
	
	public function getRoom_id() { return $this->room_id; }
	public function setRoom_id($newId) { $this->room_id = $newId; }
	
	
	public function getIsotope() {
		if($this->isotope == null) {
			$isotopeDAO = new GenericDAO(new Isotope());
			$this->isotope = $isotopeDAO->getById($this->getIsotope_id());
		}
		return $this->isotope;
	}
	public function setIsotope($newIsotope) {
		$this->isotope = $newIsotope;
	}
	
	public function getIsotope_id() { return $this->isotope_id; }
	public function setIsotope_id($newId) { $this->isotope_id = $newId; }
	
	public function getPickup() {
		if($this->pickup == null) {
			$pickupDAO = new GenericDAO(new Pickup());
			$this->pickup = $pickupDAO->getById($this->getPickup_id());
		}
		return $this->pickup;
	}
	public function setPickup($newPickup) {
		$this->pickup = $newPickup;
	}
	
	public function getPickup_id() { return $this->pickup_id; }
	public function setPickup_id($newId) { $this->pickup_id = $newId; }
	
	
}
?>
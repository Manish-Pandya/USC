<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Perry Cate, GraySail LLC
 */
class SolidsContainer extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "solids_container";

	/** Key/value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"principal_investigator_id"		=> "integer",
		"room_id"						=> "integer",
		
		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);		
	
	//access information
	
	/** integer key id of the principal investigator this container belongs to. */
	private $principal_investigator_id;
	private $principal_investigator;
	
	/** integer key id of the room this container is in */
	private $room_id;
	private $room;
	
	public function __construct() {
		
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
		$entityMaps[] = new EntityMap("lazy", "getRoom");
	}
	
	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}
	
	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	// Accessors / Mutators
	public function getPrincipal_investigator_id() { return $this->principal_investigator_id; }
	public function setPrincipal_investigator_id($newId) { $this->principal_investigator_id = $newId; }
	
	public function getPrincipal_investigator() {
		if($this->principal_investigator === null && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->principal_investigator = $thisDao->getById($this->getKey_id());
		}
		return $this->principal_investigator;
	}
	public function setPrincipal_investigator($newPI) {
		$this->principal_investigator = $newPI;
	}
	
	public function getRoom_id() { return $this->room_id; }
	public function setRoom_id($newId) { $this->room_id = $newId; }
	
	public function getRoom() {
		if($this->room === null && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->room = $thisDao->getById($this->getKey_id());
		}
		return $this->room;
	}
	public function setRoom($newRoom) {
		$this->room = $newRoom;
	}
	
}
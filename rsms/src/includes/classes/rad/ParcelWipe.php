<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class ParcelWipe extends RadCrud {
	/** Name of the DB Table */
	protected static $TABLE_NAME = "parcel_wipe";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
			"curie_level"					=> "float",
			"reading_type"					=> "text",
			"notes"							=> "text",
			"parcel_wipe_test_id"			=> "integer",
			"location"						=> "text",
	
			//GenericCrud
			"key_id"						=> "integer",
			"is_active"						=> "boolean",
			"date_last_modified"			=> "timestamp",
			"last_modified_user_id"			=> "integer",
			"date_created"					=> "timestamp",
			"created_user_id"				=> "integer"
	);
	
	public function __construct(){
	
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getParcel_wipe_test");
		$entityMaps[] = new EntityMap("lazy","getRoom");
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
	//access information

	private $curie_level;
	private $notes;
	
	/** Wipe test readings can be done with LSC, Alpha/Beta or MCA counters  **/
	private $reading_type;
	
	/** The location on the parcel that was wiped **/
	private $location;
	
	private $parcel_wipe_test_id;
	private $parcel_wipe_test;
		
	public function getRoom(){
		$roomDAO = new GenericDAO(new Room());
		$this->room = $roomDAO->getById($this->room_id);
		return $this->room;
	}
	
	public function getCurie_level() {return $this->curie_level;}
	public function setCurie_level($curie_level) {$this->curie_level = $curie_level;}
	
	public function getNotes() {return $this->notes;}
	public function setNotes($notes) {$this->notes = $notes;}
	
	public function getParcel_wipe_test_id() {return $this->parcel_wipe_test_id;}
	public function setParcel_wipe_test_id($parcel_wipe_test_id) {$this->parcel_wipe_test_id = $parcel_wipe_test_id;}
	
	public function getParcel_wipe_test() {
		$parcelWipeTestDAO = new GenericDAO(new ParcelWipeTest());
		$this->parcel_wipe_test = $parcelWipeTestDAO->getById($this->parcel_wipe_test_id);
		return $this->parcel_wipe_test;
	}
	public function getReading_type() {return $this->reading_type;}
	public function setReading_type($reading_type) {$this->reading_type = $reading_type;}
	
	public function getLocation() {return $this->location;}
	public function setLocation($location) {$this->location = $location;}
}
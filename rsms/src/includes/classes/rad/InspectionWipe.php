<?php

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class InspectionWipe extends RadCrud {
	/** Name of the DB Table */
	protected static $TABLE_NAME = "inspection_wipe";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
			"room_id"					    => "integer",
			"curie_level"					=> "float",
			"lab_curie_level"				=> "float",
			"notes"							=> "text",
			"inspection_wipe_test_id"		=> "integer",
			"reading_type"					=> "text",
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
		$entityMaps[] = new EntityMap("lazy","getInspection_wipe_test");
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
	
	private $room_id;
	private $room;
	
	/*  the radioactivity of this wipe as read by RSO staff */
	private $curie_level;
	
	/** the radioactivity of this wipe as read by lab staff, if the wipe was hot and a new wipe was required*/
	private $lab_curie_level;
	
	private $notes;
	
	private $inspection_wipe_test_id;
	private $inspection_wipe_test;
	
	private  $reading_type;
	
	/** location within the room where this wipe was performed **/
	private $location;
	
	public function getRoom_id() {return $this->room_id;}
	public function setRoom_id($room_id) {$this->room_id = $room_id;}
	
	public function getRoom(){
		$inspectionWipeTestDAO = new GenericDAO(new Room());
		$this->room = $inspectionWipeTestDAO->getById($this->room_id);
		return $this->room;
	}
	
	public function getCurie_level() {return $this->curie_level;}
	public function setCurie_level($curie_level) {$this->curie_level = $curie_level;}
	
	public function getLab_curie_level() {return $this->lab_curie_level;}
	public function setLab_curie_level($lab_curie_level) {$this->lab_curie_level = $lab_curie_level;}
	
	public function getNotes() {return $this->notes;}
	public function setNotes($notes) {$this->notes = $notes;}
	
	public function getInspection_wipe_test_id() {return $this->inspection_wipe_test_id;}
	public function setInspection_wipe_test_id($inspection_wipe_test_id) {$this->inspection_wipe_test_id = $inspection_wipe_test_id;}
	
	public function getInspection_wipe_test() {
		$inspectionWipeTestDAO = new GenericDAO(new InspectionWipeTest());
		$this->inspection_wipe_test = $inspectionWipeTestDAO->getById($this->inspection_wipe_test_id);
		return $this->inspection_wipe_test;
	}
	
	public function getLocation() {return $this->location;}
	public function setLocation($location) {$this->location = $location;}
	
	public function getReading_type() {return $this->reading_type;}
	public function setReading_type($reading_type) {$this->reading_type = $reading_type;}
	
	
}
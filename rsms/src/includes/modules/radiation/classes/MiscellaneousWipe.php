<?php

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */

class MiscellaneousWipe extends RadCrud {
	/** Name of the DB Table */
	protected static $TABLE_NAME = "miscellaneous_wipe";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
			"curie_level"					=> "float",
			"reading_type"					=> "text",
			"notes"							=> "text",
			"miscellaneous_wipe_test_id"	=> "integer",
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

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getMiscellaneous_wipe_test");
		$entityMaps[] = EntityMap::lazy("getRoom");
		return $entityMaps;
	}
	
	//access information
	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}
	
	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	// Accessors / Mutators
	private $curie_level;
	private $notes;
	
	/** Wipe test readings can be done with LSC, Alpha/Beta or MCA counters  **/
	private $reading_type;
	
	/** The location on the parcel that was wiped **/
	private $location;
	
	private $miscellaneous_wipe_test_id;
	private $miscellaneous_wipe_test;
		
	public function getCurie_level() {return $this->curie_level;}
	public function setCurie_level($curie_level) {$this->curie_level = $curie_level;}
	
	public function getNotes() {return $this->notes;}
	public function setNotes($notes) {$this->notes = $notes;}
	
	public function getMiscellaneous_wipe_test_id() {return $this->miscellaneous_wipe_test_id;}
	public function setMiscellaneous_wipe_test_id($miscellaneous_wipe_test_id) {$this->miscellaneous_wipe_test_id = $miscellaneous_wipe_test_id;}
	
	public function getMiscellaneous_wipe_test() {
		$miscWipeTestDAO = new GenericDAO(new MiscellaneousWipeTest());
		$this->miscellaneous_wipe_test = $miscWipeTestDAO->getById($this->miscellaneous_wipe_test_id);
		return $this->miscellaneous_wipe_test;
	}
	
	public function getReading_type() {return $this->reading_type;}
	public function setReading_type($reading_type) {$this->reading_type = $reading_type;}
	
	public function getLocation() {return $this->location;}
	public function setLocation($location) {$this->location = $location;}
	
}
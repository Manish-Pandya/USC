<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */

class PIWipe extends RadCrud {
	/** Name of the DB Table */
	protected static $TABLE_NAME = "pi_wipe";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
			"curie_level"					=> "float",
			"reading_type"					=> "text",
			"notes"							=> "text",
			"pi_wipe_test_id"	            => "integer",
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
		$entityMaps[] = new EntityMap("lazy","getPIWipeTest");
		$entityMaps[] = new EntityMap("lazy","getRoom");
		$this->setEntityMaps($entityMaps);

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

	private $pi_wipe_test_id;
	private $pIWipeTest;

	public function getCurie_level() {return $this->curie_level;}
	public function setCurie_level($curie_level) {$this->curie_level = $curie_level;}

	public function getNotes() {return $this->notes;}
	public function setNotes($notes) {$this->notes = $notes;}

	public function getPi_wipe_test_id() {return $this->pi_wipe_test_id;}
	public function setPi_wipe_test_id($pi_wipe_test_id) {$this->pi_wipe_test_id = $pi_wipe_test_id;}

	public function getPIWipeTest() {
		$miscWipeTestDAO = new GenericDAO(new MiscellaneousWipeTest());
		$this->pIWipeTest = $miscWipeTestDAO->getById($this->pi_wipe_test_id);
		return $this->pIWipeTest;
	}

	public function getReading_type() {return $this->reading_type;}
	public function setReading_type($reading_type) {$this->reading_type = $reading_type;}

	public function getLocation() {return $this->location;}
	public function setLocation($location) {$this->location = $location;}

}
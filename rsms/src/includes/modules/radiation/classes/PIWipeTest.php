<?php

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class PIWipeTest extends RadCrud {
	/** Name of the DB Table */
	protected static $TABLE_NAME = "pi_wipe_test";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(

			"notes"							=> "text",
			"closeout_date"					=> "date",
			"reading_type"					=> "text",
			"background_level"				=> "float",
			"lab_background_level"			=> "float",
            "principal_investigator_id"     => "integer",

			//GenericCrud
			"key_id"						=> "integer",
			"is_active"						=> "boolean",
			"date_last_modified"			=> "timestamp",
			"last_modified_user_id"			=> "integer",
			"date_created"					=> "timestamp",
			"created_user_id"				=> "integer"
	);

	/** Relationships */
	public static $PI_WIPE_RELATIONSHIP = array(
			"className" => "PIWipe",
			"tableName" => "pi_wipe",
			"keyName"   => "key_id",
			"foreignKeyName" => "pi_wipe_test_id"
	);


	public function __construct(){

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getPIWipes");
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
	private $pIWipes;
	private $notes;

    private $principal_investigator_id;

	/** Wipe test readings can be done with LSC, Alpha/Beta or MCA counters  **/
	private $reading_type;

	private $closeout_date;

	private $background_level;
	private $lab_background_level;

	public function getNotes(){return $this->notes;}
	public function setNotes($notes){$this->notes = $notes;}

	public function getPIWipes() {
		if($this->piWipes == null && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->piWipes = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PI_WIPE_RELATIONSHIP));
		}
		return $this->piWipes;
	}
	public function setPIWipes($wipes){$this->piWipes = $wipes;}

	public function getCloseout_date() {return $this->closeout_date;}
	public function setCloseout_date($closeout_date) {$this->closeout_date = $closeout_date;}

	public function getReading_type() {return $this->reading_type;}
	public function setReading_type($reading_type) {$this->reading_type = $reading_type;}

	public function getBackground_level() {return $this->background_level;}
	public function setBackground_level($background_level) {$this->background_level = $background_level;}

	public function getLab_background_level() {return $this->lab_background_level;}
	public function setLab_background_level($lab_background_level) {$this->lab_background_level = $lab_background_level;}

    public function getPrincipal_investigator_id(){return $this->principal_investigator_id;}
    public function setPrincipal_investigator_id($id){$this->principal_investigator_id = $id;}

}
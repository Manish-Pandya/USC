<?php

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class InspectionWipeTest extends RadCrud {
	/** Name of the DB Table */
	protected static $TABLE_NAME = "inspection_wipe_test";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
			"inspection_id"					=> "integer",
			"reading_type"					=> "text",
			"background_level"				=> 'float',
			"lab_background_level"			=> 'float',
				
			//GenericCrud
			"key_id"						=> "integer",
			"is_active"						=> "boolean",
			"date_last_modified"			=> "timestamp",
			"last_modified_user_id"			=> "integer",
			"date_created"					=> "timestamp",
			"created_user_id"				=> "integer"
	);
	
	/** Relationships */
	public static $INSPECTION_WIPE_RELATIONSHIP = array(
			"className" => "InspectionWipe",
			"tableName" => "inspection_wipe",
			"keyName"   => "key_id",
			"foreignKeyName" => "inspection_wipe_test_id"
	);
	
	public function __construct(){
	
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getInspection");		
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
	
	private $inspection_id;
	private $inspection;
	
	/** Wipe test readings can be done with LSC, Alpha/Beta or MCA counters  **/
	private $reading_type;
	
	private $inspection_wipes;
	
	/** background level reading done by RSO staff */
	private $background_level;
	
	/** background level reading done by Lab for corrective action, when an inspection has hot wipes */
	private $lab_background_level;
	
	public function getInspection_id(){return $this->inspection_id;}
	public function setInspection_id($id){$this->inspection_id = $id;}
	
	public function getInspection(){
		$inspectionDAO = new GenericDAO(new Inspection());
		$this->inspection = $inspectionDAO->getById($this->inspection_id);
		return $this->inspection;
	}
	
	public function getWipe_test() {
		
	}
	
	public function getReading_type() {return $this->reading_type;}
	public function setReading_type($reading_type) {$this->reading_type = $reading_type;}
	
	public function getInspection_wipes() {
		if($this->inspection_wipes == null && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->inspection_wipes = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$INSPECTION_WIPE_RELATIONSHIP));
		}
		return $this->inspection_wipes;	
	}
	public function setInspection_wipes($wipes){$this->inspection_wipes = $wipes;}
	
	public function getBackground_level() {return $this->background_level;}
	public function setBackground_level($background_level) {$this->background_level = $background_level;}
	
	public function getLab_background_level() {return $this->lab_background_level;}
	public function setLab_background_level($lab_background_level) {$this->lab_background_level = $lab_background_level;}
	
	
}
<?php

include_once 'RadCrud.php';

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
		$entityMaps[] = new EntityMap("eager","getInspection");
		
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
	
	private $inspection_wipes;
	
	public function getInspection_id(){return $this->inspection_id;}
	public function setInspection_id($id){$this->inspection_id = $id;}
	
	public function getInspection(){
		$inspectionDAO = new GenericDAO(new Inspection());
		$this->inspection = $inspectionDAO->getById($this->inspection_id);
		return $this->inspection;
	}
	
	public function getWipe_test() {
		
	}
	
	public function getInspection_wipes() {
		if($this->inspection_wipe == null && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->inspection_wipe = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$INSPECTION_WIPE_RELATIONSHIP));
		}
		return $this->inspection_wipe;	
	}
	
}
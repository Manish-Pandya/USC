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
	
	//access information
	
	private $inspection_id;
	private $inspection;
	
	public function getInspection_id(){return $this->inspection_id;}
	public function setInspection_id($id){$this->inspection_id = $id;}
	
	public function getInspection(){
		$inspectionDAO = new GenericDAO(new Inspection());
		$this->inspection = $inspectionDAO->getById($this->inspection_id);
		return $this->inspection;
	}
}
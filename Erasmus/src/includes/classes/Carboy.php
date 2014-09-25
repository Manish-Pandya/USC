<?php

include_once 'GenericCrud.php';

/**
 * 
 * 
 * 
 * @author Perry Cate, GraySail LLC
 */
class Carboy extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "carboy";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"commission_date"				=> "timestamp",
		"retirement_date"				=> "timestamp",
		
		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);
	
	//access information
	
	/** timestamp with the date this carboy was made */
	private $commission_date;
	
	/** timestamp with the date this carboy should be thrown away */
	private $retirement_date;

	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}
	
	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	// Accessors / Mutators
	public function getCommission_date() { return $this->commission_date; }
	public function setCommission_date($newDate) { $this->commission_date = $newDate; }
	
	public function getRetirement_date() { return $this->retirement_date; }
	public function setRetirement_date($newDate) { $this->retirement_date = $newDate; }

}
?>
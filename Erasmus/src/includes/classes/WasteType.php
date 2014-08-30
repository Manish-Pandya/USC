<?php

include_once 'GenericCrud.php';

/**
 * 
 * 
 * 
 * @author Perry Cate, GraySail LLC
 */
class WasteType extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "waste_type";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"							=> "text",
		
		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);
	
	// Access information

	/** String containing the name of this type of waste. */
	private $name;
	
	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}
	
	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	// Getters / Setters
	public function getName() { return $this->name; }
	public function setName($newName) { $this->name = $newName; }

	
}
?>
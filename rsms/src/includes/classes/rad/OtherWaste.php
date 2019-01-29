<?php

/**
 *
 *
 *
 * @author Perry Cate, GraySail LLC
 */
class OtherWaste extends RadCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "other_waste";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"pickup_id"						=> "integer",
		"contents"						=> "text",
		"amount"						=> "float",
		"comments"						=> "text",

		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);

	//access information

	/** Reference to the carboy this use cycle refers to. */
	private $pickup_id;
	
	private $amount;
	private $contents;
	
	public function __construct() {}
	
	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}
	
	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	public function getPickup_id(){return $this->pickup_id;}
	public function setPickup_id($id){$this->pickup_id = $id;}
	
	public function getContents(){return $this->contents;}
	public function setContents($contents){$this->contents = $contents;}
	
	public function getAmount(){return $this->amount;}
	public function setAmount($amount){$this->amount = $amount;}
	
	public function getComments(){return $this->comments;}
	public function setComments($commies){$this->comments = $commies;}
	
}
?>
<?php

/**
 * RadCondition short summary.
 *
 * RadCondition description.
 *
 * @version 1.0
 * @author Matt Breeden
 */
class RadCondition extends RadCrud {

    /** Name of the DB Table */
	protected static $TABLE_NAME = "rad_condition";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		
		"text"							=> "text",

		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);

	//access information

	private $text;
    private $order_index;

	public function __construct() {

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getIsotope");
		$this->setEntityMaps($entityMaps);

	}

	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}

	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}

    public function getText(){ return $this->text; }
    public function setText($text) { $this->text = $text; }

    public function getOrder_index(){return $this->order_index;}
    public function setOrder_index($i){$this->order_index = $i;}

}
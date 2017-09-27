<?php

/**
 * OtherWasteType short summary.
 *
 * OtherWasteType description.
 *
 * @version 1.0
 * @author Matt Breeden
 */
class OtherWasteType extends RadCrud
{

	/** Name of the DB Table */
	protected static $TABLE_NAME = "other_waste_type";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"							=> "text",
        "clearable"						=> "boolean",

		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);

	public function __construct() {
		$entityMaps = array();
		$this->setEntityMaps($entityMaps);
	}


	// Access information

	/** String containing the name of this type of waste. */
	private $name;

    /**
     * Boolean to determine if this subtype of other waste much be cleared by RSO(true) or should be picked up(false)
     * @var boolean
     * */
    private $clearable;

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

    public function getClearable(){return (boolean) $this->clearable;}
    public function setClearable($clearable){return $this->clearable = $clearable;}
}
<?php

/**
 * ChemFumeHoodFeature short summary.
 *
 * ChemFumeHoodFeature description.
 *
 * @version 1.0
 * @author intoxopox
 */
class ChemFumeHoodFeature extends GenericCrud {
	/** Name of the DB Table */
	protected static $TABLE_NAME = "chem_fume_hood_feature";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
        "name"		            => "text",

		//GenericCrud
		"key_id"			    => "integer",
		"date_created"		    => "timestamp",
		"date_last_modified"    => "timestamp",
		"is_active"			    => "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"	    => "integer"
	);

    private $name;

	public function __construct(){
        parent::__construct();
	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
        //return array_merge(parent::$COLUMN_NAMES_AND_TYPES, this:);
		return self::$COLUMN_NAMES_AND_TYPES;
	}

    public function getName(){
		return $this->name;
	}

	public function setName($name){
		$this->name = $name;
	}

}
<?php

include_once '../GenericCrud.php';

/**
 *
 *
 *
 * @author David Hamiter
 */
class BioSafetyCabinet extends Equipment {

    /** Name of the DB Table */
	protected static $TABLE_NAME = "biosafety_cabinet";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
        "type"		            		=> "text",
        "serial_number"		        	=> "text",
        "make"          	   			=> "text",
        "model"     		    		=> "text",
				
		//GenericCrud
		"key_id"			    => "integer",
		"date_created"		    => "timestamp",
		"date_last_modified"    => "timestamp",
		"is_active"			    => "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"	    => "integer"
	);
    
	public function __construct(){
		// Define which subentities to load
		$entityMaps = array();
	}
    
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
        //return array_merge(parent::$COLUMN_NAMES_AND_TYPES, this:);
		return self::$COLUMN_NAMES_AND_TYPES;
	}

}
?>
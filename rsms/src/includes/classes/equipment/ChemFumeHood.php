<?php

include_once '../GenericCrud.php';

/**
 *
 *
 *
 * @author David Hamiter
 */
class ChemFumeHood extends Equipment {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "chem_fume_hood";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
        "type"		            		=> "text",
        "serial_number"		        	=> "text",
        "make"          	   			=> "text",
        "model"     		    		=> "text",
        "comments"                      => "text",

		"id_number"						=> "",

		//GenericCrud
		"key_id"			    => "integer",
		"date_created"		    => "timestamp",
		"date_last_modified"    => "timestamp",
		"is_active"			    => "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"	    => "integer"
	);

    private $selectedInspection;
	private $id_number;

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

    public function getSelectedInspection(){
		return $this->selectedInspection;
	}

	public function setSelectedInspection($selectedInspection){
		$this->selectedInspection = $selectedInspection;
	}

	public function getId_number(){
		return $this->id_number;
	}
	public function setId_number($id_number){
		$this->id_number = $id_number;
	}

}
?>
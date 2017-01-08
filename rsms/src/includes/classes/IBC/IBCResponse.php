<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class IBCResponse extends GenericCrud
{
    /** Name of the DB Table */
	protected static $TABLE_NAME = "ibc_response";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"protocol_id"			=> "integer",
		"answer_id"				=> "integer",
		"free_text"             => "text",
        "is_selected"           => "boolean",
        "grid_row"              => "integer",

		//GenericCrud
		"key_id"				=> "integer",
		"date_created"			=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"				=> "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"		=> "integer",
	);

	private $protocol_id;
    /* Not all responses will have an answer id.  Some responses are free text*/
	private $answer_id;
	private $free_text;
	private $is_selected;
    private $grid_row;

	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$this->setEntityMaps($entityMaps);
	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

    public function getProtocol_id(){return $this->protocol_id;}
	public function setProtocol_id($protocol_id){$this->protocol_id = $protocol_id;}

	public function getAnswer_id(){return $this->answer_id;}
	public function setAnswer_id($answer_id){$this->answer_id = $answer_id;}

	public function getFree_text(){return $this->free_text;}
	public function setFree_text($free_text){$this->free_text = $free_text;}

	public function getIs_selected(){return $this->is_selected;}
	public function setIs_selected($is_selected){$this->is_selected = $is_selected;}

	public function getGrid_row(){return $this->grid_row;}
	public function setGrid_row($grid_row){$this->grid_row = $grid_row;}

}
<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class IBCAnswer extends GenericCrud
{



	/** Name of the DB Table */
	protected static $TABLE_NAME = "ibc_answer";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"question_id"			=> "integer",
		"response_type_id"		=> "integer",
        "response_type"         => "text",
        "grid_column_index"		=> "integer",
		"grid_column_name"		=> "text",

		//GenericCrud
		"key_id"				=> "integer",
		"date_created"			=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"				=> "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"		=> "integer",
	);

    /** Relationships */
	public static $RESPONSES_RELATIONSHIP = array(
		"className"	=>	"IBCResponse",
		"tableName"	=>	"ibc_response",
		"keyName"	=>	"key_id",
		"foreignKeyName"	=>	"answer_id"
	);

    private $question_id;
    private $response_type_id;
    private $response_type;
    private $responses;
    private $grid_column_index;
    private $grid_column_name;

    /* non-persisted value passed to all questions that are part of an Protocol instance*/
    private $protocol_id;

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

    public function getQuestion_id(){return $this->question_id;}
	public function setQuestion_id($question_id){$this->question_id = $question_id;}

	public function getResponse_type_id(){return $this->response_type_id;}
	public function setResponse_type_id($response_type_id){$this->response_type_id = $response_type_id;}

	public function getResponse_type(){return $this->response_type;}
	public function setResponse_type($response_type){$this->response_type = $response_type;}

	public function getResponses(){
        if($this->responses === NULL && $this->protocol_id != null && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO(new IBCResponse());
            $grp = new WhereClauseGroup(
                new WhereClause("protocol_id","=",$this->protocol_id),
                new WhereClause("answer_id","=",$this->key_id)
            );
			$this->responses = $thisDAO->getAllWhere($grp);
		}
		return $this->responses;
	}
	public function setResponses($responses){$this->responses = $responses;}

	public function getGrid_column_index(){return (integer) $this->grid_column_index;}
	public function setGrid_column_index($grid_column_index){$this->grid_column_index = $grid_column_index;}

	public function getGrid_column_name(){return $this->grid_column_name;}
	public function setGrid_column_name($grid_column_name){$this->grid_column_name = $grid_column_name;}

    public function getProtocol_id(){return (integer) $this->protocol_id;}
    public function setProtocol_id($protocol_id){$this->protocol_id = $protocol_id;}

}
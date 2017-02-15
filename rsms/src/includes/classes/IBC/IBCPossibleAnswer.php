<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class IBCPossibleAnswer extends GenericCrud
{



	/** Name of the DB Table */
	protected static $TABLE_NAME = "ibc_possible_answer";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"question_id"			=> "integer",
        "grid_column_index"		=> "integer",
		"answer_text"			=> "text",

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
    private $IBCResponses;
    private $grid_column_index;
    private $answer_text;

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

	public function getIBCResponses(){
        if($this->IBCResponses === NULL && $this->protocol_id != null && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO(new IBCResponse());
            $grp = new WhereClauseGroup(
                new WhereClause("protocol_id","=",$this->protocol_id),
                new WhereClause("answer_id","=",$this->key_id)
            );
			$this->IBCResponses = $thisDAO->getAllWhere($grp);
		}
		return $this->IBCResponses;
	}
	public function setIBCResponses($responses){$this->IBCResponses = $responses;}

	public function getGrid_column_index(){return (integer) $this->grid_column_index;}
	public function setGrid_column_index($grid_column_index){$this->grid_column_index = $grid_column_index;}

	public function getAnswer_text(){return $this->answer_text;}
	public function setAnswer_text($text){$this->answer_text = $text;}

    public function getProtocol_id(){return (integer) $this->protocol_id;}
    public function setProtocol_id($protocol_id){$this->protocol_id = $protocol_id;}

}
<?php

/**
 * IBCPreliminaryComment short summary.
 *
 * Comment added in the preliminary stage of protocol revision review, before revision has gone to full committee for review
 *
 * @version 1.0
 * @author Matt Breeden
 */
class IBCPreliminaryComment
{

	/** Name of the DB Table */
	protected static $TABLE_NAME = "ibc_preliminary_comment";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"ibc_question_id"	=> "integer",
        "ibc_revision_id"	=> "integer",
		"text"			=> "text",

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

    private $ibc_question_id;
    private $ibc_revision_id;
    private $text;

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

    public function getIbc_question_id(){
		return $this->ibc_question_id;
	}
	public function setIbc_question_id($ibc_question_id){
		$this->ibc_question_id = $ibc_question_id;
	}

	public function getIbc_revision_id(){
		return $this->ibc_revision_id;
	}
	public function setIbc_revision_id($ibc_revision_id){
		$this->ibc_revision_id = $ibc_revision_id;
	}

	public function getText(){
		return $this->text;
	}
    public function setText($text){
		$this->text = $text;
	}
}
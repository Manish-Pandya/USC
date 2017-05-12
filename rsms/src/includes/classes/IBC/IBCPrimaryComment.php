<?php

/**
 * IBCPrimaryComment short summary.
 *
 * Comment added in the primary stage of protocol revision review, before revision has gone to full committee for review
 *
 * @version 1.0
 * @author Matt Breeden
 */
class IBCPrimaryComment extends GenericCrud
{

	/** Name of the DB Table */
	protected static $TABLE_NAME = "ibc_primary_comment";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"question_id"	=> "integer",
        "revision_id"	=> "integer",
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

    private $question_id;
    private $revision_id;
	private $section_id;
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

    public function getQuestion_id(){
		return $this->question_id;
	}
	public function setQuestion_id($question_id){
		$this->question_id = $question_id;
	}

	public function getRevision_id(){
		return $this->revision_id;
	}
	public function setRevision_id($revision_id){
		$this->revision_id = $revision_id;
	}

	public function getSection_id(){
		$question = new Question();
		$thisDAO = new GenericDAO($question);
		//$question = $thisDAO->getById($this->question_id);
		if ($question != NULL) {
			$this->section_id = $question->getSection_id();
		}

		return $this->section_id;
	}

	public function getText(){
		return $this->text;
	}
    public function setText($text){
		$this->text = $text;
	}
}
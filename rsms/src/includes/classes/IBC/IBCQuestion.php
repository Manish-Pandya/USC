<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class IBCQuestion extends GenericCrud
{

	/** Name of the DB Table */
	protected static $TABLE_NAME = "ibc_question";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"section_id"			=> "integer",
		"text"					=> "text",

		//GenericCrud
		"key_id"				=> "integer",
		"date_created"			=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"				=> "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"		=> "integer",
	);

    /** Relationships */
	public static $ANSWERS_RELATIONSHIP = array(
		"className"	=>	"IBCAnswer",
		"tableName"	=>	"ibc_question",
		"keyName"	=>	"key_id",
		"foreignKeyName"	=>	"question_id"
	);


	private $section_id;
	private $text;
    private $answers;


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

    public function getSection_id(){return $this->section_id;}
	public function setSection_id($section_id){$this->section_id = $section_id;}

	public function getText(){return $this->text;}
	public function setText($text){$this->text = $text;}

    public function getAnswers(){
        if($this->answers === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->answers = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$ANSWERS_RELATIONSHIP));
		}
		return $this->answers;
	}
	public function setAnswers($answers){$this->answers = $answers;}
}
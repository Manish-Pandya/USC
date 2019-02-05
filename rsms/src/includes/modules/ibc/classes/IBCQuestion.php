<?php


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
        "answer_type"			=> "text",
        "weight"			    => "integer",


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
		"className"	=>	"IBCPossibleAnswer",
		"tableName"	=>	"ibc_question",
		"keyName"	=>	"key_id",
		"foreignKeyName"	=>	"question_id"
	);


	private $section_id;
	private $text;
    private $IBCPossibleAnswers;
    private $answer_type;
    private $weight;

	public function __construct(){

		
    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		return $entityMaps;
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

    public function getIBCPossibleAnswers(){
        if($this->IBCPossibleAnswers === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->IBCPossibleAnswers = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$ANSWERS_RELATIONSHIP));
		}
		return $this->IBCPossibleAnswers;
	}
	public function setIBCPossibleAnswers($answers){$this->IBCPossibleAnswers = $answers;}

    /*Enumeration of available types for this question */
    static $ANSWER_TYPES = array(
            "MULTIPLE_CHOICE" => "MULTIPLE_CHOICE",
            "TABLE" => "TABLE",
            "FREE_TEXT" => "FREE_TEXT",
            "MULTI_SELECT" => "MULTI_SELECT"
        );
	public function getAnswer_type(){return $this->answer_type;}
	public function setAnswer_type($type){$this->answer_type = $type;}

	public function getWeight(){return $this->weight;}
	public function setWeight($weight){$this->weight = $weight;}
}
<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class IBCSection extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "ibc_section";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"hazard_id"						=> "integer",
		"answer_id"					    => "integer",
        "weight"					    => "integer",

		//GenericCrud
		"key_id"				=> "integer",
		"date_created"			=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"				=> "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"		=> "integer",
	);

    /** Relationships */
	public static $QUESTIONS_RELATIONSHIP = array(
		"className"	=>	"IBCQuestion",
		"tableName"	=>	"ibc_question",
		"keyName"	=>	"key_id",
		"foreignKeyName"	=>	"section_id"
	);


	/**
	 * If this Section is the parent section of a protocol, it will have a relationship with that Protocol's hazard
	 * @var integer
	 */
	private $hazard_id;

	/**
     * If this Section is NOT the parent section of a protocol, it will have a relationship with an answer.  When that answer is selected in the UI, this Section should appear in the protocol
     * @var integer
	 */
	private $answer_id;

    private $questions;

    private $weight;


	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getQuestions");
		$this->setEntityMaps($entityMaps);
	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	public function getHazard_id(){return $this->hazard_id;}
	public function setHazard_id($id){$this->hazard_id = $id;}

	public function getAnswer_id(){return $this->answer_id;}
	public function setAnswer_id($answer_id){$this->answer_id = $answer_id;}

    public function getQuestions(){
        if($this->questions === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->questions = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$QUESTIONS_RELATIONSHIP));
		}
		return $this->questions;
    }
    public function setQuestions($questions){$this->questions = $questions;}


	public function getWeight(){return $this->weight;}
	public function setWeight($weight){$this->weight = $weight;}
}
?>
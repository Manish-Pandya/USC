<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Deficiency extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "deficiency";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//question is a relationship
		"text"		=> "text",
		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer"
	);
	
	/** Reference to the Question entity to which this Deficiency applies */
	private $question;
	private $question_id;
	
	/** String containing the text describing this Deficiency */
	private $text;
	
	public function __construct(){
	
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getQuestion(){ 
		if($this->question == null) {
			$questionDAO = new GenericDAO("Question");
			$this->question = $questionDAO->getById($this->question_id);
		}
		return $this->question; 
	}
	public function setQuestion($question){
		$this->question = $question; 
		$this->question_id = $question->getKey_id();
	}
	
	public function getQuestion_id(){ return $this->question_id; }
	public function setQuestion_id($question_id){ $this->question_id = $question_id; }
	
	public function getText(){ return $this->text; }
	public function setText($text){ $this->text = $text; }
}
?>
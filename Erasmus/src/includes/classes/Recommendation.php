<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Recommendation extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "recommendation";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"text"		=> "text",
		"question_id"	=>	"integer",

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer"
	);
	
	/** Reference to the Question entity to which this Recommendation applies */
	private $question;
	private $question_id;
	
	/** String containing the text describing this Recommendation */
	private $text;
	
	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getQuestion");
		$this->setEntityMaps($entityMaps);
				
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
			$questionDAO = new GenericDAO(new Question());
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
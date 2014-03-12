<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Response extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "response";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"question_text"	=> "text",
		"answer"	=> "text",
		"inspection_id"	=> "integer",
		"question_id"	=> "question",
		//inspection is a relationship
		//question is a relationship
		//deficiencySelections are a relationship
		//recommendations are a relationship
				
		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer"
	);
	
	/** Relationships */
	protected static $DEFICIENCIES_RELATIONSHIP = array(
			"className"	=>	"DeficiencySelection",
			"tableName"	=>	"deficiency_selection",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"response_id"
	);
	
	protected static $RECOMMENDATIONS_RELATIONSHIP = array(
			"className"	=>	"Recommendation",
			"tableName"	=>	"response_recommendation",
			"keyName"	=>	"room_id",
			"foreignKeyName"	=>	"response_id"
	);
	
	protected static $OBSERVATIONS_RELATIONSHIP = array(
			"className"	=>	"Observation",
			"tableName"	=>	"response_observation",
			"keyName"	=>	"observation_id",
			"foreignKeyName"	=>	"response_id"
	);
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function __construct(){}
	
	/** Reference to Question entity to which this Response applies */
	private $question;
	private $question_id;
	
	/** Reference to the Inspection entity's KeyId to which this Response belongs */
	private $inspection;
	private $inspection_id;
	
	/** Answer selected by inspector for the associated Question */
	private $answer;
	
	/** Text of the question asked */
	private $question_text;
	
	/** Array of DeficiencySelection entities describing this Question's deficiencies */
	private $deficiencySelections;
	
	/** Array of Recommendation entities selected as part of the associated Question */
	private $recommendations;
	

	
	
	
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
	
	public function getInspection(){
		if($this->inspection == null) {
			$inspectionDAO = new GenericDAO("Inspection");
			$this->inspection = $inspectionDAO->getById($this->inspection_id);
		}
		return $this->inspection; 
	}
	public function setInspection($inspection){
		$this->inspection = $inspection;
		$this->inspection_id = $inspection->getKey_id();
	}
	
	public function getInspection_id(){ return $this->inspection_id; }
	public function setInspection_id($inspection_id){ $this->inspection_id = $inspection_id; }
	
	public function getAnswer(){ return $this->answer; }
	public function setAnswer($answer){ $this->answer = $answer; }
	
	public function getQuestion_text(){ return $this->question_text; }
	public function setQuestion($question_text){ $this->question_text = $question_text; }
		
	public function getDeficiencySelections(){ 
		if($this->deficiencySelections === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->deficiencySelections = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$DEFICIENCIES_RELATIONSHIP));
		}
		return $this->deficiencySelections;
	}
	public function setDeficiencySelections($deficiencySelections){ $this->deficiencySelections = $deficiencySelections; }
	
	public function getRecommendations(){ 
		if($this->recommendations === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->recommendations = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$RECOMMENDATIONS_RELATIONSHIP));
		}
		return $this->recommendations;
	}
	public function setRecommendations($recommendations){ $this->recommendations = $recommendations; }
	
	public function getObservations(){ 
		if($this->observations === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->observations = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$OBSERVATIONS_RELATIONSHIP));
		}
		return $this->observations;
	}
	public function setObservations($observations){ $this->observations = $observations; }
}
?>
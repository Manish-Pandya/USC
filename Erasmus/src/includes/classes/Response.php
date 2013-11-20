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
		"question"	=> "text",
		"answer"	=> "text",
		//deficiencySelections are a relationship
		//recommendations are a relationship
	);
	
	public static $POSSIBLE_ANSWERS = array('Yes', 'No', 'NotApplicable', 'NoResponse' );
	
	/** Reference to Question entity to which this Response applies */
	private $question;
	
	/** Reference to the Inspection entity's KeyId to which this Response belongs */
	private $inspectionId;
	
	//TODO: Enum-type object for answer? simple string?
	/** Answer selected by inspector for the associated Question */
	private $answer;
	
	/** Array of DeficiencySelection entities describing this Question's deficiencies */
	private $deficiencySelections;
	
	/** Array of Recommendation entities selected as part of the associated Question */
	private $recommendations;
	
	public function __construct(){
	
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getQuestion(){ return $this->question; }
	public function setQuestion($question){ $this->question = $question; }
	
	public function getInspectionId(){ return $this->inspectionId; }
	public function setInspectionId($inspectionId){ $this->inspectionId = $inspectionId; }
	
	public function getAnswer(){ return $this->answer; }
	public function setAnswer($answer){ $this->answer = $answer; }
	
	public function getDeficiencySelections(){ return $this->deficiencySelections; }
	public function setDeficiencySelections($deficiencySelections){ $this->deficiencySelections = $deficiencySelections; }
	
	public function getRecommendations(){ return $this->recommendations; }
	public function setRecommendations($recommendations){ $this->recommendations = $recommendations; }
}
?>
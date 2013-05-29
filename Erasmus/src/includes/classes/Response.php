<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Response {
	
	/** Reference to Question entity to which this Response applies */
	private $question;
	
	//TODO: Enum-type object for answer? simple string?
	/** Answer selected by inspector for the associated Question */
	private $answer;
	
	/** Array of DeficiencySelection entities describing this Question's deficiencies */
	private $deficiencySelections;
	
	/** Array of Recommendation entities selected as part of the associated Question */
	private $recommendations;
	
	public function __construct(){
	
	}
	
	public function getquestion(){ return $this->question; }
	public function setquestion($question){ $this->question = $question; }
	
	public function getanswer(){ return $this->answer; }
	public function setanswer($answer){ $this->answer = $answer; }
	
	public function getdeficiencySelections(){ return $this->deficiencySelections; }
	public function setdeficiencySelections($deficiencySelections){ $this->deficiencySelections = $deficiencySelections; }
	
	public function getrecommendations(){ return $this->recommendations; }
	public function setrecommendations($recommendations){ $this->recommendations = $recommendations; }
}
?>
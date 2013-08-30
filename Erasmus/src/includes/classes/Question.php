<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Question {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "erasmus_question";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"text"						=> "text",
		"order_index"				=> "integer",
		"standards_and_guidelines"	=> "text",
		"is_mandatory"				=> "boolean",
		//deficiencies is a relationship
		//deficiency_root_causes is a relationship
		//recommendations is a relationship
	);
	
	/** Question text */
	private $text;
	
	/** Checklist to which this Question belongs */
	private $checklist;
	
	/** Question ordering descriptor; index of this question */
	private $orderIndex;
	
	/** String that describes (or excerpts) the Standards and Guidelines to which this Question pertains */
	private $standardsAndGuidelines;
	
	/** Boolean that determines whether this Question may be skipped (FALSE) or must be answered (TRUE) */
	private $isMandatory;
	
	/** Array of pre-defined Deficiency entities that may be selected for this Question */
	private $deficiencies;
	
	/** Array of pre-defined DeficiencyRootCause entities that may be selected
	 * 	as a cause for this Question being answered Deficiently */
	private $deficiencyRootCauses;
	
	/** Array of Recommendation entities that may be selected for this Question */
	private $recommendations;
	
	/** Array of Observation entities that may be selected for this Question */
	private $observations;
	
	public function __construct(){
	
	}
	
	public function getText(){ return $this->text; }
	public function setText($text){ $this->text = $text; }
	
	public function getChecklist(){ return $this->checklist; }
	public function setChecklist($checklist){ $this->checklist = $checklist; }
	
	public function getOrderIndex(){ return $this->orderIndex; }
	public function setOrderIndex($orderIndex){ $this->orderIndex = $orderIndex; }
	
	public function getStandardsAndGuidelines(){ return $this->standardsAndGuidelines; }
	public function setStandardsAndGuidelines($standardsAndGuidelines){ $this->standardsAndGuidelines = $standardsAndGuidelines; }
	
	public function getIsMandatory(){ return $this->isMandatory; }
	public function setIsMandatory($isMandatory){ $this->isMandatory = $isMandatory; }
	
	public function getDeficiencies(){ return $this->deficiencies; }
	public function setDeficiencies($deficiencies){ $this->deficiencies = $deficiencies; }
	
	public function getDeficiencyRootCauses(){ return $this->deficiencyRootCauses; }
	public function setDeficiencyRootCauses($deficiencyRootCauses){ $this->deficiencyRootCauses = $deficiencyRootCauses; }
	
	public function getRecommendations(){ return $this->recommendations; }
	public function setRecommendations($recommendations){ $this->recommendations = $recommendations; }
	
	public function getObservations(){ return $this->observations; }
	public function setObservations($observations){ $this->observations = $observations; }
}
?>
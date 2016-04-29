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
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer"
	);

	/** Relationships */
	public static $DEFICIENCIES_RELATIONSHIP = array(
			"className"	=>	"DeficiencySelection",
			"tableName"	=>	"deficiency_selection",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"response_id"
	);

	public static $RECOMMENDATIONS_RELATIONSHIP = array(
			"className"	=>	"Recommendation",
			"tableName"	=>	"response_recommendation",
			"keyName"	=>	"recommendation_id",
			"foreignKeyName"	=>	"response_id"
	);

	public static $OBSERVATIONS_RELATIONSHIP = array(
			"className"	=>	"Observation",
			"tableName"	=>	"response_observation",
			"keyName"	=>	"observation_id",
			"foreignKeyName"	=>	"response_id"
	);

	public static $SUPPLEMENTAL_RECOMMENDATIONS_RELATIONSHIP = array(
			"className"	=>	"SupplementalRecommendation",
			"tableName"	=>	"supplemental_recommendation",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"response_id"
	);

	public static $SUPPLEMENTAL_OBSERVATIONS_RELATIONSHIP = array(
			"className"	=>	"SupplementalObservation",
			"tableName"	=>	"supplemental_observation",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"response_id"
	);

    public static $SUPPLEMENTAL_DEFICIENCIES_RELATIONSHIP = array(
			"className"	=>	"SupplementalDeficiency",
			"tableName"	=>	"supplemental_deficiency",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"response_id"
	);

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getQuestion");
		$entityMaps[] = new EntityMap("lazy","getInspection");
		$entityMaps[] = new EntityMap("eager","getDeficiencySelections");
		$entityMaps[] = new EntityMap("eager","getRecommendations");
		$entityMaps[] = new EntityMap("eager","getObservations");
		$entityMaps[] = new EntityMap("eager","getSupplementalRecommendations");
		$entityMaps[] = new EntityMap("eager","getSupplementalObservations");
        $entityMaps[] = new EntityMap("eager","getSupplementalDeficiencies");

		$this->setEntityMaps($entityMaps);

	}

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

	/** Array of Recommendation entities selected as part of the associated Question */
	private $observations;

	private $supplementalObservations;
	private $supplementalRecommendations;
    private $supplementalDeficiencies;



	public function getQuestion(){
		if($this->question == null) {
			$questionDAO = new GenericDAO(new Question());
			$this->question = $questionDAO->getById($this->question_id);
		}
		return $this->question;
	}
	public function setQuestion($question){
		$this->question = $question;
	}

	public function getQuestion_id(){ return $this->question_id; }
	public function setQuestion_id($question_id){ $this->question_id = $question_id; }

	public function getInspection(){
		if($this->inspection == null) {
			$inspectionDAO = new GenericDAO(new Inspection());
			$this->inspection = $inspectionDAO->getById($this->inspection_id);
		}
		return $this->inspection;
	}
	public function setInspection($inspection){
		$this->inspection = $inspection;
	}

	public function getInspection_id(){ return $this->inspection_id; }
	public function setInspection_id($inspection_id){ $this->inspection_id = $inspection_id; }

	public function getAnswer(){ return $this->answer; }
	public function setAnswer($answer){ $this->answer = $answer; }

	public function getQuestion_text(){ return $this->question_text; }
	public function setQuestion_text($question_text){ $this->question_text = $question_text; }

	public function getDeficiencySelections(){
		if($this->deficiencySelections === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->deficiencySelections = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$DEFICIENCIES_RELATIONSHIP));
		}
		return $this->deficiencySelections;
	}
	public function setDeficiencySelections($deficiencySelections){ $this->deficiencySelections = $deficiencySelections; }

	public function getRecommendations(){
		if($this->recommendations === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->recommendations = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$RECOMMENDATIONS_RELATIONSHIP));
		}
		return $this->recommendations;
	}
	public function setRecommendations($recommendations){ $this->recommendations = $recommendations; }

	public function getObservations(){
		if($this->observations === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->observations = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$OBSERVATIONS_RELATIONSHIP));
		}
		return $this->observations;
	}

	public function setObservations($observations){ $this->observations = $observations; }

	public function getSupplementalRecommendations(){
			$thisDAO = new GenericDAO($this);
			$this->supplementalRecommendations = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$SUPPLEMENTAL_RECOMMENDATIONS_RELATIONSHIP));
			return $this->supplementalRecommendations;
	}
	public function setSupplementalRecommendations($supplementalRecommendations){ $this->supplementalRecommendations = $supplementalRecommendations; }

	public function getSupplementalObservations(){
		$thisDAO = new GenericDAO($this);
		$this->supplementalObservations = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$SUPPLEMENTAL_OBSERVATIONS_RELATIONSHIP));
		return $this->supplementalObservations;
	}
	public function setSupplementalObservations($supplementalObservations){ $this->supplementalObservations = $supplementalObservations; }

    public function getSupplementalDeficiencies(){
		$thisDAO = new GenericDAO($this);
		$this->supplementalDeficiencies = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$SUPPLEMENTAL_DEFICIENCIES_RELATIONSHIP));
		return $this->supplementalDeficiencies;
	}
	public function setSupplementalDeficiencies($supplementalDeficiencies){ $this->supplementalDeficiencies = $supplementalDeficiencies; }
}
?>
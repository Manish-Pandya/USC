<?php
/**
 *
 *
 *
 * @author Hoke Currie, GraySail LLC
 */
class Question extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "question";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"text"						=> "text",
		"order_index"				=> "integer",
		"standards_and_guidelines"	=> "text",
		"is_mandatory"				=> "boolean",
		"description" => "text",
		"reference" => "text",
		// checklist is a relationship
		"checklist_id"				=>	"integer",
		"root_cause"				=>	"text",
		//deficiencies is a relationship
		//recommendations is a relationship

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer"
	);

	/** Relationships */
	protected static $DEFICIENCIES_RELATIONSHIP = array(
			"className"	=>	"Deficiency",
			"tableName"	=>	"deficiency",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"question_id"
	);

	protected static $RECOMMENDATIONS_RELATIONSHIP = array(
			"className"	=>	"Recommendation",
			"tableName"	=>	"recommendation",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"question_id"
	);

	protected static $OBSERVATIONS_RELATIONSHIP = array(
			"className"	=>	"Observation",
			"tableName"	=>	"observation",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"question_id"
	);

	private static $RESPONSES_RELATIONSHIP = array(
			"className"	=>	"Response",
			"tableName"	=>	"response",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"question_id"
	);

	/** Question text */
	private $text;

	/** Checklist to which this Question belongs */
	private $checklist;
	private $checklist_id;

	/** Question ordering descriptor; index of this question */
	private $order_index;

	/** String that describes (or excerpts) the Standards and Guidelines to which this Question pertains */
	private $standards_and_guidelines;

	/** String that describes the root cause of the deficiency */
	private $root_cause;

	/** Boolean that determines whether this Question may be skipped (FALSE) or must be answered (TRUE) */
	private $is_mandatory;

	/** Array of pre-defined Deficiency entities that may be selected for this Question */
	private $deficiencies;

	/** Array of Recommendation entities that may be selected for this Question */
	private $recommendations;

	/** Array of Observation entities that may be selected for this Question */
	private $observations;

	/** Array of Response entities encompassing answers made to this Question during Inspections */
	private $responses;

	/** Non-persisted value used to filter Responses for a particular inspection */
	private $inspectionId;
	
	private $checklistName;

	public function __construct(){

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getChecklist");
		$entityMaps[] = EntityMap::eager("getDeficiencies");
		$entityMaps[] = EntityMap::eager("getRecommendations");
		$entityMaps[] = EntityMap::eager("getObservations");
		$entityMaps[] = EntityMap::lazy("getResponses");
		return $entityMaps;
	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	public function getText(){ return $this->text; }
	public function setText($text){ $this->text = $text; }

	public function getChecklist(){
		if($this->getChecklist_id() != null && $this->checklist == null) {
			$checklistDAO = new GenericDAO(new Checklist());
			$this->checklist = $checklistDAO->getById($this->checklist_id);
		}
		return $this->checklist;
	}
	public function setChecklist($checklist){
		$this->checklist = $checklist;
	}

	public function getChecklist_id() { return $this->checklist_id; }
	public function setChecklist_id($checklist_id) { $this->checklist_id = $checklist_id; }

	public function getOrder_index(){ return $this->order_index; }
	public function setOrder_index($order_index){ $this->order_index = $order_index; }

	public function getStandards_and_guidelines(){ return $this->standards_and_guidelines; }
	public function setStandardsA_and_guidelines($standards_and_guidelines){ $this->standards_and_guidelines = $standards_and_guidelines; }

	public function getRoot_cause(){ return $this->root_cause; }
	public function setRoot_cause($root_cause){ $this->root_cause = $root_cause; }

	public function getIs_mandatory(){ return $this->is_mandatory; }
	public function setIs_mandatory($is_mandatory){ $this->is_mandatory = $is_mandatory; }

	public function getDescription(){ return $this->description; }
	public function setDescription($description){ $this->description = $description; }

	public function getReference(){ return $this->reference; }
	public function setReference($reference){ $this->reference = $reference; }

	public function getDeficiencies(){
		if($this->deficiencies === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->deficiencies = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$DEFICIENCIES_RELATIONSHIP));
			
			//also get the Other Defiency, for all questions
            //DEPRECATED:  Other Deficiencies are now contructed on the client to allow selection of multiple Other Deficiencies per question and on-demand creation of new deficiencies
			/*
            $defDao = new GenericDAO(new Deficiency());
			$whereClauseGroup = new WhereClauseGroup(array(new WhereClause("text","=","Other")));
			$otherDeficiency = $defDao->getAllWhere($whereClauseGroup);
			$this->deficiencies = array_merge($this->deficiencies, $otherDeficiency);
            */
		}
		return $this->deficiencies;
	}
	public function setDeficiencies($deficiencies){ $this->deficiencies = $deficiencies; }

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

	public function getResponses(){
		$LOG = Logger::getLogger(__Class__);
		if(!empty($this->inspectionId)) {		
			$whereClauseGroup = new WhereClauseGroup(array(new WhereClause("inspection_id","=",$this->inspectionId), new WhereClause("question_id", "=", $this->key_id)));
			$responsesDao = new GenericDAO(new Response);
			$this->responses = $this->filterResponsesByInspection($responsesDao->getAllWhere($whereClauseGroup));

		}else{
			$thisDAO = new GenericDAO($this);
			$this->responses = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$RESPONSES_RELATIONSHIP));
		}
		return $this->responses;
	}
	public function setResponses($responses){ $this->responses = $responses; }

	public function getInspectionId(){ return $this->inspectionId; }
	public function setInspectionId($inspectionId){ $this->inspectionId = $inspectionId; }

	public function getChecklistName(){
		$LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
	
		if($this->checklistName == null && $this->getChecklist_id() != null){
			$dao = new GenericDAO(new Checklist());
			$this->checklistName = $dao->getById($this->getChecklist_id())->getName();
		}
		return $this->checklistName;
	}
	
	private function filterResponsesByInspection($responses){
		//$LOG->fatal("about to filter ".  count($responses) . " responses");
		if(!empty($this->inspectionId)) {

			//$LOG->fatal("Inspection Id " . $this->inspectionId . " found.");		
			if (!empty($responses)){
                $insp = new Inspection();
                $dao = new GenericDAO($insp);
                $inspDate = $dao->getById($this->inspectionId)->getDate_started();

                $validResponses = array();
                foreach($responses as $response){
                    if( strtotime($response->getDate_created()) > strtotime($inspDate) ){
                        $validResponses[] = $response;
                    }
                }
				return array_shift($validResponses);
			} 
			return null;			
		}
        return null;
       

	}

}
?>
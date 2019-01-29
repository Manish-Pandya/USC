<?php
/**
 *
 *
 *
 * @author Hoke Currie, GraySail LLC
 */
class Checklist extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "checklist";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"	=>	"text",
		//hazards is a relationship
		"hazard_id"	=>	"integer",
		//questions is a relationship
		"master_hazard" => "text",
		"master_id" 	=> "integer",
				

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer"
	);

	/** Relationships */
	protected static $QUESTIONS_RELATIONSHIP = array(
			"className"	=>	"Question",
			"tableName"	=>	"question",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"checklist_id"
	);

	protected static $ROOMS_RELATIONSHIP = array(
			"className"	=>	"Room",
			"tableName"	=>	"hazard_room",
			"keyName"	=>	"room_id",
			"foreignKeyName"	=>	"hazard_id"
	);

	/** Name of this Checklist */
	private $name;

	/** The Hazard entity to which this Checklist applies */
	private $hazard;
	private $hazard_id;
	private $parent_hazard_id;
    private $orderIndex;

	/** The name of the master hazard category assigned to this checklist's hazard, e.g. Chemical, Biological Radiological */
	private $master_hazard;
	private $master_id;


	/** Array of Question entities that comprise this Checklist */
	private $questions;

	/** Non-persisted value used to filter Responses for a particular inspection */
	private $inspectionId;

	/** Array of Room entities in which this Checklist's Hazard is contained */
	private $rooms;

	/** Array of Room entities relevant to a particular inspection */
	private $inspectionRooms;

	private $isOrdered;
	private $isPresent;

	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getHazard");
		$entityMaps[] = new EntityMap("lazy","getRooms");
		$entityMaps[] = new EntityMap("lazy","getInspectionRooms");
		$entityMaps[] = new EntityMap("eager","getQuestions");
		$this->setEntityMaps($entityMaps);

	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	public function getHazard(){
		if($this->hazard == null) {
			$hazardDAO = new GenericDAO(new Hazard());
			$this->hazard = $hazardDAO->getById($this->hazard_id);
		}
		return $this->hazard;
	}
	public function setHazard($hazard){
		$this->hazard = $hazard;
	}

	public function getHazard_id(){ return $this->hazard_id; }
	public function setHazard_id($hazard_id){ $this->hazard_id = $hazard_id; }	
	
	public function getParent_hazard_id(){ return $this->parent_hazard_id; }
	public function setParent_hazard_id($hazard_id){ $this->parent_hazard_id = $hazard_id; }

	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }

	public function getMaster_hazard(){ return $this->master_hazard; }
	public function setMaster_hazard($master_hazard){ $this->master_hazard = $master_hazard; }	
	
	public function getMaster_id(){ return $this->master_id; }
	public function setMaster_id($master_id){ $this->master_id = $master_id; }

	public function getQuestions(){
		$thisDAO = new GenericDAO($this);
		$this->questions = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$QUESTIONS_RELATIONSHIP), array('order_index'));
		return $this->filterQuestionsForInspection($this->questions);
	}
	public function setQuestions($questions){ $this->questions = $questions; }

	public function getInspectionId(){ return $this->inspectionId; }
	public function setInspectionId($inspectionId){ $this->inspectionId = $inspectionId; }

	private function filterQuestionsForInspection($questions){

		$LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
		$LOG->debug("about to init ".  count($questions) . " Question objects with inspection filter info.");

		if(!empty($this->inspectionId)) {
			$LOG->debug("Inspection Id " . $this->inspectionId . " found.");
			// Define which subentities to load
			$entityMaps = array();
			$entityMaps[] = new EntityMap("lazy","getChecklist");
			$entityMaps[] = new EntityMap("eager","getDeficiencies");
			$entityMaps[] = new EntityMap("eager","getRecommendations");
			$entityMaps[] = new EntityMap("eager","getObservations");
			$entityMaps[] = new EntityMap("eager","getResponses");

			foreach ($questions as $question){
				$question->setInspectionId($this->inspectionId);
				$question->setEntityMaps($entityMaps);
			}
		}
		return $questions;
	}

	public function getRooms(){
		if($this->rooms === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->rooms = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$ROOMS_RELATIONSHIP));
		}
		return $this->rooms;
	}
	public function setRooms($rooms){ $this->rooms = $rooms; }
   

	public function getInspectionRooms() { return $this->inspectionRooms; }
	public function setInspectionRooms($inspectionRooms){
		$LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

		$this->inspectionRooms = array();
		$roomDao = new GenericDAO(new Room());
		//$LOG->debug($roomDao);

		if( isset($inspectionRooms) ){
			foreach ($inspectionRooms as $rm){
				//if the hazard has been received from an API call, each of its inspection rooms will be an array instead of an object, because PHP\
				//If so, we set the key id by index instead of calling the getter
				if(!is_object($rm)){
					$key_id = $rm['Key_id'];
				}else{
					$key_id = $rm->getKey_id();
				}
				$this->inspectionRooms[] = $roomDao->getById($key_id);
			}
		}
	}
    public function pushInspectionRoom($room){
        if($this->inspectionRooms == null){
            $this->inspectionRooms = array();
        }
        array_push($this->inspectionRooms, $room);
    }

	public function filterRooms($piId = null){
        if($piId == null)return null;
		$LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
		$LOG->debug("Filtering rooms for checklist: " . $this->getName() . ", key_id " . $this->getKey_id());

		// Get the db connection
		$db = DBConnection::get();

		foreach($this->getRooms() as $room){
			$rooms[] = $room->getKey_id();
		}
		
		$roomIds = implode (',',$rooms);

		$queryString = "SELECT room_id FROM principal_investigator_hazard_room WHERE principal_investigator_id = $piId AND hazard_id = $this->hazard_id AND room_id IN ( $roomIds )";
		$LOG->debug("query: " . $queryString);
		$stmt = DBConnection::prepareStatement($queryString);
		$stmt->execute();
		$roomIdsToEval = array();
		while($roomId = $stmt->fetchColumn()){
			$this->isPresent = true;
			array_push($roomIdsToEval,$roomId);
		}

		$inspectionRooms = array();

		foreach ($this->rooms as $room){
		    if( in_array ( $room->getKey_id() , $roomIdsToEval ) )array_push($inspectionRooms, $room);
		}

		$this->inspectionRooms = $inspectionRooms;
	}
    
    public function getOrderIndex(){
        return $this->orderIndex;
    }
    
    public function setOrderIndex($idx){
        $this->orderIndex = $idx;
    }

    public function getIsOrdered(){return $this->isOrdered;}
    public function setIsOrdered($is){$this->isOrdered = $is;}

    public function getIsPresent(){return $this->isPresent;}
}
?>
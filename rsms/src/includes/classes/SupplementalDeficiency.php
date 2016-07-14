<?php

/**
 *
 * @author Matt Breeden
 */
class SupplementalDeficiency extends GenericCrud
{

	/** Name of the DB Table */
	protected static $TABLE_NAME = "supplemental_deficiency";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"text"		=> "text",
		"response_id"	=>	"integer",

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
        "show_rooms"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer"
	);

    /** Relationships */
	public static $ROOMS_RELATIONSHIP = array(
			"className"	=>	"Room",
			"tableName"	=>	"supplemental_deficiency_room",
			"keyName"	=>	"room_id",
			"foreignKeyName"	=>	"supplemental_deficiency_id"
	);

	public static $CORRECTIVE_ACTIONS_RELATIONSHIP = array(
			"className"	=>	"CorrectiveAction",
			"tableName"	=>	"corrective_action",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"supplemental_deficiency_id"
	);

	/** Reference to the Response entity to which this SupplementalDeficiency applies */
	private $response;
	private $response_id;

	/** String containing the text describing this SupplementalDeficiency */
	private $text;

    private $inspectionRooms;
    private $rooms;
    private $roomIds;
    private $correctiveActions;
    private $showRooms;

	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getResponse");
		$this->setEntityMaps($entityMaps);
        //$LOG = Logger::getLogger("sup");
	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	public function getResponse(){
		if($this->response == null) {
			$responseDAO = new GenericDAO(new Response());
			$this->response = $responseDAO->getById($this->response_id);
		}
		return $this->response;
	}
	public function setResponse($response){
		$this->response = $response;
	}

	public function getResponse_id(){ return $this->response_id; }
	public function setResponse_id($response_id){ $this->response_id = $response_id; }


	public function getText(){ return $this->text; }
	public function setText($text){ $this->text = $text; }

    public function getCorrectiveActions(){
		if($this->correctiveActions === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->correctiveActions = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$CORRECTIVE_ACTIONS_RELATIONSHIP));
		}
		return $this->correctiveActions;
	}
	public function setCorrectiveActions($correctiveActions){ $this->correctiveActions = $correctiveActions; }

	public function getRoomIds() {return $this->roomIds;}
	public function setRoomIds($roomIds){ $this->roomIds = $roomIds;}

    public function getInspectionRooms(){return $this->inspectionRooms;	}
	public function setInspectionRooms($rooms){ $this->inspectionRooms = $rooms; }

    public function getRooms(){
		if($this->rooms === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->rooms = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$ROOMS_RELATIONSHIP));
		}
        $LOG=Logger::getLogger('adf');
        $LOG->fatal($this);
		return $this->rooms;
	}
	public function setRooms($rooms){ $this->rooms = $rooms; }

	public function getCorrected_in_inspection() {return $this->corrected_in_inspection;}
	public function setCorrected_in_inspection($corrected) { $this->corrected_in_inspection = $corrected; }

	public function getShow_rooms(){return $this->show_rooms;}
	public function setShow_rooms($show_rooms){$this->show_rooms = $show_rooms;}
}
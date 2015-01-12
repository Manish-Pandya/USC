<?php
/**
 * TODO: DOC
 *
 * @author Mitch Martin, GraySail LLC
 */
class PrincipalInvestigator extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "principal_investigator";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//TODO: IS user a relationship?
		"user_id" => "integer",
		//departments is a relationship
		//rooms is a relationship
		//lab_personnel is a relationship

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer"
	);

	/** Relationships */
	protected static $INSPECTIONS_RELATIONSHIP = array(
		"className"	=>	"Inspection",
		"tableName"	=>	"inspection",
		"keyName"	=>	"key_id",
		"foreignKeyName"	=>	"principal_investigator_id"
	);

	public static $ROOMS_RELATIONSHIP = array(
		"className"	=>	"Room",
		"tableName"	=>	"principal_investigator_room",
		"keyName"	=>	"room_id",
		"foreignKeyName"	=>	"principal_investigator_id"
	);

	public static $LABPERSONNEL_RELATIONSHIP = array(
		"className"	=>	"User",
		"tableName"	=>	"erasmus_user",
		"keyName"	=>	"key_id",
		"foreignKeyName"	=>	"supervisor_id"
	);

	public static $DEPARTMENTS_RELATIONSHIP = array(
		"className"	=>	"Department",
		"tableName"	=>	"principal_investigator_department",
		"keyName"	=>	"department_id",
		"foreignKeyName"	=>	"principal_investigator_id"
	);

	public static $PRINCIPAL_INVESTIGATOR_ROOMS_RELATIONSHIP = array(
		"className"	=>	"PrincipalInvestigatorRoomRelation",
		"tableName"	=>	"principal_investigator_room",
		"keyName"	=>	"key_id",
		"foreignKeyName"	=>	"principal_investigator_id"
	);

/** Base User object that this PI represents */
	private $user_id;
	private $user;

	/** Array of Departments to which this PI belongs */
	private $departments;

	/** Array of Room entities managed by this PI */
	private $rooms;

	/** Array of LabPersonnel entities */
	private $labPersonnel;

	/** Array of Inspection entities */
	private $inspections;


	private $principal_investigator_room_relations;


	public function __construct(){

		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getLabPersonnel");
		$entityMaps[] = new EntityMap("lazy","getRooms");
		$entityMaps[] = new EntityMap("eager","getDepartments");
		$entityMaps[] = new EntityMap("eager","getUser");
		$entityMaps[] = new EntityMap("lazy","getInspections");
		$entityMaps[] = new EntityMap("lazy","getPrincipal_investigator_room_relations");
		$this->setEntityMaps($entityMaps);

	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	public function getUser(){
		if($this->user == null) {
			$userDAO = new GenericDAO(new User());
			$this->user = $userDAO->getById($this->user_id);
		}
		return $this->user;
	}
	public function setUser($user){
		$this->user = $user;
	}

	public function getUser_id(){ return $this->user_id; }
	public function setUser_id($id){ $this->user_id = $id; }

	public function getDepartments(){
		if($this->departments === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->departments = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$DEPARTMENTS_RELATIONSHIP));
		}
		return $this->departments;
	}
	public function setDepartments($departments){ $this->departments = $departments; }

	public function getRooms(){
		if($this->rooms === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->rooms = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$ROOMS_RELATIONSHIP));
		}
		return $this->rooms;
	}
	public function setRooms($rooms){ $this->rooms = $rooms; }

	public function getLabPersonnel(){
		if($this->labPersonnel === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->labPersonnel = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$LABPERSONNEL_RELATIONSHIP));
		}
		return $this->labPersonnel;
	}
	public function setLabPersonnel($labPersonnel){ $this->labPersonnel = $labPersonnel; }

	public function getInspections(){
		if($this->inspections === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->inspections = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$INSPECTIONS_RELATIONSHIP));
		}
		return $this->inspections;
	}
	public function setInspections($inspections){ $this->inspections = $inspections; }

	public function getPrincipal_investigator_room_relations(){
		if($this->principal_investigator_room_relations == null) {
			$thisDAO = new GenericDAO($this);
			$this->principal_investigator_room_relations = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$PRINCIPAL_INVESTIGATOR_ROOMS_RELATIONSHIP));
		}
		return $this->principal_investigator_room_relations;
	}


}
?>
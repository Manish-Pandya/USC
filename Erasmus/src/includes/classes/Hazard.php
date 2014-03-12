<?php
/**
 * 
 * 
 * 
 * @author Hoke Currie, GraySail LLC
 */
class Hazard extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "hazard";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"	=> "text",
		//parent hazard is a relationship
		//subhazards are relationships
		"parent_hazard_id" => "integer",
		//checklist is a relationship
		//rooms are relationships

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer"
	);
	
	/** Relationships */
	protected static $ROOMS_RELATIONSHIP = array(
			"className"	=>	"Room",
			"tableName"	=>	"hazard_room",
			"keyName"	=>	"room_id",
			"foreignKeyName"	=>	"hazard_id"
	);
	
	protected static $HAZARDS_RELATIONSHIP = array(
			"className"	=>	"Hazard",
			"tableName"	=>	"hazard",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"parent_hazard_id"
	);
	
	protected static $CHECKLIST_RELATIONSHIP = array(
			"className"	=>	"Checklist",
			"tableName"	=>	"checklist",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"hazard_id"
	);
	
	/** Name of the hazard */
	private $name;
	
	/** parent Hazard entity */
	private $parent_hazard_id;
	
	/** Array of child Hazard entities */
	private $subHazards;
	
	/** The single Checklist entity associated with this Hazard */
	private $checklist;
	
	/** Array of Room entities in which this Hazard is contained */
	private $rooms;
	
	//TODO: Room relationship should/may contain information about Equipment, etc
	
	public function __construct(){
	
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }
	
	public function getParent_hazard_id(){ return $this->parent_hazard_id; }
	public function setParent_hazard_id($parent_hazard_id){ $this->parent_hazard_id = $parent_hazard_id; }
	
	public function getSubHazards(){ 
		if($this->subHazards === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->subHazards = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$HAZARDS_RELATIONSHIP));
		}
		return $this->subHazards;
	}
	public function setSubHazards($subHazards){ $this->subHazards = $subHazards; }
	
	public function getChecklist(){ 
		if($this->checklist === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$checklistArray = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$CHECKLIST_RELATIONSHIP));
			if (isset($checklistArray[0])) {$this->checklist = $checklistArray[0];}
		}
		return $this->checklist;
	}
	public function setChecklist($checklist){ $this->checklist = $checklist; }
	
	public function getRooms(){ 
		if($this->rooms === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->rooms = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$ROOMS_RELATIONSHIP));
		}
		return $this->rooms;
	}
	public function setRooms($rooms){ $this->rooms = $rooms; }
	
	}
?>
<?php

include_once 'GenericCrud.php';
include_once 'Hazard.php';

/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Room extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "room";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"		=> "text",
		"safety_contact_information" 	=> "text",
		"building_id"		=> "integer",

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer"
							);


	protected static $PIS_RELATIONSHIP = array(
			"className"	=>	"PrincipalInvestigator",
			"tableName"	=>	"principal_investigator_room",
			"keyName"	=>	"principal_investigator_id",
			"foreignKeyName"	=>	"room_id"
	);

	public static $HAZARDS_RELATIONSHIP = array(
			"className"	=>	"Hazard",
			"tableName"	=>	"hazard_room",
			"keyName"	=>	"hazard_id",
			"foreignKeyName"	=>	"room_id"
	);

	public static $HAZARD_ROOMS_RELATIONSHIP = array(
			"className"	=>	"Hazard_room_relation",
			"tableName"	=>	"hazard_room",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"room_id"
	);

	private $name;

	/** Reference to the Building entity that contains this Room */
	private $building_id;
	private $building;

	/** Array of PricipalInvestigator entities that manage this Room */
	private $principalInvestigators;

	/** Array of Hazard entities contained in this Room */
	private $hazards;

	/** String containing emergency contact information */
	private $safety_contact_information;

	/** String containing emergency contact information */
	private $containsHazard;

	/** A collection of hazard_room_relations this room has a relationship to **/
	private $hazard_room_relations;

	private $has_hazards;

	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
		$entityMaps[] = new EntityMap("lazy","getHazards");
		$entityMaps[] = new EntityMap("lazy","getHazard_room_relations");
		$entityMaps[] = new EntityMap("lazy","getHas_hazards");
		$entityMaps[] = new EntityMap("eager","getBuilding");
		$this->setEntityMaps($entityMaps);

	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors / Mutators
	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }

	public function getBuilding_id(){ return $this->building_id; }
	public function setBuilding_id($building_id){ $this->building_id = $building_id; }

	public function getBuilding(){
		if($this->building == null) {
			$buildingDAO = new GenericDAO(new Building());
			$this->building = $buildingDAO->getById($this->building_id);
		}
		return $this->building;
	}
	public function setBuilding($building){
		$this->building = $building;
	}

	public function getHazards(){
		if($this->hazards == null) {
			$thisDAO = new GenericDAO($this);
			$this->hazards = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$HAZARDS_RELATIONSHIP), NULL, NULL, TRUE);
			$LOG = Logger::getLogger(__CLASS__);
			//General Hazards are present in every room
			//In addition to the Hazards this room is related to, we also get all hazards that are either the General Hazard or it's SubHazards

			// Get the db connection
			global $db;

			$queryString = "SELECT * FROM hazard WHERE key_id = 9999 OR parent_hazard_id = 9999";
			$LOG->debug("query: " . $queryString);
			$stmt = $db->prepare($queryString);
			$stmt->execute();
			$generalHazards = $stmt->fetchAll(PDO::FETCH_CLASS, "Hazard");
			$this->hazards = array_merge($this->hazards, $generalHazards);

		}
		return $this->hazards;
	}
	public function setHazards($hazards){ $this->hazards = $hazards; }

	public function getPrincipalInvestigators(){
		if($this->principalInvestigators == null) {
			$thisDAO = new GenericDAO($this);
			$this->principalInvestigators = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$PIS_RELATIONSHIP), NULL, NULL, TRUE);
		}
		return $this->principalInvestigators;
	}
	public function setPrincipalInvestigators($principalInvestigators){ $this->principalInvestigators = $principalInvestigators; }

	public function getSafety_contact_information(){ return $this->safety_contact_information; }
	public function setSafety_contact_information($contactInformation){ $this->safety_contact_information = $contactInformation; }

	public function getContainsHazard(){ return $this->containsHazard; }
	public function setContainsHazard($containsHazard){ $this->containsHazard = $containsHazard; }

	public function getHazard_room_relations(){
		if($this->hazard_room_relations == null) {
			$thisDAO = new GenericDAO($this);
			$this->hazard_room_relations = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$HAZARD_ROOMS_RELATIONSHIP));
		}
		return $this->hazard_room_relations;
	}

	public function getHas_hazards(){
		$LOG = Logger::getLogger(__CLASS__);

		$this->has_hazards = false;
		// Get the db connection
		global $db;

		$queryString = "SELECT COUNT(*) FROM hazard_room WHERE room_id = " . $this->key_id;
		$stmt = $db->prepare($queryString);
		$stmt->execute();
		$number_of_rows = $stmt->fetchColumn();
		if($number_of_rows > 0) $this->has_hazards =  true;
		return $this->has_hazards;
	}


}
?>
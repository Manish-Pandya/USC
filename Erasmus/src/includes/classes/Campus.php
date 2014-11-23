<?php

include_once 'GenericCrud.php';
include_once 'Hazard.php';

/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Campus extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "campus";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"		=> "text",

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer"
	);


	protected static $BUILDINGS_RELATIONSHIP = array(
			"className"	=>	"Building",
			"tableName"	=>	"building",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"campus_id"
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

	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getBuilding");
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

	public function getBuildings(){
		if($this->buildings == null) {
			$thisDAO = new GenericDAO($this);
			$this->buildings = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$BUILDINGS_RELATIONSHIP));
		}
		return $this->building;
	}
	public function setBuilding($buildings){
		$this->buildings = $buildings;
	}

	public function getHazard_room_relations(){
	if($this->hazard_room_relations == null) {

		}
		return $this->hazard_room_relations;
	}


}
?>
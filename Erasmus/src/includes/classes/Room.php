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
		"last_modified_user_id"			=> "integer"
							);
		
	
	protected static $PIS_RELATIONSHIP = array(
			"className"	=>	"PrincipalInvestigator",
			"tableName"	=>	"principal_investigator_room",
			"keyName"	=>	"principal_investigator_id",
			"foreignKeyName"	=>	"room_id"
	);
	
	protected static $HAZARDS_RELATIONSHIP = array(
			"className"	=>	"Hazard",
			"tableName"	=>	"hazard_room",
			"keyName"	=>	"hazard_id",
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
	
	public function __construct(){
	
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
		$this->building_id = $building->getKey_id();
	}
	
	public function getHazards(){
		if($this->hazards == null) {
			$thisDAO = new GenericDAO($this);
			$this->hazards = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationShip::fromArray(self::$HAZARDS_RELATIONSHIP));
		}
		return $this->hazards;
	}
	public function setHazards($hazards){ $this->hazards = $hazards; }

	public function getPrincipalInvestigators(){
		if($this->principalInvestigators == null) {
			$thisDAO = new GenericDAO($this);
			$this->principalInvestigators = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationShip::fromArray(self::$PIS_RELATIONSHIP));
		}
		return $this->principalInvestigators;
	}
	public function setPrincipalInvestigators($principalInvestigators){ $this->principalInvestigators = $principalInvestigators; }
	
	public function getSafety_contact_information(){ return $this->safety_contact_information; }
	public function setSafety_contact_information($contactInformation){ $this->safety_contact_information = $contactInformation; }
	
}
?>
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
		"chem_hazards_present"			=> "boolean",
		"rad_hazards_present"			=> "boolean",
		"bio_hazards_present"			=> "boolean",
		//"chem_hazards_present"			=> "boolean",
		//"rad_hazards_present"			=> "boolean",
		//"bio_hazards_present"			=> "boolean",

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer",
		"purpose"	=>	"text"
							);


	protected static $PIS_RELATIONSHIP = array(
			"className"	=>	"PrincipalInvestigator",
			"tableName"	=>	"principal_investigator_room",
			"keyName"	=>	"principal_investigator_id",
			"foreignKeyName"	=>	"room_id"
	);

	public static $HAZARDS_RELATIONSHIP = array(
			"className"	=>	"Hazard",
			"tableName"	=>	"principal_investigator_hazard_room",
			"keyName"	=>	"hazard_id",
			"foreignKeyName"	=>	"room_id"
	);

	public static $HAZARD_ROOMS_RELATIONSHIP = array(
			"className"	=>	"PrincipalInvestigatorHazardRoomRelation",
			"tableName"	=>	"principal_investigator_hazard_room",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"room_id"
	);

	public static $CONTAINERS_RELATIONSHIP = array(
			"className" =>  "SolidsContainer",
			"tableName" =>  "solids_container",
			"keyName" 	=>  "key_id",
			"foreignKeyName"	=>  "room_id"
	);

	private $name;

	private $purpose;

	/** Reference to the Building entity that contains this Room */
	private $building_id;
	private $building;
    private $building_name;

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

	/** String containing emergency contact information */
	private $chem_hazards_present;

	/** String containing emergency contact information */
	private $rad_hazards_present;

	/** String containing emergency contact information */
	private $bio_hazards_present;

	/** Array of solid waste containers present in this room */
	private $solidsContainers;

	/** Boolean to indicate whether this Room has relationships with more than 1 PrincipalInvestigator */
	private $hasMultiplePIs;

	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
		$entityMaps[] = new EntityMap("lazy","getHazards");
		$entityMaps[] = new EntityMap("lazy","getHazard_room_relations");
		$entityMaps[] = new EntityMap("lazy","getHas_hazards");
		$entityMaps[] = new EntityMap("eager","getBuilding");
		$entityMaps[] = new EntityMap("lazy","getSolidsContainers");
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

	public function getPurpose(){ return $this->purpose; }
	public function setPurpose($purpose){ $this->purpose = $purpose; }

	public function getChem_hazards_present() {
        if($this->chem_hazards_present == null){
            $this->getHazardTypesArePresent();
        }
        return $this->chem_hazards_present;
    }
	public function setChem_hazards_present($chem_hazards_present){ $this->chem_hazards_present = (boolean) $chem_hazards_present; }

	public function getRad_hazards_present() {
        if($this->rad_hazards_present == null){
            $this->getHazardTypesArePresent();
        }
        return $this->rad_hazards_present;
    }
	public function setRad_hazards_present($rad_hazards_present){ $this->rad_hazards_present = (boolean) $rad_hazards_present; }

	public function getBio_hazards_present() {
        if($this->bio_hazards_present == null){
            $this->getHazardTypesArePresent();
        }
        return $this->bio_hazards_present;
    }
	public function setBio_hazards_present($bio_hazards_present){ $this->bio_hazards_present = (boolean) $bio_hazards_present; }

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

    public function getBuilding_name(){
		if($this->building_name == null && $this->getBuilding_id() != null) {
			$buildingDAO = new GenericDAO(new Building());
            $bldg = $buildingDAO->getById($this->building_id);
			$this->building_name = $bldg->getAlias() != null ? $bldg->getAlias() : $bldg->getName();
		}
		return $this->building_name;
	}

	public function getHazards(){
		if($this->hazards == null) {
			$thisDAO = new GenericDAO($this);
			$this->hazards = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$HAZARDS_RELATIONSHIP), array("parent_hazard_id", "order_index"), NULL, TRUE, "parent_hazard_id");
			$LOG = Logger::getLogger(__FUNCTION__);
			//General Hazards are present in every room
			//In addition to the Hazards this room is related to, we also get all hazards that are either the General Hazard or it's SubHazards

			// Get the db connection
			global $db;

			$queryString = "SELECT * FROM hazard WHERE key_id = 9999 OR parent_hazard_id = 9999 ORDER BY parent_hazard_id, order_index";
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

	public function getSolidsContainers() {
		if( $this->solidsContainers === NULL && $this->hasPrimaryKeyValue() ) {
			$thisDao = new GenericDAO($this);
			$this->solidsContainers = $thisDao->getRelatedItemsById(
					$this->getKey_id(), DataRelationship::fromArray(self::$CONTAINERS_RELATIONSHIP));

		}
		return $this->solidsContainers;
	}

	public function setSolidsContainers($newContainers) {
		$this->solidsContainers = $newContainers;
	}

	public function getHasMultiplePIs(){
		if($this->hasMultiplePIs == NULL){
			$this->hasMultiplePIs = false;
			if(count($this->getPrincipalInvestigators()) > 1) $this->hasMultiplePIs = true;
		}
		return $this->hasMultiplePIs;
	}

    public function getHazardTypesArePresent(){
        $LOG = Logger::getLogger(__CLASS__ );
        //IDS of the direct children of the root hazard, except General Hazards, which are present in all rooms
        $branchIds = "1, 10009, 10010";

        // Get the db connection
        global $db;

        //get all the Relationships between this hazard and rooms that this PI has, so we can determine if this PI or ANY PI has the hazard in any of these rooms
        $queryString = "SELECT DISTINCT parent_hazard_id
                        FROM hazard a
                        LEFT JOIN principal_investigator_hazard_room b
                        ON a.key_id = b.hazard_id
                        LEFT JOIN principal_investigator_room c
                        ON b.principal_investigator_id = c.principal_investigator_id
                        LEFT JOIN principal_investigator d
                        ON b.principal_investigator_id = d.key_id
                        WHERE a.is_active = true
                        AND d.is_active = true
                        AND a.parent_hazard_id IN ($branchIds)
                        AND b.room_id = $this->key_id
                        AND c.room_id = $this->key_id";
        $stmt = $db->prepare($queryString);
        $stmt->execute();

        $bioPresent = false;
        $chemPresent = false;
        $radPresent = false;

        while($id = $stmt->fetchColumn()){
			if($id == 1){
                $bioPresent = true;
            }elseif($id == 10009){
                $chemPresent = true;
            }elseif($id == 10010){
                $radPresent = true;
            }
		}
        $this->bio_hazards_present = $bioPresent;
        $this->chem_hazards_present = $chemPresent;
        $this->rad_hazards_present = $radPresent;
    }


}
?>
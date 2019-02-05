<?php

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
		"animal_facility" 	=> "boolean",
		"building_id"		=> "integer",
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


	public static $PIS_RELATIONSHIP = array(
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
    private $campus_name;

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

	private $chem_hazards_present;
	private $rad_hazards_present;
	private $bio_hazards_present;
	private $lasers_present;
	private $recombinant_dna_present;
	private $xrays_present;
    private $flammable_gas_present;
	private $toxic_gas_present;
	private $corrosive_gas_present;
	private $hf_present;
    private $animal_facility;

	/** Array of solid waste containers present in this room */
	private $solidsContainers;

	/** Boolean to indicate whether this Room has relationships with more than 1 PrincipalInvestigator */
	private $hasMultiplePIs;

	private $_hazardTypesComputed = false;

	public function __construct(){

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
		$entityMaps[] = new EntityMap("lazy","getHazards");
		$entityMaps[] = new EntityMap("lazy","getHazard_room_relations");
		$entityMaps[] = new EntityMap("lazy","getHas_hazards");
		$entityMaps[] = new EntityMap("eager","getBuilding");
		$entityMaps[] = new EntityMap("lazy","getSolidsContainers");
		return $entityMaps;
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
        $this->getHazardTypesArePresent();
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
        $this->getHazardTypesArePresent();
        return $this->bio_hazards_present;
    }
	public function setBio_hazards_present($bio_hazards_present){ $this->bio_hazards_present = (boolean) $bio_hazards_present; }

    public function getLasers_present(){
        if($this->lasers_present == null){
            $this->getHazardTypesArePresent();
        }
        return $this->lasers_present;
	}
	public function setLasers_present($lasers_present){
		$this->lasers_present = $lasers_present;
	}

	public function getRecombinant_dna_present(){
        if($this->recombinant_dna_present == null){
            $this->getHazardTypesArePresent();
        }
		return $this->recombinant_dna_present;
	}
	public function setRecombinant_dna_present($recombinant_dna_presen){
		$this->recombinant_dna_present = $recombinant_dna_presen;
	}

	public function getXrays_present(){
        if($this->xrays_present == null){
            $this->getHazardTypesArePresent();
        }
		return $this->xrays_present;
	}
	public function setXrays_present($xrays_present){
		$this->xrays_present = $xrays_present;
	}

    public function getCorrosive_gas_present(){
        if($this->corrosive_gas_present == null){
            $this->getHazardTypesArePresent();
        }
		return $this->corrosive_gas_present;
	}
	public function setCorrosive_gas_present($xrays_present){
		$this->corrosive_gas_present = $xrays_present;
	}

    public function getFlammable_gas_present(){
        if($this->flammable_gas_present == null){
            $this->getHazardTypesArePresent();
        }
		return $this->flammable_gas_present;
	}
	public function setFlammable_gas_present($xrays_present){
		$this->flammable_gas_present = $xrays_present;
	}

    public function getToxic_gas_present(){
        if($this->toxic_gas_present == null){
            $this->getHazardTypesArePresent();
        }
		return $this->toxic_gas_present;
	}
	public function setToxic_gas_present($xrays_present){
		$this->toxic_gas_present = $xrays_present;
	}

    public function getHf_present(){
        if($this->hf_present == null){
            $this->getHazardTypesArePresent();
        }
		return $this->hf_present;
	}
	public function setHf_present($xrays_present){
		$this->hf_present = $xrays_present;
	}


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

    public function getCampus_Name(){
        if($this->campus_name == null && $this->getBuilding_id() != null) {
			$buildingDAO = new GenericDAO(new Building());
            $bldg = $buildingDAO->getById($this->building_id);
            if($bldg->getCampus_id() != null){
                $this->campus_name = $bldg->getCampus()->getName();
            }
		}
		return $this->campus_name;
    }

	public function getHazards(){
		if($this->hazards == null) {
			$thisDAO = new GenericDAO($this);
			$this->hazards = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$HAZARDS_RELATIONSHIP), array("parent_hazard_id", "order_index"), NULL, TRUE, "parent_hazard_id");
			$LOG = Logger::getLogger(__FUNCTION__);
			//General Hazards are present in every room
			//In addition to the Hazards this room is related to, we also get all hazards that are either the General Hazard or it's SubHazards

			// Get the db connection
			$db = DBConnection::get();

			$queryString = "SELECT * FROM hazard WHERE key_id = 9999 OR parent_hazard_id = 9999 ORDER BY parent_hazard_id, order_index";
			$LOG->debug("query: " . $queryString);
			$stmt = DBConnection::prepareStatement($queryString);
			$stmt->execute();
			$generalHazards = $stmt->fetchAll(PDO::FETCH_CLASS, "Hazard");
			$this->hazards = array_merge($this->hazards, $generalHazards);

		}
		return $this->hazards;
	}
	public function setHazards($hazards){ $this->hazards = $hazards; }

	public function getPrincipalInvestigators(){
		if($this->principalInvestigators == null && !is_array($this->principalInvestigators)) {
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
		$db = DBConnection::get();

		$queryString = "SELECT COUNT(*) FROM hazard_room WHERE room_id = " . $this->key_id;
		$stmt = DBConnection::prepareStatement($queryString);
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
		if( $this->_hazardTypesComputed ){
			return $this->_hazardTypesComputed;
		}

		$LOG = Logger::getLogger(__CLASS__ );

        // Get the db connection
        $db = DBConnection::get();

        $this->bio_hazards_present = false;
        $this->chem_hazards_present = false;
        $this->rad_hazards_present = false;
        $this->lasers_present = false;
        $this->xrays_present = false;
        $this->recombinant_dna_present = false;
        $this->flammable_gas_present = false;
	    $this->toxic_gas_present = false;
	    $this->corrosive_gas_present = false;
        $this->hf_present = false;

        if($this->getPrincipalInvestigators() == null)return;

		$sql = "SELECT * FROM room_hazards WHERE room_id = :rid";
		$stmt = DBConnection::prepareStatement($sql);
		$stmt->bindValue(':rid', $this->key_id);
		$stmt->execute();
		$hazardCategories = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->bio_hazards_present = boolval( $hazardCategories['bio_hazards_present'] );
        $this->chem_hazards_present = boolval( $hazardCategories['chem_hazards_present'] );
        $this->rad_hazards_present = boolval( $hazardCategories['rad_hazards_present'] );
        $this->lasers_present = boolval( $hazardCategories['lasers_present'] );
        $this->xrays_present = boolval( $hazardCategories['xrays_present'] );
        $this->recombinant_dna_present = boolval( $hazardCategories['recombinant_dna_present'] );
        $this->flammable_gas_present = boolval( $hazardCategories['flammable_gas_present'] );
	    $this->toxic_gas_present = boolval( $hazardCategories['toxic_gas_present'] );
	    $this->corrosive_gas_present = boolval( $hazardCategories['corrosive_gas_present'] );
        $this->hf_present = boolval( $hazardCategories['hf_present'] );

		$this->_hazardTypesComputed = true;
		return $this->_hazardTypesComputed;
    }

    public function getAnimal_facility(){
		if( !$this->hasPrimaryKeyValue() ){
			// If we don't have a pkey, then we can't determine if we are a special lab.
			return false;
		}

        $db = DBConnection::get();

        $queryString = "select count(*)
                        from room a
                        where a.key_id in(select room_id from principal_investigator_room
	                        where principal_investigator_id in (select principal_investigator_id from principal_investigator_department where department_id = 2)
                        )
                        AND a.key_id = $this->key_id";
        $stmt = DBConnection::prepareStatement($queryString);
        $stmt->execute();
        $num_rows = $stmt->fetchAll();
        //$l = Logger::getLogger(__FUNCTION__);
        //$l->fatal($this->key_id);
        //$l->fatal($num_rows);
        return (bool) $this->animal_facility = $num_rows[0][0] > 0;
    }
    public function setAnimal_facility($af){$this->animal_facility = $af;}


}
?>
<?php
/**
 * TODO: DOC
 *
 * @author Mitch Martin, GraySail LLC
 */
class PrincipalInvestigator extends GenericCrud {

	/** Name of the DB Table */
	public const TABLE_NAME = 'principal_investigator';
	protected static $TABLE_NAME = self::TABLE_NAME;

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//TODO: IS user a relationship?
		"user_id" => "integer",
		"inspection_notes" => "text",
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
		"foreignKeyName" =>	"principal_investigator_id"
	);

	public static $ROOMS_RELATIONSHIP = array(
		"className"	=>	"Room",
		"tableName"	=>	"principal_investigator_room",
		"keyName"	=>	"room_id",
		"foreignKeyName" =>	"principal_investigator_id"
	);

	public static $LABPERSONNEL_RELATIONSHIP = array(
		"className"	=>	"User",
		"tableName"	=>	"erasmus_user",
		"keyName"	=>	"key_id",
		"foreignKeyName" =>	"supervisor_id"
	);

	public static $DEPARTMENTS_RELATIONSHIP = array(
		"className"	=>	"Department",
		"tableName"	=>	"principal_investigator_department",
		"keyName"	=>	"department_id",
		"foreignKeyName" =>	"principal_investigator_id"
	);

	public static $PRINCIPAL_INVESTIGATOR_ROOMS_RELATIONSHIP = array(
		"className"	=>	"PrincipalInvestigatorRoomRelation",
		"tableName"	=>	"principal_investigator_room",
		"keyName"	=>	"key_id",
		"foreignKeyName" =>	"principal_investigator_id"
	);

	public static $ACTIVEPARCELS_RELATIONSHIP = array(
			"className" => "Parcel",
			"tableName" => "parcel",
			"keyName"   => "key_id",
			"foreignKeyName" => "principal_investigator_id"
	);

	public static $PURCHACEORDERS_RELATIONSHIP = array(
			"className" => "PurchaseOrder",
			"tableName" => "purchase_order",
			"keyName"   => "key_id",
			"foreignKeyName" => "principal_investigator_id"
	);

	public static $CARBOY_USE_CYCLES_RELATIONSHIP = array(
			"className" => "CarboyUseCycle",
			"tableName" => "carboy_use_cycle",
			"keyName"   => "key_id",
			"foreignKeyName" => "principal_investigator_id"
	);

	public static $SCINT_VIAL_COLLECTION_RELATIONSHIP = array(
			"className" => "ScintVialCollection",
			"tableName" => "scint_vial_collection",
			"keyName"   => "key_id",
			"foreignKeyName" => "principal_investigator_id"
	);

    public static $WASTE_BAG_RELATIONSHIP = array(
			"className" => "WasteBag",
			"tableName" => "waste_bag",
			"keyName"   => "key_id",
			"foreignKeyName" => "principal_investigator_id"
	);

    public static $OTHER_WASTE_CONTAINER_RELATIONSHIP = array(
        "className" => "OtherWasteContainer",
        "tableName" => "other_waste_container",
        "keyName"   => "key_id",
        "foreignKeyName" => "principal_investigator_id"
    );

	public static $PICKUPS_RELATIONSHIP = array(
			"className" => "Pickup",
			"tableName" => "pickup",
			"keyName"   => "key_id",
			"foreignKeyName" => "principal_investigator_id"
	);

	public static $QUARTERLY_INVENTORIES_RELATIONSHIP = array(
			"className" => "PIQuarterlyInventory",
			"tableName" => "pi_quarterly_inventory",
			"keyName"   => "key_id",
			"foreignKeyName" => "principal_investigator_id"
	);

	public static $VERIFICATIONS_RELATIONSHIP = array(
			"className" => "Verification",
			"tableName" => "verification",
			"keyName"   => "key_id",
			"foreignKeyName" => "principal_investigator_id"
	);

    public static $WIPE_TESTS_RELATIONSHIP = array(
			"className" => "PIWipeTest",
			"tableName" => "pi_wipe_test",
			"keyName"   => "key_id",
			"foreignKeyName" => "principal_investigator_id"
	);

	public static $PROTOCOLS_RELATIONSHIP = array(
			"className" => "IBCProtocol",
			"tableName" => "protocol_pi",
			"keyName"   => "key_id",
			"foreignKeyName" => "protocol_id"
	);

    public static $OTHER_WASTE_TYPES_ALLOWED = array(
        "className" => "OtherWasteType",
        "tableName" => "other_waste_type_pi",
        "keyName"   => "other_waste_type_id",
        "foreignKeyName" => "principal_investigator_id"
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

	/** Array of Authorizations entities */
	private $pi_authorization;

	/** Array of Active (not completed) parcels */
	private $activeParcels;

	/** Array of PurchaseOrder entities **/
	private $purchaseOrders;

	/** Array of WasteBag entities **/
	private $wasteBags;
    private $currentWasteBag;

	/** Array of CarboyUseCycle entities **/
	private $carboyUseCycles;

	/** Array of Carboy entities **/
	private $activeCarboys;

	/** Notes for inspections.   **/
	private $inspection_notes;

	/** Array of Pickup entities **/
	private $pickups;

	/** Array of collections of scint vials that are ready for pickup or were in a given pickup **/
	private $scintVialCollections;

	/** isotopes in scint vials that are ready for pickup **/
	private $scintVialAmounts;
    private $otherWasteContainers;


	/** collections of scint vials that haven't been picked up **/
	private $currentScintVialCollections;

	/** QuarterlyInventories for this PI **/
	private $quarterly_inventories;

	private $buildings;

	private $verifications;
	private $currentVerifications;
    private $currentIsotopeInventories;

    private $wipeTests;

	private $protocols;

    private $name;

    private $otherWasteTypes;

	public function __construct(){

	}

	public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = EntityMap::eager("getLabPersonnel");
		$entityMaps[] = EntityMap::lazy("getRooms");
		$entityMaps[] = EntityMap::eager("getDepartments");
		$entityMaps[] = EntityMap::eager("getUser");
		$entityMaps[] = EntityMap::lazy("getInspections");
		$entityMaps[] = EntityMap::lazy("getActiveParcels");
		$entityMaps[] = EntityMap::eager("getCarboyUseCycles");
		$entityMaps[] = EntityMap::lazy("getPurchaseOrders");
		$entityMaps[] = EntityMap::lazy("getWasteBags");
		$entityMaps[] = EntityMap::lazy("getOtherWasteContainers");
        $entityMaps[] = EntityMap::lazy("getCurrentWasteBag");
		$entityMaps[] = EntityMap::lazy("getPickups");
		$entityMaps[] = EntityMap::lazy("getScintVialCollections");
		$entityMaps[] = EntityMap::lazy("getCurrentScintVialCollections");
		$entityMaps[] = EntityMap::lazy("getOpenInspections");
		$entityMaps[] = EntityMap::lazy("getQuarterly_inventories");
		$entityMaps[] = EntityMap::lazy("getCurrentVerifications");
		$entityMaps[] = EntityMap::lazy("getVerifications");
		$entityMaps[] = EntityMap::lazy("getPi_authorization");
		$entityMaps[] = EntityMap::lazy("getCurrentPi_authorization");
		$entityMaps[] = EntityMap::lazy("getWipeTests");
		$entityMaps[] = EntityMap::lazy("getProtocols");
		$entityMaps[] = EntityMap::lazy("getGetOtherWasteTypes");

		return $entityMaps;
	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	/*
	 * @return User
	 */
	public function getUser(){
		if($this->user == null && $this->user_id !== null) {
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
			$thisDAO = new PrincipalInvestigatorDAO();
			$this->rooms = $thisDAO->getRooms($this->getKey_id());
		}
		return $this->rooms;
	}
	public function setRooms($rooms){ $this->rooms = $rooms; }

	public function getLabPersonnel(){
		if($this->labPersonnel === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			// Get only Active related users
			$this->labPersonnel = $thisDAO->getRelatedItemsById(
				$this->getKey_id(),
				DataRelationship::fromArray(self::$LABPERSONNEL_RELATIONSHIP),
				null, true, true);
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

	public function getActiveParcels() {
		if($this->activeParcels === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			// Note: By default GenericDAO will only return active parcels, which is good - the client probably
			// doesn't care about parcels that have already been completely used up. A getAllParcels method can be
			// added later if necessary.
			$this->activeParcels = $thisDao->getRelatedItemsById(
					$this->getKey_id(),
					DataRelationship::fromArray(self::$ACTIVEPARCELS_RELATIONSHIP)
			);
		}
		return $this->activeParcels;
	}

	public function getPurchaseOrders(){
		if($this->purchaseOrders === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			// Note: By default GenericDAO will only return active parcels, which is good - the client probably
			// doesn't care about parcels that have already been completely used up. A getAllParcels method can be
			// added later if necessary.
			$this->purchaseOrders = $thisDao->getRelatedItemsById(
					$this->getKey_id(),
					DataRelationship::fromArray(self::$PURCHACEORDERS_RELATIONSHIP)
			);
		}
		return $this->purchaseOrders;
	}
	public function setPurchaseOrders($purchaseOrders){$this->purchaseOrders = $purchaseOrders;}

	public function getCarboyUseCycles(){
		if($this->carboyUseCycles === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->carboyUseCycles = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$CARBOY_USE_CYCLES_RELATIONSHIP));
		}
		return $this->carboyUseCycles;
	}
	public function setCarboyUseCycles($carboyUseCycles){$this->carboyUseCycles = $carboyUseCycles;}

	public function getPickups(){
		if($this->pickups === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->pickups = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PICKUPS_RELATIONSHIP));
		}
		return $this->pickups;
	}
	public function setPickups($pickups){$this->pickups = $pickups;}

	public function getScintVialCollections(){

		if($this->scintVialCollections === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			// Note: By default GenericDAO will only return active parcels, which is good - the client probably
			// doesn't care about parcels that have already been completely used up. A getAllParcels method can be
			// added later if necessary.
			$this->scintVialCollections = $thisDao->getRelatedItemsById(
					$this->getKey_id(),
					DataRelationship::fromArray(self::$SCINT_VIAL_COLLECTION_RELATIONSHIP),
                    array('key_id')
			);
		}
		return $this->scintVialCollections;
	}

	public function getCurrentScintVialCollections(){
		//todo:  determine if this should be one-to-many or one to one.
		$LOG = Logger::getLogger(__CLASS__);
		$svCollections = $this->getScintVialCollections();

		$this->currentScintVialCollections = array();
		foreach($svCollections as $collection){
			if($collection->getPickup_id() == null)$this->currentScintVialCollections[] = $collection;
		}
		return $this->currentScintVialCollections;
	}

	public function getInspection_notes() {
		return $this->inspection_notes;
	}
	public function setInspection_notes($inspection_notes) {
		$this->inspection_notes = $inspection_notes;
	}
	public function getOpenInspections(){
		// Get the db connection
		$db = DBConnection::get();

		$LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );


		$queryString = "SELECT * FROM inspection WHERE principal_investigator_id =  $this->key_id AND date_closed IS NULL";
		$stmt = DBConnection::prepareStatement($queryString);
		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, "Inspection");
			// ... otherwise, die and echo the db error
		} else {
			$error = $stmt->errorInfo();
			die($error[2]);
		}

		$LOG->debug($result);

		return $result;
	}

	public function getQuarterly_inventories(  ){
		$LOG = Logger::getLogger(__CLASS__);
		$LOG->debug("Get quarterly inventories for $this");
		if($this->quarterly_inventories === NULL && $this->hasPrimaryKeyValue()) {
			$LOG->debug("Loading quarterly inventories...");
			$thisDAO = new GenericDAO($this);
			/** quarterly inventories should be sorted by date */
			$this->quarterly_inventories = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$QUARTERLY_INVENTORIES_RELATIONSHIP), array("date_last_modified"), true);

			$LOG->debug("Loaded " . count($this->quarterly_inventories) . " quarterly inventories. Sorting...");
            usort($this->quarterly_inventories, function($a, $b)
            {
                return strcmp($a->getEnd_date(), $b->getEnd_date());
            });
        }
		return $this->quarterly_inventories;
	}

	public function getBuildings(){return  $this->buildings;}
	public function setBuildings($buildings){$this->buildings = $buildings;}

	public function getVerifications(){
		if($this->verifications === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->verifications = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$VERIFICATIONS_RELATIONSHIP));
		}
		return $this->verifications;
	}

	public function getCurrentVerifications(){
		if($this->currentVerifications === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO(new Verification());
			$whereClauseGroup = new WhereClauseGroup(
				array(
						new WhereClause("principal_investigator_id", "=" , $this->getKey_id()),
						new WhereClause("completed_date", "IS", NULL),
						new WhereClause("notification_date", "IS NOT", NULL)
				)
			);
			$this->currentVerifications = $thisDAO->getAllWhere($whereClauseGroup);
		}
		return $this->currentVerifications;
	}

	public function getPi_authorization(){
		if($this->pi_authorization == NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO(new PIAuthorization());
			$whereClauseGroup = new WhereClauseGroup(
					array(
						new WhereClause("principal_investigator_id", "=" , $this->getKey_id()),
					)
			);
			$this->pi_authorization = $thisDAO->getAllWhere($whereClauseGroup);
		}
		return $this->pi_authorization;
	}

    public function getCurrentPi_authorization(){
		$LOG = Logger::getLogger(__CLASS__);

		if($this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO(new PIAuthorization());
			$whereClauseGroup = new WhereClauseGroup(
					array(
						new WhereClause("principal_investigator_id", "=" , $this->getKey_id()),
					)
			);
			$auths = $thisDAO->getAllWhere($whereClauseGroup, "AND", "Approval_date");
            if($auths != null){
                return end($auths);
            }
		}
		else{
			$LOG->warn("$this has no primary key!");
		}
        return array();

	}

    public function getCurrentIsotopeInventories(){
        if($this->currentIsotopeInventories == null && $this->hasPrimaryKeyValue() && $this->getCurrentPi_authorization() != null){
            $inventoriesDao = new IsotopeDAO();
            $this->currentIsotopeInventories = $inventoriesDao->getCurrentInvetoriesByPiId($this->key_id, $this->getCurrentPi_authorization()->getKey_id());
        }
        return $this->currentIsotopeInventories;
    }

    public function getWipeTests(){
		if($this->wipeTests === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->wipeTests = $thisDao->getRelatedItemsById(
					$this->getKey_id(),
					DataRelationship::fromArray(self::$WIPE_TESTS_RELATIONSHIP)
			);
		}
		return $this->wipeTests;
	}
	public function setWipeTests($wipeTests){$this->wipeTests = $wipeTests;}

	public function getProtocols(){
        if($this->protocols === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->protocols = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PROTOCOLS_RELATIONSHIP));
		}
		return $this->protocols;
	}
	public function setProtocols($protocols){
		$this->protocols = $protocols;
	}

    public function getName(){
		if( $this->hasPrimaryKeyValue() && $this->getUser() != NULL ) {
			$this->name = $this->user->getName();
		}
		return $this->name;
	}
	public function setName($name){$this->name = $name;}

    public function getOtherWasteContainers(){
        if($this->otherWasteContainers === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->otherWasteContainers = $thisDao->getRelatedItemsById(
					$this->getKey_id(),
					DataRelationship::fromArray(self::$OTHER_WASTE_CONTAINER_RELATIONSHIP),
                    array('date_created')
			);
		}
        return $this->otherWasteContainers;
    }
    public function setOtherWasteContainers($bags){$this->otherWasteContainers = $bags;}

    public function getWasteBags(){

		if($this->wasteBags === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->wasteBags = $thisDao->getRelatedItemsById(
					$this->getKey_id(),
					DataRelationship::fromArray(self::$WASTE_BAG_RELATIONSHIP),
                    array('date_created')
			);
		}

		return $this->wasteBags;
	}
    public function setWasteBags($bags){$this->wasteBags = $bags;}
	public function getCurrentWasteBag(){
		if(($this->currentWasteBag == null || $this->wasteBags === NULL) && $this->hasPrimaryKeyValue()) {
			$bagDao = new GenericDAO(new WasteBag());
            $group = new WhereClauseGroup(array(
                new WhereClause("principal_investigator_id","=",$this->key_id),
                new WhereClause("pickup_id","IS","NULL")
			));
			$bags = $bagDao->getAllWhere($group);
            $this->currentWasteBag = end($bags);
		}
		return $this->currentWasteBag;
	}
    public function setCurrentWasteBag($bag){$this->currentWasteBag = $bag;}

    public function getOtherWasteTypes(){
		if($this->otherWasteTypes === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->otherWasteTypes = $thisDao->getRelatedItemsById(
					$this->getKey_id(),
					DataRelationship::fromArray(self::$OTHER_WASTE_TYPES_ALLOWED)
			);
		}
        return $this->otherWasteTypes;
	}
	public function setOtherWasteTypes($otherWasteTypes){ $this->otherWasteTypes = $otherWasteTypes; }

}

?>
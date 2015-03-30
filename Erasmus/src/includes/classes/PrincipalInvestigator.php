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

	public static $AUTHORIZATIONS_RELATIONSHIP = array(
		"className" =>  "Authorization",
		"tableName" =>  "authorization",
		"keyName"   =>  "key_id",
		"foreignKeyName"	=> "principal_investigator_id"
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

	public static $CABOY_USE_CYCLES_RELATIONSHIP = array(
		"className" => "CarboyUseCycle",
		"tableName" => "carboy_use_cycle",
		"keyName"   => "key_id",
		"foreignKeyName" => "principal_investigator_id"
	);

	public static $SOLIDS_CONTAINERS_RELATIONSHIP = array(
		"className" => "SolidsContainer",
		"tableName" => "solids_container",
		"keyName"   => "key_id",
		"foreignKeyName" => "principal_investigator_id"
	);

	public static $PICKUPS_RELATIONSHIP = array(
		"className" => "Pickup",
		"tableName" => "pickup",
		"keyName"   => "key_id",
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
	private $authorizations;

	/** Array of Active (not completed) parcels */
	private $activeParcels;

	/** Array of PurchaseOrder entities **/
	private $purchaseOrders;

	/** Array of SolidsContainer entities **/
	private $solidsContainers;

	/** Array of CarboyUseCycle entities **/
	private $carboyUseCycles;

	/** Array of Carboy entities **/
	private $activeCarboys;

	/** Array of Pickup entities **/
	private $pickups;
	
	/** isotopes in scint vials that are ready for pickup **/
	private $scintVialAmounts;

	public function __construct(){

		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getLabPersonnel");
		$entityMaps[] = new EntityMap("lazy","getRooms");
		$entityMaps[] = new EntityMap("eager","getDepartments");
		$entityMaps[] = new EntityMap("eager","getUser");
		$entityMaps[] = new EntityMap("lazy","getInspections");
		$entityMaps[] = new EntityMap("lazy","getAuthorizations");
		$entityMaps[] = new EntityMap("lazy", "getActiveParcels");
		$entityMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
		$entityMaps[] = new EntityMap("lazy", "getPurchaseOrders");
		$entityMaps[] = new EntityMap("lazy", "getSolidsContainers");
		$entityMaps[] = new EntityMap("lazy", "getPickups");

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

	public function getAuthorizations() {
		if($this->authorizations === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->authorizations = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$AUTHORIZATIONS_RELATIONSHIP));
		}
		return $this->authorizations;
	}
	public function setAuthorizations($authorizations) { $this->authorizations = $authorizations; }

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
			$this->carboyUseCycles = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$CABOY_USE_CYCLES_RELATIONSHIP));
		}
		return $this->carboyUseCycles;
	}
	public function setCarboyUseCycles($carboyUseCycles){$this->carboyUseCycles = $carboyUseCycles;}

	public function getSolidsContainers(){
		if($this->solidsContainers === NULL && $this->hasPrimaryKeyValue()) {

			// get rooms this PI has
			$rooms = $this->getRooms();

			// get containers in each room
			$containers = array();
			foreach($rooms as $room) {
				$containers = array_merge($room->getSolidsContainers(), $containers);
			}

			$this->solidsContainers = $containers;
		}
		return $this->solidsContainers;
	}
	public function setSolidsContainers($solidsContainers){$this->solidsContainers = $solidsContainers;}

	public function getPickups(){
		if($this->pickups === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->pickups = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PICKUPS_RELATIONSHIP));
		}
		return $this->pickups;
	}
	public function setPickups($pickups){$this->pickups = $pickups;}
	
	public function getSVIsotopeAmounts(){
		
		//build array of ParcelUseAmounts that went into Scint Vials
		$amounts = array();
		foreach($this->getActiveParcels() as $parcel){
			$uses = $parcel->getParcelUses();
			foreach($uses as $use){
				$useAmounts = $use->getParcelUseAmounts();
				foreach($useAmounts as $useAmount){
					if($useAmount->getWaste_type()->getName() == "Vial"){
						array_push($amounts, $useAmount);
					}
				}
			}
		}
		
		$radShim = new RadCrud();
		
		if($amounts != NULL)$this->scitnVialsForPickups = $radShim->sumUsages($amounts);
		return $this->scintVialsForPickups;
	
	}
}

?>
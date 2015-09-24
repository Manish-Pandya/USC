<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author Perry Cate, GraySail LLC
 */
class Parcel extends RadCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "parcel";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"principal_investigator_id"		=> "integer",
		"purchase_order_id"				=> "integer",
		"status"						=> "text",
		"isotope_id"					=> "integer",
		"arrival_date"					=> "timestamp",
		"quantity"						=> "float",
		"rs_number"						=> "text",
		"authorization_id"				=> "integer",

		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);

	/** Relationships */
	protected static $PARCELUSE_RELATIONSHIP = array(
		"className" => "ParcelUse",
		"tableName" => "parcel_use",
		"keyName"	=> "key_id",
		"foreignKeyName" => "parcel_id"
	);
	
	protected static $WIPE_TEST_RELATIONSHIP = array(
			"className" => "ParcelWipeTest",
			"tableName" => "parcel_wipe_test",
			"keyName"	=> "key_id",
			"foreignKeyName" => "parcel_id"
	);


	//access information

	/** Reference to the principal investigator this parcel belongs to. */
	private $principal_investigator;
	private $principal_investigator_id;

	/** Reference to the purchase order used to obtain this parcel. */
	private $purchase_order;
	private $purchase_order_id;

	/** String containing the status of this parcel. */
	private $status;

	/** Reference to the isotope this parcel contains */
	private $isotope;
	private $isotope_id;

	/** Date this parcel will arrive/arrived. */
	private $arrival_date;

	/** Float quantity of isotope in the parcel. */
	private $quantity;

	/** Float ammount of isotope that has not been used yet. */
	private $remainder;
	
	/** Float ammount of isotope that has not been picked up yet, regardless of whether it has been used */
	private $onHand;

	/** Array of parcel uses that pertain to this parcel. */
	private $parcelUses;

	/* Text field human readable unique ID for orders*/
	private $rs_number;
	
	/* collection of IsotopeAmountDTOs that went into scint vials for this parcel */
	private $svIsotopeAmounts;
	
	/** wipe test done on this parcel **/
	private $wipe_test;
	
	/** id of the authorization that allows PI to have this parcel **/
	private $authorization_id;
	private $authorization;

	public function __construct() {
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
		$entityMaps[] = new EntityMap("lazy", "getPurchase_order");
		$entityMaps[] = new EntityMap("lazy", "getIsotope");
		$entityMaps[] = new EntityMap("eager", "getParcelUses");
		$entityMaps[] = new EntityMap("eager", "getRemainder");
		$entityMaps[] = new EntityMap("eager", "getWipe_test");
		$this->setEntityMaps($entityMaps);
	}

	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}

	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors / Mutators
	public function getPrincipal_investigator_id() { return $this->principal_investigator_id; }
	public function setPrincipal_investigator_id($newId) { $this->principal_investigator_id = $newId; }

	public function getPrincipal_investigator() {
		if($this->principal_investigator == null) {
			$principal_investigatorDAO = new GenericDAO(new PrincipalInvestigator());
			$this->principal_investigator = $principal_investigatorDAO->getById($this->getPrincipal_investigator_id());
		}
		return $this->principal_investigator;
	}
	public function setPrincipal_investigator($newPI) {
		$this->principal_investigator = $newPI;
	}

	public function getPurchase_order_id() { return $this->purchase_order_id; }
	public function setPurchase_order_id($newId) { $this->purchase_order_id = $newId; }

	public function getPurchase_order() {
		if($this->purchase_order == null) {
			$purchase_orderDAO = new GenericDAO(new PurchaseOrder());
			$this->purchase_order = $purchase_orderDAO->getById($this->getPurchase_order_id());
		}
		return $this->purchase_order;
	}
	public function setPurchase_order($newOrder) {
		$this->purchase_order = $newOrder;
	}

	public function getStatus() { return $this->status; }
	public function setStatus($newStatus) { $this->status = $newStatus; }

	public function getIsotope_id() { return $this->isotope_id; }
	public function setIsotope_id($newId) { $this->isotope_id = $newId; }

	public function getAuthorization() {
		if($this->authorization == null) {
			$authDao = new GenericDAO(new Authorization());
			$this->authorization = $authDao->getById($this->getAuthorization_id());
		}
		return $this->authorization;
	}
	public function setAuthorization($auth) {
		$this->authorization = $auth;
	}

	public function getArrival_date() { return $this->arrival_date; }
	public function setArrival_date($newDate) { $this->arrival_date = $newDate; }

	public function getQuantity() { return $this->quantity; }
	public function setQuantity($newQuantity) { $this->quantity = $newQuantity; }

	public function getParcelUses() {
		if($this->parcelUses == null && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->parcelUses = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PARCELUSE_RELATIONSHIP));
		}
		return $this->parcelUses;
	}
	public function setParcelUses($newUsesArray) {
		$this->parcelUses = $newUsesArray;
	}

	public function getRemainder() {
		if($this->remainder == null) {
			// Get total amount used from this parcel
			$uses = $this->getParcelUses();
			$usedAmount = 0;
			foreach($uses as $use) {
				$usedAmount += $use->getQuantity();
			}

			// subtract the amount used from the initial quantity
			$this->remainder = $this->getQuantity() - $usedAmount;
		}
		return $this->remainder;
	}
	
	public function getOnHand(){
		$this->onHand = $this->getQuantity();
		$uses = $this->getParcelUses();
		foreach($uses as $use){
			$amounts = $use->getParcelUseAmounts();
			foreach ($amounts as $amount){
				$amount = new ParcelUseAmount();
				if($amount->getPickedUp() == true){
					$this->onHand -= $amount->getCurie_level();
				}
			}
		}
		
		return $this->onHand;
	}

	public function getRs_number(){return $this->rs_number;}
	public function setRs_number($rs_number){$this->rs_number = $rs_number;}
	
	public function getSVIsotopeAmounts(){
		
		//get use amounts
		$svAmounts = array();
		foreach($this->getParcelUses() as $use){
			$innerAmounts = array_filter(
					$use->getParcelUseAmounts(),
					function ($e) {
						$wasteType = $e->getWaste_type();
						return $wasteType->getName() == "Vial";
					}
			);
			$svAmounts = array_merge($svAmounts, $innerAmounts);
		}
		
		//sum use amounts
		if($svAmounts != NULL)$this->svIsotopeAmounts = $this->sumUsages($svAmounts);
		return $this->svIsotopeAmounts;
		
	}
	
	public function getWipe_test() {
		if($this->wipe_tests == null && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->wipe_tests = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$WIPE_TEST_RELATIONSHIP));
		}
		return $this->wipe_tests;
	}
	
	public function setWipe_test($test){
		$this->wipe_tests = array($test);
	}
	
	public function getAuthorization_id() {return $this->authorization_id;}
	public function setAuthorization_id($authorization_id) {$this->authorization_id = $authorization_id;}
}
?>
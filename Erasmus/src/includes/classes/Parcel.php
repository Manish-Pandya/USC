<?php

include_once 'GenericCrud.php';

/**
 * 
 * 
 * 
 * @author Perry Cate, GraySail LLC
 */
class Parcel extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "parcel";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"principal_investigator_id"		=> "integer",
		"purchase_order_id"				=> "integer", 
		"status"						=> "text",
		"isotope_id"					=> "integer",
		"arrival_date"					=> "DateTime",
		"quantity"						=> "float",
		
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
		"keyName"	=> "kay_id",
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

	
	public function __construct() {
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
		$entityMaps[] = new EntityMap("lazy", "getPurchase_order");
		$entityMaps[] = new EntityMap("eager", "getIsotope");
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
	
	public function getIsotope() {
		if($this->isotope == null) {
			$isotopeDAO = new GenericDAO(new Isotope());
			$this->isotope = $isotopeDAO->getById($this->getIsotope_id());
		}
		return $this->isotope;
	}
	public function setIsotope($newIsotope) {
		$this->isotope = $newIsotope;
	}
	
	public function getArrival_date() { return $this->arrival_date; }
	public function setArrival_date($newDate) { $this->arrival_date = $newDate; }
	
	public function getQuantity() { return $this->quantity; }
	public function setQuantity($newQuantity) { $this->quantity = $newQuantity; }
	
	public function getUses() {
		if($this->uses == null && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->uses = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PARCELUSE_RELATIONSHIP));
		}
		return $this->uses;
	}
	public function setUses($newUsesArray) {
		$this->uses = $newUsesArray;
	}
	
}
?>
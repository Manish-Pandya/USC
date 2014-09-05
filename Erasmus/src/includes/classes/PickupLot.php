<?php

include_once 'GenericCrud.php';

/**
 * 
 * 
 * 
 * @author Perry Cate, GraySail LLC
 */
class PickupLot extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "pickup_lot";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"pickup_id"						=> "integer",
		"isotope_id"					=> "integer",
		"curie_level"					=> "float",
		"waste_type_id"					=> "integer",
		
		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);
	
	/** Relationships */
	protected static $DISPOSALLOTS_RELATIONSHIP = array(
		"className" => "DisposalLot",
		"tableName" => "disposal_lot",
		"keyName"	=> "key_id",
		"foreignKeyName"	=> "pickup_lot_id"
	);
	
	
	//access information

	/** Integer containing the id of the pickup this lot was in. */
	private $pickup_id; 
	// reference to the pickup itself omitted because no use case for that yet.
	
	/** Reference to the isotope this pickup lot contains. */
	private $isotope;
	private $isotope_id;
	
	/** Float amount in curies of radiation present. */
	private $curie_level;
	
	/** Type of waste this lot contains. */
	private $waste_type;
	private $waste_type_id;
	
	/** Array of children Disposal Lots */
	private $disposalLots;
	
	
	public function __construct() {
		
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager", "getIsotope");
		$entitymaps[] = new EntityMap("eager", "getWaste_type");
		$entityMaps[] = new EntityMap("lazy", "getDisposalLots");
		$this->setEntityMaps($entityMaps);

	}
	
	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}
	
	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	// Getters / Setters
	
	public function getPickup_id() { return $this->pickup_id; }
	public function setPickup_id($newId) { $this->pickup_id = $newId; }
	
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
	
	public function getIsotope_id() { return $this->isotope_id; }
	public function setIsotope_id($newId) { $this->isotope_id = $newId; }
	
	public function getCurie_level() { return $this->curie_level; }
	public function setCurie_level($newLevel) { $this->curie_level = $newLevel; }
	
	public function getWaste_type() {
		if($this->waste_type == null) {
			$wasteDAO = new GenericDAO(new WasteType());
			$this->waste_type = $wasteDAO->getById($this->getWaste_type_id());
		}
		return $this->waste_type;
	}
	public function setWaste_type($newType) {
		$this->waste_type = $newType;
	}
	
	public function getWaste_type_id() { return $this->waste_type_id; }
	public function setWaste_type_id($newId) { $this->waste_type_id = $newId; }
	
	public function getDisposalLots() {
		if($this->DisposalLots === NULL) {
			$thisDao = new GenericDAO($this);
			$this->disposalLots = $thisDao->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$DISPOSALLOTS_RELATIONSHIP));
		}
		return $this->disposalLots;
	}
	public function setDisposalLots($newLots) {
		$this->disposalLots = $newLots;
	}
	
}
?>
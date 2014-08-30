<?php

include_once 'GenericCrud.php';

/**
 * 
 * 
 * 
 * @author Perry Cate, GraySail LLC
 */
class DisposalLot extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "disposal_lot";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"pickup_lot_id"					=> "integer",
		"drum_id"						=> "integer",
		"curie_level"					=> "float",
		
		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);
	
	//access information

	/** Reference to the pickup lot this disposal lot comes from. */
	private $pickup_lot;
	private $pickup_lot_id;
	
	/** Reference to the drum this lot is to be disposed in. */
	private $drum;
	private $drum_id;
	
	/** Float amount of curies in this lot */
	private $curie_level;
	
	
	public function __construct() {
		
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getPickup_lot");
		$entityMaps[] = new EntityMap("lazy", "getDrum");
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
	public function getPickup_lot() {
		if($this->pickup_lot == null) {
			$lotDAO = new GenericDAO(new PickupLot());
			$this->pickup_lot = $lotDAO->getById($this->getPickup_lot_id());
		}
		return $this->pickup_lot;
	}
	public function setPickup_lot($newLot) {
		$this->pickup_lot = $newLot;
	}
	
	public function getPickup_lot_id() { return $this->pickup_lot_id; }
	public function setPickup_lot_id($newId) { $this->pickup_lot_id = $newId; }
	
	public function getDrum() { 
		if($this->drum == null) {
			$drumDAO = new GenericDAO(new Drum());
			$this->drum = $drumDAO->getById($this->getDrum_id());
		}
		return $this->drum;
	}
	public function setDrum($newDrum) {
		$this->drum = $newDrum;
	}
	
	public function getDrum_id() { return $this->drum_id; }
	public function setDrum_id($newId) { $this->drum_id = $newId; }
	
	public function getCurie_level() { return $this->curie_level; }
	public function setCurie_level($newLevel) { $this->curie_level = $newLevel; }
	
}
?>
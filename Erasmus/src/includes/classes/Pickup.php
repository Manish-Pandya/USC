<?php

include_once 'GenericCrud.php';

/**
 * 
 * 
 * 
 * @author Perry Cate, GraySail LLC
 */
class Pickup extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "pickup";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"pickup_date"					=> "timestamp",
		"pickup_user_id"				=> "integer",
		
		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);
	
	/** Relationships */
	public static $CARBOYS_RELATIONSHIP = array(
		"className" => "Carboy",
		"tableName" => "carboy_use_cycle",
		"keyName"   => "key_id",
		"foreignKeyName" => "pickup_id"
	);
	
	public static $WASTEBAGS_RELATIONSHIP = array(
		"className" => "WasteBag",
		"tableName" => "waste_bag",
		"keyName"   => "key_id",
		"foreignKeyName" => "pickup_id"	
	);
	
	//access information
	
	/** Date (timestamp) that this pickup occurred. */
	private $pickup_date;
	
	/** Integer id of the user who picked up the materials. */
	private $pickup_user_id;
	
	/** Array of Carboys picked up */
	private $carboys;
	
	/** Array of Waste Bags picked up */
	private $wastebags;
	

	public function __construct() {
		
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager", "getCarboys");
		$entityMaps[] = new EntityMap("eager", "getWasteBags");
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
	public function getPickup_date() { return $this->pickup_date; }
	public function setPickup_date($newDate) { $this->pickup_date = $newDate; }

	public function getPickup_user_id() { return $this->pickup_user_id; }
	public function setPickup_user_id($newId) { $this->pickup_user_id = $newId; }
	
	public function getCarboys() {
		if($this->carboys === null && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->carboys = $thisDao->getRelatedItemsById(
					$this->getKey_id(), DataRelationship::fromArray(self::$CARBOYS_RELATIONSHIP));
		}
		return $this->carboys;
	}
	public function setCarboys($newCarboys) {
		$this->carboys = $newCarboys;
	}
	
	
	public function getWasteBags() {
		if($this->wasteBags === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->wasteBags = $thisDao->getRelatedItemsById(
				$this->getKey_id(), DataRelationship::fromArray(self::$WASTEBAGS_RELATIONSHIP));
		}
		return $this->wasteBags;
	}
	public function setWasteBags($newBags) {
		$this->wasteBags = $newBags;
	}
}
?>
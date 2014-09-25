<?php

include_once 'GenericCrud.php';

/**
 * 
 * 
 * 
 * @author Perry Cate, GraySail LLC
 */
class Drum extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "drum";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"commission_date"				=> "timestamp",
		"retirement_date"				=> "timestamp",
		"status"						=> "text",
		"date_closed"					=> "timestamp",
		"pickup_date"					=> "timestamp",
		"shipping_info"					=> "text",
		
		
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
			"foreignKeyName"	=> "drum_id"
	);
	
	//access information

	/** DateTime containing the date this drum was made. */
	private $commission_date;
	
	/** DateTime containing the date this drum will be disposed of. */
	private $retirement_date;
	
	/** String containing the current status of this drum. */
	private $status;
	
	/** DateTime containing the date this drum was filled and closed. */
	private $date_closed;
	
	/** DateTime containing the date this drum was picked up for shipping. */
	private $pickup_date;
	
	/** String of details about this drum's shipping. */
	private $shipping_info;
	
	/** Array of disposal lots going into this drum. */
	private $disposalLots;
	
	
	public function __construct() {
		
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getDisposalLots");
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
	public function getCommission_date() { return $this->commission_date; }
	public function setCommission_date($newDate) { $this->commission_date = $newDate; }
	
	public function getRetirement_date() { return $this->retirement_date; }
	public function setRetirement_date($newDate) { $this->retirement_date = $newDate; }
	
	public function getStatus() { return $this->status; }
	public function setStatus($newStatus) { $this->status = $newStatus; }
	
	public function getDate_closed() { return $this->date_closed; }
	public function setDate_closed($newDate) { $this->date_closed = $newDate; }
	
	public function getPickup_date() { return $this->pickup_date; }
	public function setPickup_date($newDate) { $this->pickup_date = $newDate; }
	
	public function getShipping_info() { return $this->shipping_info; }
	public function setShipping_info($newInfo) { $this->shipping_info = $newInfo; }
	
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
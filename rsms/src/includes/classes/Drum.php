<?php

include_once 'RadCrud.php';

/**
 * 
 * 
 * 
 * @author Perry Cate, GraySail LLC
 */
class Drum extends RadCrud {

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
		"label"							=> "text",
		
		
		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);
	
	/** Relationships */
	protected static $WASTEBAGS_RELATIONSHIP = array(
			"className" => "WasteBag",
			"tableName" => "waste_bag",
			"keyName"	=> "key_id",
			"foreignKeyName"	=> "drum_id"
	);
	
	public static $SCINT_VIAL_COLLECTION_RELATIONSHIP = array(
			"className" => "ScintVialCollection",
			"tableName" => "scint_vial_collection",
			"keyName"   => "key_id",
			"foreignKeyName" => "drum_id"
	);
	
	//access information

	/** timestamp containing the date this drum was made. */
	private $commission_date;
	
	/** timestamp containing the date this drum will be disposed of. */
	private $retirement_date;
	
	/** String containing the current status of this drum. */
	private $status;
	
	/** timestamp containing the date this drum was filled and closed. */
	private $date_closed;
	
	/** timestamp containing the date this drum was picked up for shipping. */
	private $pickup_date;
	
	/** String of details about this drum's shipping. */
	private $shipping_info;
	
	/** Array of Waste Bags that filled this drum*/
	private $wasteBags;
	
	/** Array of Scint Vial collections in this drum */
	private $scintVialCollections; 
	
	
	/** IsotopeAmountDTOs in this drum **/
	private $contents;
	
	private $label;
	
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
	
	public function getLabel() { return $this->label; }
	public function setLabel($label) { $this->label = $label;}
	
	public function getDate_closed() { return $this->date_closed; }
	public function setDate_closed($newDate) { $this->date_closed = $newDate; }
	
	public function getPickup_date() { return $this->pickup_date; }
	public function setPickup_date($newDate) { $this->pickup_date = $newDate; }
	
	public function getShipping_info() { return $this->shipping_info; }
	public function setShipping_info($newInfo) { $this->shipping_info = $newInfo; }
	
	public function getWasteBags() {
		if($this->wasteBags === NULL) {
			$thisDao = new GenericDAO($this);
			$this->wasteBags = $thisDao->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$WASTEBAGS_RELATIONSHIP));
		}
		return $this->wasteBags;
	}
	public function setWasteBags($newBags) {
		$this->wasteBags = $newBags;
	}
	
	public function getScintVialCollections(){
		if($this->scintVialCollections === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			// Note: By default GenericDAO will only return active parcels, which is good - the client probably
			// doesn't care about parcels that have already been completely used up. A getAllParcels method can be
			// added later if necessary.
			$this->scintVialCollections = $thisDao->getRelatedItemsById(
					$this->getKey_id(),
					DataRelationship::fromArray(self::$SCINT_VIAL_COLLECTION_RELATIONSHIP)
			);
		}
		return $this->scintVialCollections;
	}
	public function setScintVialCollections($collections) {
		$this->scintVialCollections = $collections;
	}
	
	public function getContents(){
		$LOG = Logger::getLogger(__CLASS__);
		$LOG->debug('getting contents for drum');
		$amounts = array();
		foreach($this->getWasteBags() as $bag){
			$LOG->debug($bag);
			if($bag->getParcelUseAmounts() != NULL){		
				$amounts = array_merge($amounts, $bag->getParcelUseAmounts());
			}
		}
		foreach($this->getScintVialCollections() as $collection){
			$LOG->debug($collection);

			if($collection->getParcel_use_amounts() != NULL){
				$amounts = array_merge($amounts, $collection->getParcel_use_amounts());
			}
		}
		$this->contents = $this->sumUsages($amounts);
		return $this->contents;
	}
	
	
}
?>
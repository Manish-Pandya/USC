<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Perry Cate, GraySail LLC
 */
class ParcelUse extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "parcel_use";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"quantity"						=> "float",
		"parcel_id"						=> "integer",
		"date_of_use"					=> "timestamp",

		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);

	/** Relationships */
	protected static $USEAMOUNTS_RELATIONSHIP = array(
		"className" => "ParcelUseAmount",
		"tableName" => "parcel_use_amount",
		"keyName"	=> "key_id",
		"foreignKeyName"	=> "parcel_use_id"
	);
	
	//access information

	/** Float containing the amount of isotope used */
	private $quantity;
	
	/** Reference to the Isotope entity this usage concerns */
	private $parcel;

	/** Integer containing the id of the parcel this usage concerns */
	private $parcel_id;
	
	/** timestamp of the date that this usage took place */
	private $date_of_use;
	
	/** Array of waste types and amounts from this use */
	private $parcelUseAmounts;
	

	public function __construct() {

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getParcel");
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
	public function getQuantity() { return $this->quantity; }
	public function setQuantity($newQuantity) { $this->quantity = $newQuantity; }
	
	public function getParcel_id() { return $this->parcel_id; }
	public function setParcel_id($newId) { $this->parcel_id = $newId; }
	
	public function getParcel() {
		if($this->parcel == null) {
			$parcelDAO = new GenericDAO(new Parcel());
			$this->parcel = $parcelDAO->getById($this->getParcel_id());
		}
		return $this->parcel;
	}
	public function setParcel($newParcel) {
		$this->parcel = $newParcel;
	}
	
	public function getDate_of_use() { return $this->date_of_use; }
	public function setDate_of_use($newDate) { $this->date_of_use = $newDate; }
	
	public function getParcelUseAmounts() {
		if($this->parcelUseAmounts === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->parcelUseAmounts = $thisDao->getRelatedItemsById(
					$this->getKey_id(),
					DataRelationship::fromArray(self::$USEAMOUNTS_RELATIONSHIP)
			);
		}
		return $this->parcelUseAmounts;
	}
	public function setParcelUseAmounts($newUseAmounts) {
		$this->parcelUseAmounts = $newUseAmounts;
	}

}
?>
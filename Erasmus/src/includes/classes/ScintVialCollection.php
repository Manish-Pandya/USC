<?php


include_once 'RadCrud.php';

/**
 *
 *  Persists the Collection of ParcelUseAmounts disposed in Scint Vials that went into a given pickup
 *
 * @author Matt Breeden, GraySail LLC
 */
class ScintVialCollection extends RadCrud{
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "scint_vial_collection";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
			"pickup_id"						=> "integer",
			"principal_investigator"		=> "integer",
	
			//GenericCrud
			"key_id"						=> "integer",
			"is_active"						=> "boolean",
			"date_last_modified"			=> "timestamp",
			"last_modified_user_id"			=> "integer",
			"date_created"					=> "timestamp",
			"created_user_id"				=> "integer"
	);
	
	public function __construct() {
		$LOG = Logger::getLogger(__CLASS__);
		$LOG->debug('contstuctor called');
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getPrincipalInvestigator");
		$entityMaps[] = new EntityMap("lazy", "getParcelUseAmounts");
		$entityMaps[] = new EntityMap("lazy", "getContents");
		$entityMaps[] = new EntityMap("lazy", "getPickup");
		$entityMaps[] = new EntityMap("eager", "getContents");
		
		$this->setEntityMaps($entityMaps);

	}
	

	/** Relationships */
	protected static $USEAMOUNTS_RELATIONSHIP = array(
			"className" => "ParcelUseAmount",
			"tableName" => "parcel_use_amount",
			"keyName"	=> "key_id",
			"foreignKeyName"	=> "scint_vial_collection_id"
	);
	
	private $principal_investigator;
	private $principal_investigator_id;
	
	private $parcel_use_amounts;
	private $contents;
	
	private $pickup_id;
	private $pickup;
	
	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}
	
	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	
	public function getParcelUseAmounts() {
		$LOG = Logger::getLogger(__CLASS__);
		
		if($this->parcel_use_amounts === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->parcel_use_amounts = $thisDao->getRelatedItemsById($this->getKey_id(),DataRelationship::fromArray(self::$USEAMOUNTS_RELATIONSHIP));
		}
		$LOG->debug("getting parcel use amounts sv");
		
		$LOG->debug($this->parcel_use_amounts);
		return $this->parcel_use_amounts;
	}
	public function setParcelUseAmounts($parcel_use_amounts) {
		$this->parcel_use_amounts = $parcel_use_amounts;
	}
		
	public function getContents(){
		$LOG = Logger::getLogger(__CLASS__);
		$LOG->debug('getting contents for sv collection.');
		$LOG->debug($this->getParcelUseAmounts());
		$this->contents = $this->sumUsages($this->getParcelUseAmounts());
		return $this->contents;
	}
	public function setContents($contents) {
		$this->contents = $contents;
	}
	
	public function getPrincipalInvestigatorId() {
		return $this->principal_investigator_id;
	}
	public function setPrincipalInvestigatorId($principal_investigator_id) {
		$this->principal_investigator_id = $principal_investigator_id;
	}
	

	public function getPrincipalInvestigator() {
		return $this->principal_investigator;
	}

	public function setPrincipalInvestigator($principal_investigator) {
		$this->principal_investigator = $principal_investigator;
	}
	
	public function getPickup_id() {
		return $this->pickup_id;
	}
	public function setPickup_id($pickup_id) {
		$this->pickup_id = $pickup_id;
	}
	
	public function getPickup() {
		if($this->pickup === null && $this->hasPrimaryKeyValue()) {
			$pickupDao = new GenericDAO(new Pickup());
			$this->pickup = $pickupDao->getById( $this->getPickup_id() );
		}
		return $this->pickup;
	}
	public function setPickup($newPickup) {
		$this->pickup = $newPickup;
	}
	
	
	
}

?>
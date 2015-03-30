<?php

class WasteBag extends RadCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "waste_bag";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"container_id" => "integer",
		"pickup_id"	   => "integer",
		"drum_id"	   => "integer",
		"curie_level"  => "float",
		"date_added"   => "timestamp",
		"date_removed" => "timestamp",

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer"
	);
	
	/** Relationships */
	protected static $USEAMOUNTS_RELATIONSHIP = array(
			"className" => "ParcelUseAmount",
			"tableName" => "parcel_use_amount",
			"keyName"	=> "key_id",
			"foreignKeyName"	=> "waste_bag_id"
	);
	

	/** container this bag went into */
	private $container;
	private $container_id;

	/** curie level of the isotope contained in this bag */
	private $curie_level;

	/** Pickup this bag was collected in */
	private $pickup;
	private $pickup_id;

	/** Drum this bag went into. */
	private $drum;
	private $drum_id;

	/** date this bag was added to its SolidsContainer */
	private $date_added;

	/** date this bag was removed from its SolidsContiner */
	private $date_removed;
	
	private $container_name;
	
	private $parcel_use_amounts;
	
	/** IsotopeAmountDTOs in this bag **/
	private $contents;

	public function __construct() {
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getContainer");
		$entityMaps[] = new EntityMap("lazy", "getPickup");
		$entityMaps[] = new EntityMap("lazy", "getDrum");
		$this->setEntityMaps($entityMaps);
	}

	// required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	public function getContainer() {
		if($this->container === null && $this->hasPrimaryKeyValue()) {
			$containerDao = new GenericDAO(new SolidsContainer());
			$this->container = $containerDao->getById($this->getContainer_id());
		}
		return $this->container;
	}
	public function setContainer($newContainer) {
		$this->container = $newContainer;
	}

	public function getContainer_id() {
		return $this->container_id;
	}
	public function setContainer_id($newId) {
		$this->container_id = $newId;
	}
	
	public function getContainer_name(){
		if($this->getContainer_id() != NULL && $this->container_name == NULL){
			$container = $this->getContainer();
			$this->container_name = $container->getName();
		}
		return $this->container_name;
	}

	public function getCurie_level() {
		return $this->curie_level;
	}
	public function setCurie_level($newLevel) {
		$this->curie_level = $newLevel;
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

	public function getPickup_id() { return $this->pickup_id; }
	public function setPickup_id($newId) { $this->pickup_id = $newId; }

	public function getDrum() {
		if($this->drum === null && $this->hasPrimaryKeyValue()) {
			$drumDao = new GenericDao(new Drum());
			$this->drum = $drumDao->getById( $this->getDrum_id() );
		}
		return $this->drum;
	}
	public function setDrum($newDrum) {
		$this->drum = $newDrum;
	}

	public function getDrum_id() { return $this->drum_id; }
	public function setDrum_id($newId) { $this->drum_id = $newId; }

	public function getDate_added()
	{
	    return $this->date_added;
	}

	public function setDate_added($date_added)
	{
	    $this->date_added = $date_added;
	}

	public function getDate_removed()
	{
	    return $this->date_removed;
	}

	public function setDate_removed($date_removed)
	{
	    $this->date_removed = $date_removed;
	}
	
	public function getParcelUseAmounts() {
		if($this->parcelUseAmounts === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->parcelUseAmounts = $thisDao->getRelatedItemsById($this->getKey_id(),DataRelationship::fromArray(self::$USEAMOUNTS_RELATIONSHIP));
		}
		return $this->parcelUseAmounts;
	}
	public function setParcelUseAmounts($parcel_use_amounts) {
		$this->parcel_use_amounts = $parcel_use_amounts;
	}
	
	public function getContents(){
		$LOG = Logger::getLogger(__CLASS__);
		$LOG->debug('getting contents for waste bag');
		$this->contents = $this->sumUsages($this->getParcelUseAmounts());
		return $this->contents;
	}
}
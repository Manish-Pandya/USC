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
        "is_scint_vial"                 => "boolean",
        "total_volume"                  => "float",
        "waste_volume"                  => "float",
        "date_destroyed"                => "timestamp",
        "destruction_method"            => "text",
        "epa_id"                        => "float",
        "manifest_number"               => "float",
        "shipment_number"               => "float",
        "shipment_weight"               => "float",


		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);

	/** Relationships */
	protected static $PICKUP_LOTS_RELATIONSHIP = array(
			"className" => "PickupLot",
			"tableName" => "pickup_lot",
			"keyName"	=> "key_id",
			"foreignKeyName"	=> "drum_id"
	);

	public static $SCINT_VIAL_COLLECTION_RELATIONSHIP = array(
			"className" => "ScintVialCollection",
			"tableName" => "scint_vial_collection",
			"keyName"   => "key_id",
			"foreignKeyName" => "drum_id"
	);

    public static $CARBOY_USE_CYCLE_RELATIONSHIP = array(
        "className" => "CarboyUseCycle",
        "tableName" => "carboy_use_cycle",
        "keyName"   => "key_id",
        "foreignKeyName" => "drum_id"
    );

    public static $WASTE_BAG_RELATIONSHIP = array(
        "className" => "WasteBag",
        "tableName" => "waste_bag",
        "keyName"   => "key_id",
        "foreignKeyName" => "drum_id"
    );

    public static $OTHER_WASTE_CONTAINTER_RELATIONSHIP = array(
        "className" => "OtherWasteContainer",
        "tableName" => "other_waste_container",
        "keyName"   => "key_id",
        "foreignKeyName" => "drum_id"
    );

    protected static $WIPE_TEST_RELATIONSHIP = array(
        "className" => "DrumWipeTest",
        "tableName" => "drum_wipe_test",
        "keyName"	=> "key_id",
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

	/** Array of Solid Pickup Lots that filled this drum*/
	private $pickupLots;

	/** Array of Scint Vial collections in this drum */
	private $scintVialCollections;

    /** Array of CarboyUseCycles in this drum */
	private $carboyUseCycles;

    /** Array of CarboyUseCycles in this drum */
	private $wasteBags;

    /** Array of CarboyUseCycles in this drum */
	private $otherWasteContainers;

    /** Is this a drum for scint_vials?  if not it is one for solids */
    private $is_scint_vial;

	/** IsotopeAmountDTOs in this drum **/
	private $contents;

	private $label;

    private $hasTests;
    /** wipe test done on this parcel **/
	private $wipe_test;

    private $total_volume;
    private $waste_volume;
    private $date_destroyed;
    private $destruction_method;
    private $epa_id;
    private $manifest_number;
    private $shipment_number;
    private $shipment_weight;

	public function __construct() {

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getDisposalLots");
        $entityMaps[] = new EntityMap("lazy","getWipe_test");

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

	public function getPickupLots() {
		if($this->pickupLots === NULL) {
			$thisDao = new GenericDAO($this);
			$this->pickupLots = $thisDao->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PICKUP_LOTS_RELATIONSHIP));
		}
		return $this->pickupLots;
	}
	public function setPickupLots($newBags) {
		$this->pickupLots = $newBags;
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

    public function getCarboyUseCycles(){
		if($this->carboyUseCycles === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			// Note: By default GenericDAO will only return active parcels, which is good - the client probably
			// doesn't care about parcels that have already been completely used up. A getAllParcels method can be
			// added later if necessary.
			$this->carboyUseCycles = $thisDao->getRelatedItemsById(
					$this->getKey_id(),
					DataRelationship::fromArray(self::$CARBOY_USE_CYCLE_RELATIONSHIP)
			);
		}
		return $this->carboyUseCycles;
	}
	public function setCarboyUseCycles($cycles) {
		$this->carboyUseCycles = $cycles;
	}

    public function getWasteBags(){
		if($this->wasteBags === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			// Note: By default GenericDAO will only return active parcels, which is good - the client probably
			// doesn't care about parcels that have already been completely used up. A getAllParcels method can be
			// added later if necessary.
			$this->wasteBags = $thisDao->getRelatedItemsById(
					$this->getKey_id(),
					DataRelationship::fromArray(self::$WASTE_BAG_RELATIONSHIP)
			);
		}
		return $this->wasteBags;
	}
	public function setWasteBags($cycles) {
		$this->wasteBags = $cycles;
	}

    public function getOtherWasteContainers(){
		if($this->otherWasteContainers === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			// Note: By default GenericDAO will only return active parcels, which is good - the client probably
			// doesn't care about parcels that have already been completely used up. A getAllParcels method can be
			// added later if necessary.
			$this->otherWasteContainers = $thisDao->getRelatedItemsById(
					$this->getKey_id(),
					DataRelationship::fromArray(self::$OTHER_WASTE_CONTAINTER_RELATIONSHIP)
			);
		}
		return $this->otherWasteContainers;
	}
	public function setOtherWasteContainers($cycles) {
		$this->otherWasteContainers = $cycles;
	}

	public function getContents(){
		$LOG = Logger::getLogger(__CLASS__);
		$LOG->debug('getting contents for drum');
		$amounts = array();
		foreach($this->getPickupLots() as $lot){
			$amt = new ParcelUseAmount();
            $amt->setIsotope_id($lot->getIsotope_id());
            $amt->setCurie_level($lot->getCurie_level());
            array_push($amounts, $amt);
		}
        $amounts = array();

		foreach($this->getScintVialCollections() as $collection){
			if($collection->getParcel_use_amounts() != NULL){
				$amounts = array_merge($amounts, $collection->getParcel_use_amounts());
			}
		}
        foreach($this->getCarboyUseCycles() as $cycle){
			if($cycle->getParcelUseAmounts() != NULL){
				$amounts = array_merge($amounts, $cycle->getParcelUseAmounts());
			}
		}
        foreach($this->getOtherWasteContainers() as $cycle){

			if($cycle->getParcelUseAmounts() != NULL){
				$amounts = array_merge($amounts, $cycle->getParcelUseAmounts());
			}
		}

        foreach($this->getWasteBags() as $cycle){
			if($cycle->getParcelUseAmounts() != NULL){
				$amounts = array_merge($amounts, $cycle->getParcelUseAmounts());
			}
		}
        $LOG->debug($amounts);
		$this->contents = $this->sumUsages($amounts);
		return $this->contents;
	}

    public function getHasTests(){
		if($this->hasTests == null){
			$this->hasTests = false;
			if($this->getWipe_test() != null){
				$this->hasTests = true;
			}
		}
		return $this->hasTests;
	}

    public function getWipe_test() {
		if($this->wipe_test == null && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$tests = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$WIPE_TEST_RELATIONSHIP));
			$this->wipe_test = end($tests);
		}
		return $this->wipe_test;
	}

	public function setWipe_test($test){
		$this->wipe_test = $test;
	}

    public function getIs_scint_vial(){return $this->is_scint_vial;}
    public function setIs_scint_vial($is){$this->is_scint_vial = $is;}

    public function getTotal_volume(){return $this->total_volume;}
	public function setTotal_volume($total_volume){$this->total_volume = $total_volume;}

	public function getWaste_volume(){return $this->waste_volume;}
	public function setWaste_volume($waste_volume){$this->waste_volume = $waste_volume;}

	public function getDate_destroyed(){return $this->date_destroyed;}
	public function setDate_destroyed($date_destroyed){$this->date_destroyed = $date_destroyed;}

	public function getDestruction_method(){return $this->destruction_method;}
	public function setDestruction_method($destruction_method){$this->destruction_method = $destruction_method;}

	public function getEpa_id(){return $this->epa_id;}
	public function setEpa_id($epa_id){$this->epa_id = $epa_id;}

	public function getManifest_number(){return $this->manifest_number;}
	public function setManifest_number($manifest_number){$this->manifest_number = $manifest_number;}

	public function getShipment_number(){return $this->shipment_number;}
	public function setShipment_number($shipment_number){$this->shipment_number = $shipment_number;}

    public function getShipment_weight(){return $this->shipment_weight;}
	public function setShipment_weight($shipment_number){$this->shipment_weight = $shipment_number;}
}
?>
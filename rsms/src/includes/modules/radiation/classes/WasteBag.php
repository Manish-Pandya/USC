<?php

class WasteBag extends Container {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "waste_bag";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"pickup_id"	   => "integer",
		"drum_id"	   => "integer",
		"curie_level"  => "float",
		"date_added"   => "timestamp",
		"date_removed" => "timestamp",
        "comments"     => "text",
        "principal_investigator_id"		=> "integer",
        "label"        => "text",
        "open_date"			            => "timestamp",
		"close_date"			        => "timestamp",

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


    public static $PICKUP_LOT_RELATIONSHIP = array(
        "className" => "PickupLot",
        "tableName" => "pickup_lot",
        "keyName"   => "key_id",
        "foreignKeyName" => "waste_bag_id"
    );


    /** integer key id of the principal investigator this container belongs to. */
	private $principal_investigator_id;

	/** container this bag went into */
	private $container;
	private $container_id;

	/** curie level of the isotope contained in this bag */
	private $curie_level;

	/** Pickup this bag was collected in */
	private $pickup;
	

	/** Drum this bag went into. */
	private $drum;
	private $drum_id;

	/** date this bag was added to its SolidsContainer */
	private $date_added;

	/** date this bag was removed from its SolidsContiner */
	private $date_removed;

	private $container_name;

	private $parcel_use_amounts;

    private $comments;

    private $label;

	/** IsotopeAmountDTOs in this bag **/
	private $contents;

	private $pickupLots;

	public function __construct() {

	}

	public static function defaultEntityMaps() {
		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getContainer");
		$entityMaps[] = EntityMap::lazy("getPickup");
		$entityMaps[] = EntityMap::lazy("getDrum");
		$entityMaps[] = EntityMap::lazy("getParcelUseAmounts");

		return $entityMaps;
	}

	// required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	public function getCurie_level() {
		return $this->curie_level;
	}
	public function setCurie_level($newLevel) {
		$this->curie_level = $newLevel;
	}
    /**
     * @return Pickup
     */
	public function getPickup() {
		if($this->pickup === null && $this->hasPrimaryKeyValue()) {
			if( $this->getPickup_id() != null ){
				$pickupDao = new GenericDAO(new Pickup());
				$this->pickup = $pickupDao->getById( $this->getPickup_id() );
			}
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
		if($this->parcel_use_amounts === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->parcel_use_amounts = $thisDao->getRelatedItemsById($this->getKey_id(),DataRelationship::fromArray(self::$USEAMOUNTS_RELATIONSHIP), null, true);
		}
		return $this->parcel_use_amounts;
	}
	public function setParcelUseAmounts($parcel_use_amounts) {
		$this->parcel_use_amounts = $parcel_use_amounts;
	}

	public function getContents(){
		$LOG = Logger::getLogger(__CLASS__);
	    $contents = $this->sumUsages($this->getParcelUseAmounts());
        foreach($contents as $content){
            foreach($this->getPickupLots() as $lot){
                if($content->getIsotope_id() == $lot->getIsotope_id()){
                    $content->setCurie_level($content->getCurie_level() - $lot->getCurie_level());
                }
            }
        }
        $this->contents = $contents;
		return $this->contents;
	}

    public function getComments(){return $this->comments;}
    public function setComments($comments){$this->comments = $comments;}

    public function getPrincipal_investigator_id() { return $this->principal_investigator_id; }
	public function setPrincipal_investigator_id($newId) { $this->principal_investigator_id = $newId; }

    public function getPickupLots($isotope_id = null) {
		if($this->pickupLots === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->pickupLots = $thisDao->getRelatedItemsById(
				$this->getKey_id(), DataRelationship::fromArray(self::$PICKUP_LOT_RELATIONSHIP));
		}
		return $this->pickupLots;
	}
	public function setPickupLots($lots) {$this->pickupLots = $lots;}

    public function getLabel(){
        if($this->label == null && $this->key_id != null && $this->principal_investigator_id != null){
            $piDao = new GenericDAO(new PrincipalInvestigator());
            $pi = $piDao->getById($this->principal_investigator_id);
            $name = $pi->getUser() != null ? strtoupper($pi->getUser()->getLast_name()) : null;
            if($name) $this->label = $name . "-SLD-" . $this->key_id;
        }
        return $this->label;
    }
	public function setLabel($label){ $this->label = $label; }
}
<?php

/**
 * OtherWasteContainer short summary.
 *
 * OtherWasteContainer description.
 *
 * @version 1.0
 * @author Matt Breeden
 */
class OtherWasteContainer extends Container {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "other_waste_container";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"pickup_id"	   => "integer",
		"drum_id"	   => "integer",
        "comments"     => "text",
        "description"     => "text",
        "principal_investigator_id"		=> "integer",
	    "open_date"                     => "timestamp",
	    "close_date"                    => "timestamp",
	    "other_waste_type_id"           => "integer",

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
			"foreignKeyName"	=> "other_waste_container_id"
	);


    public static $PICKUP_LOT_RELATIONSHIP = array(
        "className" => "PickupLot",
        "tableName" => "pickup_lot",
        "keyName"   => "key_id",
        "foreignKeyName" => "other_waste_type_id"
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

	/** IsotopeAmountDTOs in this bag **/
	private $contents;

    private $other_waste_type_id;
    private $otherWasteTypeName;
    private $label;
    private $clearable;
    private $description;

	private $pickupLots;

	public function __construct() {

	}

	public static function defaultEntityMaps() {
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getContainer");
		$entityMaps[] = new EntityMap("lazy", "getPickup");
		$entityMaps[] = new EntityMap("lazy", "getDrum");

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

	public function getPickup() {
		if($this->pickup === null && $this->hasPrimaryKeyValue() && $this->getPickup_id() != null) {
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
    //TODO: these should all be get all where's that check for waste type id
	public function getParcelUseAmounts() {
		if($this->parcel_use_amounts === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->parcel_use_amounts = $thisDao->getRelatedItemsById($this->getKey_id(),DataRelationship::fromArray(self::$USEAMOUNTS_RELATIONSHIP));
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

    public function getOpen_date(){ return $this->open_date; }
	public function setOpen_date($open_date){ $this->open_date = $open_date; }

	public function getClose_date(){ return $this->close_date; }
	public function setClose_date($close_date){ $this->close_date = $close_date; }

    public function getOther_waste_type_id(){
		return $this->other_waste_type_id;
	}
	public function setOther_waste_type_id($other_waste_type_id){
		$this->other_waste_type_id = $other_waste_type_id;
	}

    public function getLabel(){
        if($this->label == null && $this->key_id != null && $this->principal_investigator_id != null){
            $piDao = new GenericDAO(new PrincipalInvestigator());
            $pi = $piDao->getById($this->principal_investigator_id);
            $name = $pi->getUser() != null ? strtoupper($pi->getUser()->getLast_name()) : null;
            if($name) $this->label = $name . "-". strtoupper(str_ireplace(" ", "-", $this->getOtherWasteTypeName())) . "-" . $this->key_id;
        }
        return $this->label;
    }
	public function setLabel($label){ $this->label = $label; }

	public function getOtherWasteTypeName(){
        if($this->otherWasteTypeName == null && $this->other_waste_type_id != null){
            $thisDao = new GenericDAO(new OtherWasteType());
			$otherWasteType = $thisDao->getById($this->other_waste_type_id);
            $this->otherWasteTypeName = $otherWasteType->getName();
        }
		return $this->otherWasteTypeName;
	}
	public function setOtherWasteTypeName($otherWasteTypeName){
		$this->otherWasteTypeName = $otherWasteTypeName;
	}

    public function getClearable(){
        if($this->clearable == null && $this->hasPrimaryKeyValue() && $this->other_waste_type_id != null){
            $d = new GenericDAO(new OtherWasteType());
            $type = $d->getById($this->other_waste_type_id);
            if($type != null)(bool) $this->clearable = $type->getClearable();
        }
        return $this->clearable;
    }

    public function getDescription(){return $this->description;}
    public function setDescription($d){$this->description = $d;}
}
<?php

/**
 *
 *
 *
 * @author Perry Cate, GraySail LLC
 */
class ParcelUse extends RadCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "parcel_use";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"parcel_id"						=> "integer",
		"quantity"						=> "float",
		"experiment_use"				=> "text",
		"date_used"						=> "timestamp",
        "date_transferred"               => "timestamp",
        "destination_parcel_id"         => "integer",

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

	/** Reference to the Isotope entity this usage concerns
     * @var Parcel
     * */
	private $parcel;

	/** Integer containing the id of the parcel this usage concerns */
	private $parcel_id;
    private $destinationParcel;

	/** Array of waste types and amounts from this use */
	private $parcelUseAmounts;

	/** How the parcel was used in an experiment */
	private $experiment_use;

	/** Date the parcel was used **/
	private $date_used;

    /** Amount of parent parcel on hand**/
    private $parcelAmountOnHand;

    /** Amount of parent parcel available for use **/
    private $parcelRemainder;
    private $piName;
    private $rsNumber;
    private $isotopeName;

    /** If this ParcelUse is a transfer, when did the transfer take place **/
    private $date_transferred;
    /** Is this a transfer? **/
    private $is_transfer;
    private $destination_parcel_id;

    private $is_mass;


	public function __construct() {

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getParcel");
        $entityMaps[] = EntityMap::lazy("getParcelAmountOnHand");
        $entityMaps[] = EntityMap::lazy("getParcelRemainder");

		return $entityMaps;
	}

	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}

	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors / Mutators
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

	public function getParcelUseAmounts() {
		if($this->parcelUseAmounts === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->parcelUseAmounts = $thisDao->getRelatedItemsById($this->getKey_id(),DataRelationship::fromArray(self::$USEAMOUNTS_RELATIONSHIP));
		}
		return $this->parcelUseAmounts;
	}
	public function setParcelUseAmounts($newUseAmounts) {$this->parcelUseAmounts = $newUseAmounts;}

	public function getQuantity() {return $this->quantity;}
    public function setQuantity($quantity) {$this->quantity = $quantity;}

    public function getExperiment_use() {return $this->experiment_use;}
    public function setExperiment_use($experiment_use) {$this->experiment_use = $experiment_use;}

    public function getDate_used() {return $this->date_used;}
    public function setDate_used($date_used) {$this->date_used = $date_used;}

    public function getParcelAmountOnHand(){
        $parentDao = new GenericDAO(new Parcel());
        $parent = $parentDao->getById($this->getParcel_id());
        $this->parcelAmountOnHand = $parent->getAmountOnHand();
        return $this->parcelAmountOnHand;
    }

    public function getParcelRemainder(){
        $parentDao = new GenericDAO(new Parcel());
        $parent = $parentDao->getById($this->getParcel_id());
        $this->parcelRemainder = $parent->getRemainder();
        return $this->parcelRemainder;
    }

    public function getDate_transferred(){return $this->date_transferred;}
	public function setDate_transferred($date_transferred){$this->date_transferred = $date_transferred;}

	public function getIs_transfer(){
        $this->is_transfer = (bool) $this->getDate_transferred() != null;
        return $this->is_transfer;
    }

    public function getDestination_parcel_id(){
        return $this->destination_parcel_id;
    }
    public function setDestination_parcel_id($id){
        $this->destination_parcel_id = $id;
    }

    public function getDestinationParcel(){
        if($this->destinationParcel == null && $this->destination_parcel_id != null) {
			$parcelDAO = new GenericDAO(new Parcel());
			$this->destinationParcel = $parcelDAO->getById($this->getDestination_parcel_id());
		}
        return $this->destinationParcel;
    }
    public function setDestinationParcel($parcel){
		$this->destinationParcel = $parcel;
    }

    public function getPiName(){
        if($this->piName == null){
            $p = $this->getParcel();
            if($p->getPrincipal_investigator() != null)
                $this->piName = $p->getPrincipal_investigator()->getName();
        }
		return $this->piName;
	}
	public function setPiName($piName){
		$this->piName = $piName;
	}

	public function getIsotopeName(){
        if($this->isotopeName == null){
            $p = $this->getParcel();
            if($p->getIsotope() != null)
                $this->isotopeName = $p->getIsotope()->getName();
        }
		return $this->isotopeName;
	}
	public function setIsotopeName($isotopeName){
		$this->isotopeName = $isotopeName;
	}

    public function getRsNumber(){
        if($this->rsNumber == null){
            $p = $this->getParcel();
            if($p->getRs_number() != null)
                $this->rsNumber = $p->getRs_number();
        }
		return $this->rsNumber;
	}
	public function setRsNumber($isotopeName){
		$this->rsNumber = $isotopeName;
	}

    public function getIs_mass(){
        if($this->is_mass === null){
            $p = $this->getParcel();
            if($p->getIsotope() != null)
                $this->is_mass = $p->getIsotope()->getIs_mass();

        }
        return $this->is_mass;
    }
}
?>
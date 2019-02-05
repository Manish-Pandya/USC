<?php

/**
 *
 *
 *
 * @author Perry Cate, GraySail LLC
 */
class Parcel extends RadCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "parcel";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"principal_investigator_id"		=> "integer",
        "transfer_amount_id"		    => "integer",
        "transfer_in_date"              => "timestamp",

		"purchase_order_id"				=> "integer",
		"status"						=> "text",
		"isotope_id"					=> "integer",
		"arrival_date"					=> "timestamp",
		"quantity"						=> "float",
		"rs_number"						=> "text",
		"authorization_id"				=> "integer",
        "catalog_number"                => "text",
        "chemical_compound"             => "text",
		"comments"						=> "text",

		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);

	/** Relationships */
	protected static $PARCELUSE_RELATIONSHIP = array(
		"className" => "ParcelUse",
		"tableName" => "parcel_use",
		"keyName"	=> "key_id",
		"foreignKeyName" => "parcel_id"
	);

	protected static $WIPE_TEST_RELATIONSHIP = array(
			"className" => "ParcelWipeTest",
			"tableName" => "parcel_wipe_test",
			"keyName"	=> "key_id",
			"foreignKeyName" => "parcel_id"
	);


	//access information

	/** Reference to the principal investigator this parcel belongs to.
     * @var PrincipalInvestigator
     * */

	private $principal_investigator;
	private $principal_investigator_id;

	/** Reference to the purchase order used to obtain this parcel. */
	private $purchase_order;
	private $purchase_order_id;

	/** String containing the status of this parcel. */
	private $status;

	/** Reference to the isotope this parcel contains 
     * @var Isotope
     */
	private $isotope;
	private $isotope_id;

	/** Date this parcel will arrive/arrived. */
	private $arrival_date;

	/** Float quantity of isotope in the parcel. */
	private $quantity;

	/** Float ammount of isotope that has not been used yet. */
	private $remainder;

    /** Float ammount of isotope that has not been picked up yet. */
	private $amountOnHand;

	/** Array of parcel uses that pertain to this parcel. */
	private $parcelUses;

	/* Text field human readable unique ID for orders*/
	private $rs_number;

	/* collection of IsotopeAmountDTOs that went into scint vials for this parcel */
	private $svIsotopeAmounts;

	/** wipe test done on this parcel **/
	private $wipe_tests;

	/** id of the authorization that allows PI to have this parcel **/
	private $authorization_id;

    private $catalog_number;
    private $chemical_compound;
    private $comments;

    private $hasTests;
    private $is_mass;

    /** Is this parcel a transfer? **/
    private $is_transfer;
    private $transfer_in_date;
    /** If this parcel resulted from a trasnfer from another PI, what was the id of the parcel use amount used to remove it from the other pis inventory **/
    private $transfer_amount_id;
    private $receivingPiName;

    /** If this parcel was transfered to it's current pi by another pi, what was the key_id of the original parcel? **/
    private $original_pi_id;


	public function __construct() {

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
		$entityMaps[] = new EntityMap("lazy", "getPurchase_order");
		$entityMaps[] = new EntityMap("lazy", "getIsotope");
		$entityMaps[] = new EntityMap("lazy", "getParcelUses");
		$entityMaps[] = new EntityMap("eager", "getRemainder");
		$entityMaps[] = new EntityMap("lazy", "getWipe_test");
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
	public function getPrincipal_investigator_id() { return $this->principal_investigator_id; }
	public function setPrincipal_investigator_id($newId) { $this->principal_investigator_id = $newId; }

	public function getPrincipal_investigator() {
		if($this->principal_investigator == null) {
			$principal_investigatorDAO = new GenericDAO(new PrincipalInvestigator());
			$this->principal_investigator = $principal_investigatorDAO->getById($this->getPrincipal_investigator_id());
		}
		return $this->principal_investigator;
	}
	public function setPrincipal_investigator($newPI) {
		$this->principal_investigator = $newPI;
	}

	public function getPurchase_order_id() { return $this->purchase_order_id; }
	public function setPurchase_order_id($newId) { $this->purchase_order_id = $newId; }

	public function getPurchase_order() {
		if($this->purchase_order == null) {
			$purchase_orderDAO = new GenericDAO(new PurchaseOrder());
			$this->purchase_order = $purchase_orderDAO->getById($this->getPurchase_order_id());
		}
		return $this->purchase_order;
	}
	public function setPurchase_order($newOrder) {
		$this->purchase_order = $newOrder;
	}

	public function getStatus() { return $this->status; }
	public function setStatus($newStatus) { $this->status = $newStatus; }

	public function getIsotope_id() { return $this->isotope_id; }
	public function setIsotope_id($newId) { $this->isotope_id = $newId; }

	public function getIsotope() {
		if($this->isotope == null && $this->getAuthorization_id() != null) {
            $authDao = new GenericDAO(new Authorization());
            $auth = $authDao->getById($this->authorization_id);
			$isotopeDAO = new GenericDAO(new Isotope());
			if($auth && $auth->getIsotope_id() != null)$this->isotope = $isotopeDAO->getById($auth->getIsotope_id());
		}
		return $this->isotope;
	}
	public function setIsotope($newIsotope) {
		$this->isotope = $newIsotope;
	}

	public function getArrival_date() {
        if($this->arrival_date ==  null && $this->transfer_in_date != null){
            $this->arrival_date = $this->transfer_in_date;
        }
        return $this->arrival_date;
    }
	public function setArrival_date($newDate) { $this->arrival_date = $newDate; }

	public function getQuantity() { return $this->quantity; }
	public function setQuantity($newQuantity) { $this->quantity = $newQuantity; }

	public function getParcelUses() {
		if($this->parcelUses == null && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->parcelUses = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PARCELUSE_RELATIONSHIP));
		}
		return $this->parcelUses;
	}
	public function setParcelUses($newUsesArray) {
		$this->parcelUses = $newUsesArray;
	}

	public function getRemainder() {
		if($this->remainder == null) {
			// Get total amount used from this parcel
			$uses = $this->getParcelUses();
			$usedAmount = 0;
			foreach($uses as $use) {
				if($use->getIs_active())$usedAmount += $use->getQuantity();
			}

			// subtract the amount used from the initial quantity
			$this->remainder = $this->getQuantity() - $usedAmount;
		}
		return $this->remainder;
	}

	public function getRs_number(){return $this->rs_number;}
	public function setRs_number($rs_number){$this->rs_number = $rs_number;}

	public function getSVIsotopeAmounts(){

		//get use amounts
		$svAmounts = array();
		foreach($this->getParcelUses() as $use){
			$innerAmounts = array_filter(
					$use->getParcelUseAmounts(),
					function ($e) {
						$wasteType = $e->getWaste_type();
						return $wasteType->getName() == "Vial";
					}
			);
			$svAmounts = array_merge($svAmounts, $innerAmounts);
		}

		//sum use amounts
		if($svAmounts != NULL)$this->svIsotopeAmounts = $this->sumUsages($svAmounts);
		return $this->svIsotopeAmounts;

	}

	public function getWipe_test() {
		if($this->wipe_tests == null && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->wipe_tests = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$WIPE_TEST_RELATIONSHIP));
		}
		return $this->wipe_tests;
	}

	public function setWipe_test($test){
		$this->wipe_tests = array($test);
	}

	public function getAuthorization_id() {return $this->authorization_id;}
	public function setAuthorization_id($authorization_id) {$this->authorization_id = $authorization_id;}

    public function getCatalog_number() {return $this->catalog_number;}
	public function setCatalog_number($num) {$this->catalog_number = $num;}

    public function getChemical_compound() {return $this->chemical_compound;}
	public function setChemical_compound($compound) {$this->chemical_compound = $compound;}

	public function getComments(){return $this->comments;}
	public function setComments($comments){$this->comments = $comments;}

	public function getHasTests(){
		if($this->hasTests == null){
			$this->hasTests = false;
			if($this->getWipe_test() != null){
				$this->hasTests = true;
			}
		}
		return $this->hasTests;
	}

    public function getAmountOnHand(){
        $db = DBConnection::get();
		$totalPickedUp = 0;

		//Get the total amount which has LEFT THE LAB
		//  either by Pickup or by Transfer
		$queryString = "SELECT
			ROUND(SUM(amt.curie_level),7)
			FROM parcel_use_amount amt
			JOIN parcel_use use_log
				ON (
					amt.parcel_use_id = use_log.key_id
					AND use_log.parcel_id = ?
				)

			LEFT JOIN waste_bag wb
				ON amt.waste_bag_id = wb.key_id

			LEFT JOIN carboy_use_cycle cuc
				ON amt.carboy_id = cuc.key_id

			LEFT JOIN scint_vial_collection svc
				ON amt.scint_vial_collection_id = svc.key_id

			LEFT JOIN other_waste_container owc
				ON amt.other_waste_container_id = owc.key_id

			LEFT JOIN pickup pickup
				ON wb.pickup_id = pickup.key_id
				OR cuc.pickup_id = pickup.key_id
				OR svc.pickup_id = pickup.key_id

			WHERE
				use_log.is_active = 1
				AND (
					(pickup.status != 'REQUESTED' OR owc.close_date IS NOT NULL)
					OR use_log.date_transferred IS NOT NULL
				)";

		$stmt = DBConnection::prepareStatement($queryString);
        $stmt->bindValue(1,$this->getKey_id(),PDO::PARAM_INT);
		$stmt->execute();
		while($sum = $stmt->fetchColumn()){
			$totalPickedUp = $sum;
		}

        if($totalPickedUp == 0){
            return $this->getQuantity();
        }

        $this->amountOnHand = $this->getQuantity() - $totalPickedUp;

        return $this->amountOnHand;
    }

    public function getOriginal_pi_id(){return $this->original_pi_id;}
    public function setOriginal_pi_id($id){$this->original_pi_id = $id;}

    public function getTransfer_in_date(){return $this->transfer_in_date;}
    public function setTransfer_in_date($date){$this->transfer_in_date = $date;}

    public function getReceivingPiName(){
        if($this->transfer_in_date != null && $this->authorization_id != null){
            $authDao = new GenericDAO(new Authorization());
            $auth = $authDao->getById($this->authorization_id);
            if($auth != null){
                $piAUthDao = new GenericDAO(new PIAuthorization());
                $piAuth = $piAUthDao->getById($auth->getPi_authorization_id());
                if($piAuth != null){
                    $piDao = new GenericDAO(new PrincipalInvestigator());
                    $this->receivingPiName = $piDao->getById($piAuth->getPrincipal_investigator_id())->getUser()->getName();
                }
            }
        }
        return $this->receivingPiName;
    }

    public function getTransfer_amount_id(){return $this->transfer_amount_id;}
    public function setTransfer_amount_id($id){$this->transfer_amount_id = $id;}

    public function getIs_mass(){
        if($this->is_mass === null){
            $isotope = $this->getIsotope();
            if( isset($isotope) ){
				$this->is_mass = $isotope->getIs_mass();
			}
        }
        return $this->is_mass;
    }

}
?>
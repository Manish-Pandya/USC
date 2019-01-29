<?php

/**
 *
 *
 *
 * @author Perry Cate, GraySail LLC
 */
class PurchaseOrder extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "purchase_order";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"principal_investigator_id"		=> "integer",
		"purchase_order_number"			=> "text", // The "number" may also contain letters.
		"vendor"						=> "text",	

		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer",
		"start_date"					=> "timestamp",
		"end_date"						=> "timestamp"
	);

	//access information

	/** integer containing the id of the principal investigator making this order. */
	private $principal_investigator_id;

	/** reference to the principal investigator who made this order */
	private $principal_investigator;

	/** alphanumeric string to represent identifiable purchase "number" */
	private $purchase_order_number;

	private $vendor;

	/** start date for this authorization as a timestamp*/
	private $start_date;

	/** end date for this authorization as a timestamp */
	private $end_date;

	public function __construct() {

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getPrincipal_investigator");
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
	public function getPrincipal_investigator_id() { return $this->principal_investigator_id; }
	public function setPrincipal_investigator_id($newId) { $this->principal_investigator_id = $newId; }

	public function getPrincipal_investigator() {
		if($this->principal_investigator == null) {
			$principalInvestigatorDAO = new GenericDAO(new PrincipalInvestigator());
			$this->principal_investigator = $principalInvestigatorDAO->getById($this->getPrincipal_investigator_id());
		}
		return $this->principal_investigator;
	}
	public function setPrincipal_investigator($newPI) {
		$this->principal_investigator = $newPI;
	}

	public function getPurchase_order_number() { return $this->purchase_order_number; }
	public function setPurchase_order_number($newNumber) { $this->purchase_order_number = $newNumber; }


	public function getStart_date()
	{
	    return $this->start_date;
	}

	public function setStart_date($start_date)
	{
	    $this->start_date = $start_date;
	}

	public function getEnd_date(){return $this->end_date;}
	public function setEnd_date($end_date){$this->end_date = $end_date;}
	
	public function getVendor(){return $this->vendor;}
	public function setVendor($vendor){$this->vendor = $vendor;}
	
}
?>
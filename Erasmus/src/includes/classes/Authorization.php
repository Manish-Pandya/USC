<?php

include_once 'GenericCrud.php';

/**
 * 
 * 
 * 
 * @author Perry Cate, GraySail LLC
 */
class Authorization extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "authorization";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"principal_investigator_id"		=> "integer",
		"isotope_id"					=> "integer",
		"max_quantity"					=> "float",
		"approval_date"					=> "timestamp",
		"revocation_date"				=> "timestamp",
		
		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);
	
	//access information

	/** Reference to the Isotope entity that this authorization contains */
	private $isotope_id;
	private $isotope;
	
	/** id of principal_investigator this authorization is about */
	private $principal_investigator_id;
	// Note to self:
	// ommited a place to store the reference to the principal investigator itself
	// because (according to current specs at least) a principal investigator is 
	// gotten first, and THEN authorization, not the other way around.
	
	/** maximum curie concentration that can be used */
	private $max_quantity;
	
	/** timestamp containing the date this authorization was... Authorized. */
	private $approval_date;
	
	/** timestamp containing the date this aurhtoization will expire */
	private $revocation_date;
	
	
	public function __construct() {
		
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getIsotope");
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
	public function getIsotope_id() { return $this->isotope_id; }
	public function setIsotope_id($newId) { $this->isotope_id = $newId; }
	
	public function getIsotope() {
		if($this->isotope == null) {
			$isotopeDAO = new GenericDAO(new Isotope());
			$this->isotope = $isotopeDAO->getById($this->getIsotope_id());
		}
		return $this->isotope;
	}
	public function setIsotope($newIsotope) {
		$this->isotope = $newIsotope;
	}
	
	public function getPrincipal_investigator_id() { return $this->principal_investigator_id; }
	public function setPrincipal_investigator_id($newId) { $this->principal_investigator_id = $newId; }
	
	public function getMax_quantity() { return $this->max_quantity; }
	public function setMax_quantity($newQuantity) { $this->max_quantity = $newQuantity; }
	
	public function getApproval_date() { return $this->approval_date; }
	public function setApproval_date($newDate) { $this->approval_date = $newDate; }
	
	public function getRevocation_date() { return $this->revocation_date; }
	public function setRevocation_date($newDate) { $this->revocation_date = $newDate; }

}
?>
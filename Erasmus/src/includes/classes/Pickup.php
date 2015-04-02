<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author Perry Cate, GraySail LLC
 */
class Pickup extends RadCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "pickup";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"pickup_date"					=> "timestamp",
		"requested_date"				=> "timestamp",
		"pickup_user_id"				=> "integer",
		"principal_investigator_id"		=> "integer",
		"status"						=> "text",
		"notes"							=> "text",
		"scint_vial_trays"				=> "text",

		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);

	/** Relationships */
	public static $CARBOYS_RELATIONSHIP = array(
		"className" => "CarboyUseCycle",
		"tableName" => "carboy_use_cycle",
		"keyName"   => "key_id",
		"foreignKeyName" => "pickup_id"
	);

	public static $WASTEBAGS_RELATIONSHIP = array(
		"className" => "WasteBag",
		"tableName" => "waste_bag",
		"keyName"   => "key_id",
		"foreignKeyName" => "pickup_id"
	);
	
	public static $SCINT_VIAL_COLLECTIONS_RELATIONSHIP = array(
		"className" => "ScintVialCollection",
		"tableName" => "scint_vial_collection",
		"keyName"   => "key_id",
		"foreignKeyName" => "pickup_id"
	);

	//access information

	/** Date (timestamp) that this pickup occurred. */
	private $pickup_date;

	/** Date (timestamp) that this pickup was requested by PI or labPersonnel. */
	private $requested_date;


	/** Integer id of the user who picked up the materials. */
	private $pickup_user_id;

	/** Array of Carboys picked up */
	private $carboy_use_cycles;

	/** Array of Waste Bags picked up */
	private $waste_bags;
	
	/** Array of Scint Vial Collections picked up **/
	private $scint_vial_collections;

	/** Key_id of the PI who scheduled this pickup */
	private $principal_investigator_id;

	/** PI who scheduled this pickup */
	private $principalInvestigator;
	
	/** the current status of this pickup, indicated whether it's been Requested, Picked Up, etc. **/
	private $status;
	
	/**  notes about this pickup added by lab personnel **/
	private $notes;
	
	/** number of scint vial trays picked up **/
	private $scint_vial_trays;

	public function __construct() {

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager", "getCarboy_use_cycles");
		$entityMaps[] = new EntityMap("eager", "getWaste_bags");
		$entityMaps[] = new EntityMap("eager", "getScint_vial_collections");
		$entityMaps[] = new EntityMap("lazy", "getPrincipalInvestigator");
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
	public function getPickup_date() { return $this->pickup_date; }
	public function setPickup_date($newDate) { $this->pickup_date = $newDate; }

	public function getPickup_user_id() { return $this->pickup_user_id; }
	public function setPickup_user_id($newId) { $this->pickup_user_id = $newId; }

	public function getCarboy_use_cycles() {
		if($this->carboy_use_cycles === null && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->carboy_use_cycles = $thisDao->getRelatedItemsById(
					$this->getKey_id(), DataRelationship::fromArray(self::$CARBOYS_RELATIONSHIP));
		}
		$LOG = Logger::getLogger(__CLASS__);
		return $this->carboy_use_cycles;
	}
	public function setCarboy_use_cycles($newCarboys) {$this->carboy_use_cycles = $newCarboys;}

	public function getWaste_bags() {
		if($this->waste_bags === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->waste_bags = $thisDao->getRelatedItemsById(
				$this->getKey_id(), DataRelationship::fromArray(self::$WASTEBAGS_RELATIONSHIP));
		}
		return $this->waste_bags;
	}
	public function setWaste_bags($newBags) {$this->waste_bags = $newBags;}
	
	public function getScint_vial_collections(){
	
		if($this->scint_vial_collections === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			// Note: By default GenericDAO will only return active parcels, which is good - the client probably
			// doesn't care about parcels that have already been completely used up. A getAllParcels method can be
			// added later if necessary.
			$this->scint_vial_collections = $thisDao->getRelatedItemsById(
					$this->getKey_id(),
					DataRelationship::fromArray(self::$SCINT_VIAL_COLLECTIONS_RELATIONSHIP)
			);
		}
		$LOG = Logger::getLogger(__CLASS__);
		$LOG->debug('calling pickup get sv collections');
		return $this->scint_vial_collections;
	}
	
	public function setScint_vial_collections($collections){
		$this->scint_vial_collections = $collections;
	}

	public function getPrincipal_investigator_id(){return $this->principal_investigator_id;}
	public function setPrincipal_investigator_id($principal_investigator_id){$this->principal_investigator_id = $principal_investigator_id;}

	public function getPrincipalInvestigator(){
		$piDAO = new GenericDAO(new PrincipalInvestigator());
		$this->principalInvestigator = $piDAO->getById($this->principal_investigator_id);
		return $this->principalInvestigator;
	}
	
	public function setPrincipalInvestigator($principalInvestigator){$this->principalInvestigator = $principalInvestigator;}

	public function getRequested_date() {return $this->requested_date;}
	public function setRequested_date($requested_date) {$this->requested_date = $requested_date;}
	
	public function getStatus() { return $this->status;	}
	public function setStatus($status) { $this->status = $status; }
	
	public function getNotes(){ return $this->notes; }
	public function setNotes($notes){$this->notes = $notes;}
	
	public function getScint_vial_trays(){ return $this->scint_vial_trays; }
	public function setScint_vial_trays($trays){ $this->scint_vial_trays = $trays; }
	
}
?>
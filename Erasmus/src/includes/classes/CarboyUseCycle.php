<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author Perry Cate, GraySail LLC
 */
class CarboyUseCycle extends RadCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "carboy_use_cycle";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"carboy_id"						=> "integer",
		"curie_level"					=> "float",
		"principal_investigator_id"		=> "integer",
		"status"						=> "text",
		"lab_date"						=> "timestamp",
		"hotroom_date"					=> "timestamp",
		"pour_date"						=> "timestamp",
		"room_id"						=> "integer",
		"pickup_id"						=> "integer",
		"reading"						=> "float",


		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);

	//access information

	/** Reference to the carboy this use cycle refers to. */
	private $carboy;
	private $carboy_id;

	/** Float ammount in curies of the radiation this carboy contains. */
	private $curie_level;

	/** Reference to the principal investigator this carboy belongs to. */
	private $principal_investigator;
	private $principal_investigator_id;

	/** String describing the current status of this carboy. */
	private $status;

	/** timestamp containing the date this carboy was sent to a lab. */
	private $lab_date;

	/** timestamp containing the date this carboy was sent to the hotroom. */
	private $hotroom_date;

	/** timestamp containing the date this carboy was emptied. */
	private $pour_date;
	
	/** date this carboy can be poured **/
	private $pour_allowed_date;
	
	/** Reference to the room this carboy was sent to. */
	private $room;
	private $room_id;

	/** Reference to the pickup that removed this carboy from the lab. */
	private $pickup;
	private $pickup_id;
	
	/* parcel use amounts currently in the carboy */
	private $parcel_use_amounts;
	
	/* currie level of each isotope in this carboy **/
	private $contents;
	
	/** reading taken when carboy is returned to RSO after pickup*/
	private $reading;
		

	public function __construct() {

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getCarboy");
		$entityMaps[] = new EntityMap("lazy", "getPrincipalInvestigator");
		$entityMaps[] = new EntityMap("eager", "getRoom");
		$entityMaps[] = new EntityMap("lazy", "getPickup");
		$this->setEntityMaps($entityMaps);

	}

	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}

	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	/** Relationships */
	protected static $USEAMOUNTS_RELATIONSHIP = array(
			"className" => "ParcelUseAmount",
			"tableName" => "parcel_use_amount",
			"keyName"	=> "key_id",
			"foreignKeyName"	=> "carboy_id"
	);
	
	// Accessors / Mutators
	public function getCarboy() {
		if($this->carboy == null) {
			$carboyDAO = new GenericDAO(new Carboy());
			$this->carboy = $carboyDAO->getById($this->getCarboy_id());
		}
		return $this->carboy;
	}
	public function setCarboy($newCarboy) {
		$this->carboy = $newCarboy;
	}

	public function getCarboy_id() { return $this->carboy_id; }
	public function setCarboy_id($newId) { $this->carboy_id = $newId; }

	public function getCurie_level() { return $this->curie_level; }
	public function setCurie_level($newLevel) { $this->curie_level = $newLevel; }

	public function getPrincipal_investigator() {
		if($this->principal_investigator == null) {
			$piDAO = new GenericDAO(new PrincipalInvestigator());
			$this->principal_investigator = $piDAO->getById($this->getPrincipal_investigator_id());
		}
		return $this->principal_investigator;
	}
	public function setPrincipal_investigator($newPi) {
		$this->principal_investigator = $newPi;
	}

	public function getPrincipal_investigator_id() { return $this->principal_investigator_id; }
	public function setPrincipal_investigator_id($newId) { $this->principal_investigator_id = $newId; }

	public function getStatus() { return $this->status; }
	public function setStatus($newStatus) { $this->status = $newStatus; }

	public function getLab_date() { return $this->lab_date; }
	public function setLab_date($newDate) { $this->lab_date = $newDate; }

	public function getHotroom_date() { return $this->hotroom_date; }
	public function setHotroom_date($newDate) { $this->hotroom_date = $newDate; }

	public function getPour_date() { return $this->pour_date; }
	public function setPour_date($newDate) { $this->pour_date = $newDate; }

	public function getRoom() {
		if($this->room == null) {
			$roomDAO = new GenericDAO(new Room());
			$this->room = $roomDAO->getById($this->getRoom_id());
		}
		return $this->room;
	}
	public function setRoom($newRoom) {
		$this->room = $newRoom;
	}

	public function getRoom_id() { return $this->room_id; }
	public function setRoom_id($newId) { $this->room_id = $newId; }

	public function getPickup() {
		if($this->pickup == null) {
			$pickupDAO = new GenericDAO(new Pickup());
			$this->pickup = $pickupDAO->getById($this->getPickup_id());
		}
		return $this->pickup;
	}
	public function setPickup($newPickup) {
		$this->pickup = $newPickup;
	}

	public function getPickup_id() { return $this->pickup_id; }
	public function setPickup_id($newId) { $this->pickup_id = $newId; }

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
	
	public function getReading(){return $this->reading;}
	public function setReading($reading){$this->reading = $reading;}
	
	
	public function getContents(){
		$this->contents = $this->sumUsages($this->getParcelUseAmounts());
		return $this->contents;
	}
	
	public function getPour_allowed_date(){
		$LOG = Logger::getLogger(__CLASS__);
		$this->pour_allowed_date = null;
		$contents = $this->getContents();
		$hotroomDate = $this->getHotroom_date();
		$reading = $this->getReading();
		
		$longestHalfLife = 0;
		$totalMCi = 0;
		foreach($contents as $content){
			$isotopDao = new GenericDAO(new Isotope());
			$isotope = $isotopDao->getById($content->getIsotope_id());	
			//find the isotope with the longest half life
			if($isotope->getHalf_life() > $longestHalfLife){
				$releventIsotope = $isotope;
				$longestHalfLife = $isotope->getHalf_life();
				$totalMCi = $totalMCi + $content->getCurie_level();
			}
		}

		if($releventIsotope != null){
			$daysToDecay = $this->getDecayTime($releventIsotope->getHalf_life(), $totalMCi);
			$LOG->debug($daysToDecay);
			
			$now = new DateTime();
			//the front end parses timestamps nicely, so we return this as one
			$this->pour_allowed_date  = date("Y-m-d H:i:s" , $now->add(new DateInterval('P'.$daysToDecay.'D'))->getTimestamp());
			$LOG->debug($this);
		}
		return $this->pour_allowed_date;
		
		
	}
	
	//get the time to decay to .01 mCi in days (or whatever unit we are storing half-lives in)
	private function getDecayTime($halfLife, $mCi){
		$LOG = Logger::getLogger(__CLASS__);
		$targetMCI = .01;
		//the time in days to decay.  we always round up to make sure that the carboy is fully decayed.
		return ceil(($halfLife/-0.693147) * log($targetMCI/$mCi));
	}
	
}
?>
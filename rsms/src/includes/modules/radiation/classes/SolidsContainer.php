<?php

/**
 *
 *
 *
 * @author Perry Cate, GraySail LLC
 */
class SolidsContainer extends RadCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "solids_container";

	/** Key/value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"principal_investigator_id"		=> "integer",
		"room_id"						=> "integer",
		"name"							=> "text",
        "pickup_id"                     => "integer",

		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer",
	);

	protected static $WASTEBAG_RELATIONSHIP = array(
		"className" => "WasteBag",
		"tableName" => "waste_bag",
		"keyName"	=> "key_id",
		"foreignKeyName" => "container_id"
	);

	//access information

	/** integer key id of the principal investigator this container belongs to. */
	private $principal_investigator_id;
	private $principal_investigator;

	/** integer key id of the room this container is in */
	private $room_id;
	private $room;

	/** array of all waste bags that have been in this container */
	private $waste_bags;

	/** array of the waste bags currently in this container */
	private $current_waste_bags;
	
	private $waste_bags_for_pickup;

	private $name;

	public function __construct() {
        if($this->hasPrimaryKeyValue() && $this->getWasteBags() == null){
            $bag = new WasteBag();
            $bag->setContainer_id($this->key_id);
            $bag->setDate_added(date('Y-m-d H:i:s'));
            $bagDao = new GenericDao($bag);
            $bag = $bagDao->save($bag);
        }
    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getPrincipal_investigator");
		$entityMaps[] = EntityMap::lazy("getRoom");
		$entityMaps[] = EntityMap::lazy("getWasteBags");
		$entityMaps[] = EntityMap::eager("getCurrentWasteBags");
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
		if($this->principal_investigator === null && $this->hasPrimaryKeyValue()) {
			$piDao = new GenericDAO(new PrincipalInvestigator());
			$this->principal_investigator = $piDao->getById($this->getKey_id());
		}
		return $this->principal_investigator;
	}
	public function setPrincipal_investigator($newPI) {
		$this->principal_investigator = $newPI;
	}

	public function getRoom_id() { return $this->room_id; }
	public function setRoom_id($newId) { $this->room_id = $newId; }

	public function getRoom() {
		if($this->room === null && $this->hasPrimaryKeyValue()) {
			$roomDao = new RoomDAO();
			$this->room = $roomDao->getById($this->getKey_id());
		}
		return $this->room;
	}
	public function setRoom($newRoom) {
		$this->room = $newRoom;
	}

	/** CAUTION:
	 *
	 * This method will return all WasteBags that ever existed in this container!
	 * To get just the bags currently existing in this container, call
	 * getCurrentWasteBags instead.
	 */
	public function getWasteBags() {
		if($this->waste_bags === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->waste_bags = $thisDao->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$WASTEBAG_RELATIONSHIP));
		}
		return $this->waste_bags;
	}
	public function setWasteBags($newBags) {
		$this->waste_bags = $newBags;
	}

	public function getCurrentWasteBags() {
		// get all waste bags
		$wasteBags = $this->getWasteBags();
		// only select bags that have not been entered in drum
		$currentBags = array();
		foreach($wasteBags as $bag) {
			$removed = $bag->getDate_removed();
			if($removed == NULL || $removed == '0000-00-00 00:00:00') {
				$currentBags[] = $bag;
			}
		}
		return $currentBags;
	}

	public function getWasteBagsForPickup() {
		$LOG = Logger::getLogger(__CLASS__);
		// get all waste bags
		$wasteBags = $this->getWasteBags();
		// only select bags that have not been entered in drum
		$availableBags = array();
		foreach($wasteBags as $bag) {				
			$removed = $bag->getDate_removed();
			if($removed != NULL && $removed != '0000-00-00 00:00:00' && $bag->getPickup_id() == null) {
				//this bag hasn't been assigned to a pickup, so it is available
				$availableBags[] = $bag;
			}
		}
		$this->waste_bags_for_pickup = $availableBags;
		return $this->waste_bags_for_pickup;
	}

	public function getName()
	{
	    return $this->name;
	}

	public function setName($name)
	{
	    $this->name = $name;
	}
}
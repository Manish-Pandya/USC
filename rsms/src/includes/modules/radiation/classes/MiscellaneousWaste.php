<?php

/**
 *
 *
 *
 * @author GrayBeard Entity Generator
 * @author Matt Breeden
 */
class MiscellaneousWaste extends RadCrud {

    /** Name of the DB Table */
    protected static $TABLE_NAME = "miscellaneous_waste";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
			"drum_id"						=> "integer",
            "comments"                      => "text",
            "type"                          => "text",
            "equipment_id"                  => "integer",

			//GenericCrud
			"key_id"						=> "integer",
			"is_active"						=> "boolean",
			"date_last_modified"			=> "timestamp",
			"last_modified_user_id"			=> "integer",
			"date_created"					=> "timestamp",
			"created_user_id"				=> "integer"
	);

	public function __construct() {
		$LOG = Logger::getLogger(__CLASS__);
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getParcel_use_amounts");
		$entityMaps[] = new EntityMap("lazy", "getPickup");
		$entityMaps[] = new EntityMap("lazy", "getDrum");
		$entityMaps[] = new EntityMap("eager", "getContents");

		$this->setEntityMaps($entityMaps);
	}

	/** Relationships */
	protected static $USEAMOUNTS_RELATIONSHIP = array(
			"className" => "ParcelUseAmount",
			"tableName" => "parcel_use_amount",
			"keyName"	=> "key_id",
			"foreignKeyName"	=> "miscellaneous_waste_id"
	);

	private $parcel_use_amounts;
	private $contents;

	private $drum_id;
	private $drum;

    private $comments;

    private $equipment_id;

	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}

	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}


	public function getParcel_use_amounts() {
		$LOG = Logger::getLogger(__CLASS__);
		$LOG->debug("getting parcel use amounts misc");

		if($this->parcel_use_amounts === NULL && $this->hasPrimaryKeyValue()) {
			$thisDao = new GenericDAO($this);
			$this->parcel_use_amounts = $thisDao->getRelatedItemsById($this->getKey_id(),DataRelationship::fromArray(self::$USEAMOUNTS_RELATIONSHIP));
		}
		return $this->parcel_use_amounts;
	}
	public function setParcel_use_amounts($parcel_use_amounts) {
		$this->parcel_use_amounts = $parcel_use_amounts;
	}

	public function getContents(){
		$LOG = Logger::getLogger(__CLASS__);
		$LOG->debug('getting contents for sv collection.');
		$this->contents = $this->sumUsages($this->getParcel_use_amounts());
		return $this->contents;
	}
	public function setContents($contents) {
		$this->contents = $contents;
	}

	public function getDrum_id(){return $this->drum_id;}
	public function setDrum_id($id){$this->drum_id = $id;}

	public function getDrum(){
		if($this->drum == NULL){
			$drumDao = new GenericDAO(new Drum());
			$this->drum = $drumDao->getById($this->drum_id);
		}
		return $this->drum;
	}

    public function getComments(){return $this->comments;}
    public function setComments($comments){$this->comments = $comments;}

    public function getType(){return $this->type;}
    public function setType($type){$this->type = $type;}

    public function getEquipment_id(){return $this->equipment_id;}
    public function setEquipment_id($id){$this->equipment_id = $id;}

}
?>
<?php

include_once '../GenericCrud.php';
include_once '../Room.php';

/**
 *
 *
 *
 * @author David Hamiter
 */
class Autoclave extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "autoclave";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"		            => "text",
        "type"		            => "text",
        "serialNum"		        => "text",
        "room_id"		        => "text",
        "contractStatus"	    => "text",
        "vendorContact"		    => "text",
        "comments"		        => "text",

		//GenericCrud
		"key_id"			    => "integer",
		"date_created"		    => "timestamp",
		"date_last_modified"    => "timestamp",
		"is_active"			    => "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"	    => "integer"
	);


	protected static $ROOM_RELATIONSHIP = array(
			"className"	        => "Room",
			"tableName"	        => "room_equipment",
			"keyName"	        => "key_id",
			"foreignKeyName"    => "autoclave_id"
	);


	private $name;
    
    private $type;
    
    private $serialNum;
    
    private contractStatus;
    
    private vendorContact;
    
    private comments;

	private $room;

	public function __construct(){
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getRoom");
		$this->setEntityMaps($entityMaps);

	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors / Mutators
	public function getName(){ return $this->name; }
	public function setName($value){ $this->name = $value; }
    
    public function getType(){ return $this->type; }
	public function setType($value){ $this->type = $value; }
    
    public function getSerialNum(){ return $this->serialNum; }
	public function setSerialNum($value){ $this->serialNum = $value; }
    
    public function getContractStatus(){ return $this->contractStatus; }
	public function setContractStatus($value){ $this->contractStatus = $value; }
    
    public function getVendorContact(){ return $this->vendorContact; }
	public function setVendorContact($value){ $this->vendorContact = $value; }
    
    public function getComments(){ return $this->comments; }
	public function setComments($value){ $this->comments = $value; }

	public function getRoom(){
		if($this->buildings == null) {
			$thisDAO = new GenericDAO($this);
			$this->room = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$ROOM_RELATIONSHIP));
		}
		return $this->room;
	}
	public function setRoom($value){
		$this->room = $value;
	}


}
?>
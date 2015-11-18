<?php

include_once '../GenericCrud.php';
include_once '../Room.php';

/**
 *
 *
 *
 * @author David Hamiter
 */
class XRay extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "xray";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"		            => "text",
        "type"		            => "text",
        "serialNum"		        => "text",
        "room_id"		        => "text",
        "make"          	    => "text",
        "model"     		    => "text",
        "frequency"		        => "text",
        "pi"    		        => "text",

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
			"foreignKeyName"    => "bioSafetyCabinet_id"
	);
    
    protected static $PI_RELATIONSHIP = array(
			"className"	        => "PrincipalInvestigator",
			"tableName"	        => "principal_investigator_equipment",
			"keyName"	        => "key_id",
			"foreignKeyName"    => "bioSafetyCabinet_id"
	);


	private $name;
    
    private $type;
    
    private $serialNum;
    
    private $make;
    
    private $model;
    
    private $frequency;

	private $room;
    
    private $pi;

	public function __construct(){
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getRoom");
        $entityMaps[] = new EntityMap("lazy","getPI");
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
	public function getName(){ return $this->$value; }
	public function setName($value){ $this->name = $value; }
    
    public function getType(){ return $this->type; }
	public function setType($value){ $this->type = $value; }
    
    public function getSerialNum(){ return $this->serialNum; }
	public function setSerialNum($value){ $this->serialNum = $value; }
    
    public function getMake(){ return $this->make; }
	public function setMake($value){ $this->make = $value; }
    
    public function getModel(){ return $this->model; }
	public function setModel($value){ $this->model = $value; }
    
    public function getFrequency(){ return $this->frequency; }
	public function setFrequency($value){ $this->frequency = $value; }

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
    
    public function getPI(){
		if($this->buildings == null) {
			$thisDAO = new GenericDAO($this);
			$this->pi = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$PI_RELATIONSHIP));
		}
		return $this->pi;
	}
	public function setPI($value){
		$this->pi = $value;
	}

}
?>
<?php

include_once '../GenericCrud.php';

/**
 *
 *
 *
 * @author David Hamiter
 */
class Laser extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "laser";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
        "type"		                => "text",
        "serial_number"		        => "text",
        "room_id"		            => "integer",
        "make"          	        => "text",
        "model"     		        => "text",
        "frequency"		            => "text",
        "principal_investigator_id" => "integer",

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


    private $type;
    
    private $serial_number;
    
    private $make;
    
    private $model;
    
    private $frequency;

	private $room;
    
    private $room_id;
    
    private $principal_investigator;
    
    private $principal_investigator_id;
    

	public function __construct(){
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getRoom");
        $entityMaps[] = new EntityMap("lazy","getPrincipal_investigator");
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
    public function getType(){ return $this->type; }
	public function setType($value){ $this->type = $value; }
    
    public function getSerial_number(){ return $this->serial_number; }
	public function setSerial_number($value){ $this->serial_number = $value; }
    
    public function getMake(){ return $this->make; }
	public function setMake($value){ $this->make = $value; }
    
    public function getModel(){ return $this->model; }
	public function setModel($value){ $this->model = $value; }
    
    public function getFrequency(){ return $this->frequency; }
	public function setFrequency($value){ $this->frequency = $value; }
    
    public function getRoom_id(){ return $this->room_id; }
	public function setRoom_id($value){ $this->room_id = $value; }

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
    
    public function getPrincipal_investigator_id(){
		return $this->principal_investigator_id;
	}
	public function setPrincipal_investigator_id($value){
		$this->principal_investigator_id = $value;
	}
    
    public function getPrincipal_investigator(){
		if($this->buildings == null) {
			$thisDAO = new GenericDAO($this);
			$this->principal_investigator = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$PI_RELATIONSHIP));
		}
		return $this->principal_investigator;
	}
	public function setPrincipal_investigator($value){
		$this->principal_investigator = $value;
	}

}
?>
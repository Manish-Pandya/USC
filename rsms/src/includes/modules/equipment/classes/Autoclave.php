<?php

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
        "type"		            		=> "text",
        "serial_number"		        	=> "text",
        "room_id"		        		=> "integer",
		"contract_status"				=> "text",
		"vendor_contact"				=> "text",
		"comments"						=> "text",
				
		//GenericCrud
		"key_id"			    => "integer",
		"date_created"		    => "timestamp",
		"date_last_modified"    => "timestamp",
		"is_active"			    => "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"	    => "integer"
	);

    private $type;  
    private $serial_number; 
    private $room_id;
    private $room;
    private $contract_status;
    private $vendor_contact;
    private $comments;
    
    
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
	public function getType(){
		return $this->type;
	}
	public function setType($type){
		$this->type = $type;
	}

	public function getSerial_number(){
		return $this->serial_number;
	}
	public function setSerial_number($serial_number){
		$this->serial_number = $serial_number;
	}

	public function getRoom_id(){
		return $this->room_id;
	}
	public function setRoom_id($room_id){
		$this->room_id = $room_id;
	}

	public function getRoom(){
		return $this->room;
	}
	public function setRoom($room){
		$this->room = $room;
	}

	public function getContract_status(){
		return $this->contract_status;
	}
	public function setContract_status($contract_status){
		$this->contract_status = $contract_status;
	}

	public function getVendor_contact(){
		return $this->vendor_contact;
	}
	public function setVendor_contact($vendor_contact){
		$this->vendor_contact = $vendor_contact;
	}

	public function getComments(){
		return $this->comments;
	}
	public function setComments($comments){
		$this->comments = $comments;
	}

}
?>
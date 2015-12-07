<?php

include_once '../GenericCrud.php';

/**
 *
 *
 *
 * @author David Hamiter
 */
class BioSafetyCabinet extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "biosafety_cabinet";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
        "type"		            		=> "text",
        "serial_number"		        	=> "text",
        "room_id"		        		=> "integer",
        "make"          	   			=> "text",
        "model"     		    		=> "text",
        "frequency"		        		=> "text",
        "principal_investigator_id"    	=> "integer",
		"certification_date"			=> "date",
		"due_date"						=> "date",
		"report_path"					=> "text",
				
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
    private $make;
    private $model;  
    private $frequency;
    private $room_id;
	private $room;
	private $principal_investigator_id;
	private $principal_investigator;
	private $certification_date;
	private $due_date;
	private $report_path;

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

	public function getMake(){
		return $this->make;
	}
	public function setMake($make){
		$this->make = $make;
	}

	public function getModel(){
		return $this->model;
	}
	public function setModel($model){
		$this->model = $model;
	}

	public function getFrequency(){
		return $this->frequency;
	}
	public function setFrequency($frequency){
		$this->frequency = $frequency;
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

	public function getPrincipal_investigator_id(){
		return $this->principal_investigator_id;
	}
	public function setPrincipal_investigator_id($principal_investigator_id){
		$this->principal_investigator_id = $principal_investigator_id;
	}

	public function getPrincipal_investigator(){
		return $this->principal_investigator;
	}
	public function setPrincipal_investigator($principal_investigator){
		$this->principal_investigator = $principal_investigator;
	}

	public function getCertification_date(){
		return $this->certification_date;
	}
	public function setCertification_date($certification_date){
		$this->certification_date = $certification_date;
	}

	public function getDue_date(){
		return $this->due_date;
	}
	public function setDue_date($due_date){
		$this->due_date = $due_date;
	}

	public function getReport_path(){
		return $this->report_path;
	}
	public function setReport_path($report_path){
		$this->report_path = $report_path;
	}

}
?>
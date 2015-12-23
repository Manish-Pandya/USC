<?php
/**
 *
 *
 *
 * @author David Hamiter
 */
class Equipment extends GenericCrud{

	/** Name of the DB Table */
	protected static $TABLE_NAME = "equipment";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
        "type"		            		=> "text",
        "serial_number"		        	=> "text",
        "make"          	   			=> "text",
        "model"     		    		=> "text",
        "frequency"		        		=> "text",
        "equipment_class"               => "text",
				
		//GenericCrud
		"key_id"			    => "integer",
		"date_created"		    => "timestamp",
		"date_last_modified"    => "timestamp",
		"is_active"			    => "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"	    => "integer"
	);
    
    private $type;
    private $make;
    private $model;
    private $frequency;
    private $equipment_class;
    private $serial_number;

    public function getType(){
		return $this->type;
	}
	public function setType($type){
		$this->type = $type;
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

	public function getEquipment_class(){
		return $this->equipment_class;
	}
	public function setEquipment_class($equipment_class){
		$this->equipment_class = $equipment_class;
    } 
    
    public function getSerial_number(){
		return $this->serial_number;
	}
	public function setSerial_number($serial_number){
		$this->serial_number = $serial_number;
    }
}
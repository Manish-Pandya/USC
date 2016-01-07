<?php
/**
 *
 *
 *
 * @author David Hamiter
 */
include '../GenericCrud.php';

abstract class Equipment extends GenericCrud{

    private $type;
    private $make;
    private $model;
    private $frequency;
    private $equipment_class;
    private $serial_number;
    
    public $gotEquipment = true;
    
	public function __construct(){
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->fatal('equipment exists');
		// Define which subentities to load
		$entityMaps = array();
		$this->setEntityMaps($entityMaps);

	}
    
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
<?php
/**
 *
 *
 *
 * @author David Hamiter
 */
include '../GenericCrud.php';

abstract class Equipment extends GenericCrud{

    protected $type;
    protected $make;
    protected $model;
    protected $frequency;
    protected $serial_number;
    protected $equipmentInspections; //array
    
    /** Relationships */
	protected static $INSPECTIONS_RELATIONSHIP = array(
		"className"	=>	"Inspection",
		"tableName"	=>	"inspection",
		"keyName"	=>	"key_id",
		"foreignKeyName" =>	"equipment_id"
	);
    
    
	public function __construct(){
		// Define which subentities to load
		$entityMaps = array();
        $entityMaps[] = new EntityMap("lazy","getEquipmentInspections");
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

    public function getSerial_number(){
		return $this->serial_number;
	}
	public function setSerial_number($serial_number){
		$this->serial_number = $serial_number;
    }
    
    public function getEquipmentInspections(){
        if($this->equipmentInspections === NULL){
            $thisDAO = new GenericDAO( new EquipmentInspection() );
            // TODO: this would be a swell place to sort
            $whereClauseGroup = new WhereClauseGroup(
                array(
                    new WhereClause("equipment_class", "=", get_class($this)),
                    new WhereClause("equipment_id", "=" , $this->getKey_id())
                )
            );
            $this->equipmentInspections = $thisDAO->getAllWhere($whereClauseGroup);
        }
        
		return $this->equipment_inspections;
	}
	public function setEquipmentInspections($inspections){ $this->equipment_inspections = $inspections; }
    
    public function conditionallyCreateEquipmentInspection(){
        if ($this->hasPrimaryKeyValue()) {
            if ($this->frequency != null) {
                $inspection = new EquipmentInspection(get_class($this), $this->frequency, $this->getKey_id());
                $inspectionDao = new GenericDao($inspection);
                $this->equipmentInspections = array( $inspectionDao->save($inspection) );
            }
        }
    }
    
}
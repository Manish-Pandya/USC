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
    protected $equipmentInspections; //array\
    protected $principaInvestigatorId;
    protected $roomId;
    protected $comments;
	private $certification_date;
	private $due_date;
    private $equipment_class;


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

    public function getPrincipalInvestigatorId(){return $this->principalInvestigatorId;}
    public function setPrincipalInvestigatorId($id){$this->principalInvestigatorId = $id;}

    public function getRoomId(){return $this->roomId;}
    public function setRoomId($id){$this->roomId = $id;}

    public function getEquipmentInspections(){
        $l = Logger::getLogger('getEquipmentInspections?');

        if($this->equipmentInspections == null){
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
		return $this->equipmentInspections;
	}
	public function setEquipmentInspections($inspections){ $this->equipmentInspections = $inspections; }

    public function conditionallyCreateEquipmentInspection(){
        $l = Logger::getLogger('conditionallyCreateEquipmentInspection?');
		$l->fatal($this);
        if ($this->hasPrimaryKeyValue() && $this->getEquipmentInspections() == null) {
            if ($this->frequency != null) {
                $inspection = new EquipmentInspection(get_class($this), $this->frequency, $this->getKey_id(), $this->getCertification_date());
                if($this->getPrincipalInvestigatorId() != null) $inspection->setPrincipal_investigator_id($this->getPrincipalInvestigatorId());
                if($this->getRoomId() != null) $inspection->setRoom_id($this->getRoomId());
                if($this->getCertification_date() != null) $inspection->setCertification_date($this->getCertification_date());

                $inspectionDao = new GenericDao($inspection);
                $inspection = $inspectionDao->save($inspection);
                return $inspection;
                //$this->equipmentInspections = array( $inspection );
            }
        }else{
            return null;
        }
    }

    public function getCertification_date(){
		return $this->certification_date;
	}
	public function setCertification_date($certification_date){
		$this->certification_date = $certification_date;
        // new instance will be spawned in the controller
	}

	public function getDue_date(){
		return $this->due_date;
	}
	public function setDue_date($dueDate){
		$this->due_date = $dueDate;
        // new instance will be spawned in the controller
	}

    public function getComments(){
		return $this->comments;
	}
	public function setComments($comments){
		$this->comments = $comments;
	}

}
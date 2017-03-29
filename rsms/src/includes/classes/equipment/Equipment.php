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
        //$this->conditionallyCreateInspectionForCurrentYear();

		// Define which subentities to load
		$entityMaps = array();
        $entityMaps[] = new EntityMap("eager","getEquipmentInspections");
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

    /**
     * @return EquipmentInspection $inspection;
     **/
    public function conditionallyCreateEquipmentInspection($selectedInspection = null){
        $l = Logger::getLogger('conditionallyCreateEquipmentInspection?');
        $l->fatal("here we are now");
        if($this->frequency == null)$this->frequency = "Annually";
        //We only create a new inspection for Cabinets that have not yet been saved
        if ($this->frequency != null || ($selectedInspection != null && $selectedInspection->getFrequency() != null)) {
			if ($this->getEquipmentInspections() == null && $selectedInspection == null) {
				$inspection = new EquipmentInspection(get_class($this), $this->frequency, $this->getKey_id(), $this->getCertification_date());
            } else {
                if($selectedInspection == null){
                    $l->fatal("selected was null");
                    $inspection = $this->grabMostRecentInspection();
                }else{
                    $l->fatal("selected was not null");
                    $inspection  = $selectedInspection;
                }
			}
            $inspection->setEquipment_id($this->key_id);
            $inspectionDao = new GenericDao($inspection);
            $inspection = $inspectionDao->save($inspection);
            //We only do this if the current inspection is passed
			if($inspection->getCertification_date() != null && $inspection->getStatus() == "PASS") {
                //first, save the current inspection, because it's likely we've just certifiied it.
                if($this->getRoomId() != null) $inspection->setRoom_id($this->getRoomId());
                $l->fatal("going to make a new one");

                //Next, see if there is already an inspection we can infer to to be the next one.
                global $db;
                $query = $db->prepare("select * from equipment_inspection where equipment_id = :id and equipment_class = :class and due_date > :date ORDER BY due_date");
                $id = (int) $this->key_id;
                $date = date('Y-m-d H:i:s',strtotime($inspection->getCertification_date()));
                $query->bindParam(":id",$id,PDO::PARAM_INT);
                $query->bindParam(":class",get_class($this),PDO::PARAM_STR);
                $query->bindParam(":date",$date,PDO::PARAM_STR);


                $this->principaInvestigatorId = $inspection->getPrincipal_investigator_id();

                $query->setFetchMode(PDO::FETCH_CLASS, "EquipmentInspection");			// Query the db and return one user
                if ($query->execute()) {
                    $result = $query->fetchAll();
                }

                if($result != null){
                    $nextInspection = $result[0];
                }else{
                    $nextInspection = clone $inspection;
                    $nextInspection->setCertification_date(null);
                    $nextInspection->setKey_id(null);
                    $nextInspection->setStatus("Pending");

                }

                //a cabinet must be certified either once every year, or once every other year
                if($this->frequency == "Annually"){
                    $parts = explode("-", $this->getCertification_date());

                    $parts[0] = $parts[0]+1;
                    $l->fatal("DUE DATE OUGHT TO BE:");
                    $l->fatal(implode("-", $parts));
                    $nextInspection->setDue_date(implode("-", $parts));

                }else{
                    $newCertDate = new DateTime('America/New_York');
                    $newCertDate->setTimeStamp(strtotime($this->getCertification_date()));
                    $newCertDate->modify(('+6 months'));
                    $l->fatal("DUE DATE OUGHT TO BE:");
                    $date = 
                    $l->fatal($newCertDate);
                    $nextInspection->setDue_date($newCertDate);
                }
                $nextInspection->setEquipment_id($this->key_id);

                if($inspection->getRoom_id() != null) $nextInspection->setRoom_id($inspection->getRoom_id());
                if($inspection->getEquipment_class() != null) $nextInspection->setEquipment_class($inspection->getEquipment_class());
                if($inspection->getFrequency() != null) $nextInspection->setFrequency($inspection->getFrequency());

                $nextInspection = $inspectionDao->save($nextInspection);

                if($nextInspection->getPrincipalInvestigators() != null){
                    foreach ($nextInspection->getPrincipalInvestigators() as $pi){
                        $inspectionDao->removeRelatedItems($pi->getKey_id(),$nextInspection->getKey_id(),DataRelationship::fromArray(EquipmentInspection::$PIS_RELATIONSHIP));
                    }
                }

				foreach($inspection->getPrincipalInvestigators() as $pi){
					if(is_array($pi) ){
						$id = $pi["Key_id"];
					}else{
						$id = $pi->getKey_id();
					}
					$inspectionDao->addRelatedItems($id,$nextInspection->getKey_id(),DataRelationship::fromArray(EquipmentInspection::$PIS_RELATIONSHIP));
				}
            }
            //null out the inspections for this equipment to force relaod
			$this->equipmentInspections = array();
            return $inspection;

        } else {
			$l->fatal("We should never get here, as frequency should never be null.");
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

	/*
	 *
	 * @return EquipmentInspection $inspection
	 */
    public function grabMostRecentInspection(){
        if(!$this->hasPrimaryKeyValue())return null;
        $thisDAO = new GenericDAO( new EquipmentInspection() );
        // TODO: this would be a swell place to sort
        $whereClauseGroup = new WhereClauseGroup(
            array(
                new WhereClause("equipment_class", "=", get_class($this)),
                new WhereClause("equipment_id", "=" , $this->getKey_id())
            )
        );

        $inspections = $thisDAO->getAllWhere($whereClauseGroup, "AND", "certification_date");
		$L = Logger::getLogger(__CLASS__);
		$inspection = $inspections[count($inspections)-1];;
        return  $inspection;

    }

}
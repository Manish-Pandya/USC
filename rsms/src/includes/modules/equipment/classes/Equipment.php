<?php
/**
 *
 *
 *
 * @author David Hamiter
 */

abstract class Equipment extends GenericCrud{

    protected $type;
    protected $make;
    protected $model;
    protected $frequency;
    protected $serial_number;
    protected $equipmentInspections; //array\
    protected $principalInvestigatorId;
    protected $roomId;
    protected $comments;
	private $certification_date;
	private $due_date;
    private $equipment_class;


    /** Relationships */
	public static $INSPECTIONS_RELATIONSHIP = array(
		"className"	=>	"Inspection",
		"tableName"	=>	"inspection",
		"keyName"	=>	"key_id",
		"foreignKeyName" =>	"equipment_id"
	);


	public function __construct(){
        //$this->conditionallyCreateInspectionForCurrentYear();

		
    }
    public static function defaultEntityMaps(){
        // Define which subentities to load
		$entityMaps = array();
        $entityMaps[] = EntityMap::eager("getEquipmentInspections");
		return $entityMaps;

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
        //$l->fatal($this->equipmentInspections);
		return $this->equipmentInspections;
	}
	public function setEquipmentInspections($inspections){ $this->equipmentInspections = $inspections; }

    /**
     * @return EquipmentInspection $inspection;
     **/
    public function conditionallyCreateEquipmentInspection($selectedInspection = null){
        $l = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        $l->debug("here we are now " . $this->frequency);
        if($this->frequency == null){
            $this->frequency = $selectedInspection->getFrequency() != null ? $selectedInspection->getFrequency() : "Annually";
        }
        //We only create a new inspection for Cabinets that have not yet been saved
        if ($this->frequency != null || ($selectedInspection != null && $selectedInspection->getFrequency() != null)) {
			if ($this->getEquipmentInspections() == null && $selectedInspection == null) {
				$inspection = new EquipmentInspection(get_class($this), $this->frequency, $this->getKey_id(), $this->getCertification_date());
            } else {
                if($selectedInspection == null){
                    $l->debug("selected was null");
                    $inspection = $this->grabMostRecentInspection();
                }else{
                    $l->debug("selected was not null");
                    $inspection  = $selectedInspection;
                }
			}
            $inspection->setEquipment_id($this->key_id);
            $inspectionDao = new GenericDao($inspection);
            $inspection = $inspectionDao->save($inspection);
            //We only do this if the current inspection is passed
			if($inspection->getCertification_date() != null || $inspection->getFail_date() != null) {
                //first, save the current inspection, because it's likely we've just certifiied it.
                if($this->getRoomId() != null) $inspection->setRoom_id($this->getRoomId());
                $l->debug("going to make a new one");

                //Next, see if there is already an inspection we can infer to to be the next one.
                $db = DBConnection::get();
                $query = DBConnection::prepareStatement("select * from equipment_inspection where equipment_id = :id and equipment_class = :class and (date_created > :date OR is_uncertified = 1) ORDER BY key_id DESC");
                $id = (int) $this->key_id;
                $date = date('Y-m-d H:i:s',strtotime($inspection->getDate_created()));
                $query->bindParam(":id",$id,PDO::PARAM_INT);
                $query->bindParam(":class",get_class($this),PDO::PARAM_STR);
                $query->bindParam(":date",$date,PDO::PARAM_STR);


                $this->principalInvestigatorId = $inspection->getPrincipal_investigator_id();

                $query->setFetchMode(PDO::FETCH_CLASS, "EquipmentInspection");			// Query the db and return one user
                if ($query->execute()) {
                    $result = $query->fetchAll();
                }

                if(isset($result) && is_array($result) && !empty($result) && get_class($result[0]) == "EquipmentInspection"){
                    $nextInspection = $result[0];
                    $l->debug($result);
                    $l->debug("here");
                }else{
                    $nextInspection = clone $inspection;
                    $nextInspection->setCertification_date(null);
                    $nextInspection->setFail_date(null);
                    $nextInspection->setKey_id(null);
                    $nextInspection->setStatus("PENDING");
                    $nextInspection->setComment(null);
                    $nextInspection->setReport_path(null);
                    $l->debug("nah");

                }
                $l->debug($nextInspection);

                $nextInspection->setIs_uncertified(false);


                if($inspection->getCertification_date() != null){
                    //a cabinet must be certified either once every year, or once every other year
                    if($this->frequency == "Annually"){
                        if($this->getCertification_date() != null){
                            $parts = explode("-", $this->getCertification_date());

                            $parts[0] = $parts[0]+1;
                            $l->debug("DUE DATE OUGHT TO BE:");
                            $l->debug(implode("-", $parts));
                            $nextInspection->setDue_date(implode("-", $parts));
                        }

                    }else{
                        $newCertDate = new DateTime('America/New_York');
                        $newCertDate->setTimeStamp(strtotime($this->getCertification_date()));
                        $newCertDate->modify(('+6 months'));
                        $l->debug("DUE DATE OUGHT TO BE:");
                        $l->debug($newCertDate);
                        $l->debug($this->getCertification_date());
                        $nextInspection->setDue_date($newCertDate->format("Y-m-d H:i:s"));
                    }
                }else{

                    //If this equipment has ever passed an inspection, we set the due date for our re-inspection to 1 year from the date of that inspection
                    //the results of our query are already ordered by their created dates, so the first we find with a certification_date is the one we're looking for
                    $lastPassingInspection = null;
                    foreach($result as $r){
                        $r = new EquipmentInspection();
                        if($r->getCertification_date() != null){
                            $lastPassingInspection = $r;
                            break;
                        }
                    }

                    if($lastPassingInspection != null){
                        if($this->frequency == "Annually"){
                            $parts = explode("-", $lastPassingInspection->getCertification_date());
                            $parts[0] = $parts[0]+1;
                            $l->debug("DUE DATE OUGHT TO BE:");
                            $l->debug(implode("-", $parts));
                            $nextInspection->setDue_date(implode("-", $parts));
                        }else{
                            $newCertDate = new DateTime('America/New_York');
                            $newCertDate->setTimeStamp(strtotime($lastPassingInspection->getCertification_date()));
                            $newCertDate->modify(('+6 months'));
                            $l->debug("DUE DATE OUGHT TO BE:");
                            $l->debug($newCertDate);
                            $nextInspection->setDue_date($newCertDate);
                        }
                    }else{
                        $nextInspection->setDue_date(null);
                        $nextInspection->setReport_path(null);
                        if($inspection && $inspection->getFail_date() != null)$nextInspection->setDue_date($inspection->getFail_date());
                    }

                    //set a flag so the client knows not to group our new inspection with previous year's inspections, even if it seems to be due in a previous year
                    $nextInspection->setIs_uncertified(true);

                }


                $nextInspection->setEquipment_id($this->key_id);

                if($inspection->getRoom_id() != null && $nextInspection->getRoom_id() == null) $nextInspection->setRoom_id($inspection->getRoom_id());
                if($inspection->getEquipment_class() != null) $nextInspection->setEquipment_class($inspection->getEquipment_class());
                if($inspection->getFrequency() != null) $nextInspection->setFrequency($inspection->getFrequency());

                $nextInspection = $inspectionDao->save($nextInspection);
                $newPis = $nextInspection->getPrincipalInvestigators();
                $oldPis = $inspection->getPrincipalInvestigators();
                if($newPis != null){
                    foreach ($newPis as $pi){
                        $inspectionDao->removeRelatedItems($pi->getKey_id(),$nextInspection->getKey_id(),DataRelationship::fromArray(EquipmentInspection::$PIS_RELATIONSHIP));
                    }
                }

				foreach($oldPis as $pi){
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
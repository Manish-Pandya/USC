<?php
/**
 *
 *
 *
 * @author David Hamiter
 */

class EquipmentInspection extends GenericCrud implements JsonSerializable {
	use DtoGenerator;

	/** Name of the DB Table */
	protected static $TABLE_NAME = "equipment_inspection";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
        "room_id"		            	=> "integer",
        "principal_investigator_id"		=> "principal_investigator_id",
        "certification_date"          	=> "timestamp",
        "fail_date"          	        => "timestamp",
        "is_uncertified"                => "boolean",
        "decon_path"                    => "text",
        "due_date"     		    		=> "timestamp",
        "report_path"		        	=> "text",
        "quote_path"		        	=> "text",

        "equipment_id"                  => "integer",
        "equipment_class"               => "text",
        "comment"                       => "text",
        "frequency"                     => "text",
        "status"                        => "text",


		//GenericCrud
		"key_id"			    => "integer",
		"date_created"		    => "timestamp",
		"date_last_modified"    => "timestamp",
		"is_active"			    => "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"	    => "integer"
	);

    public function __construct($equipment_class = null, $frequency = null, $equipmentId = null){
        if ($equipment_class) $this->setEquipment_class($equipment_class);
        if ($frequency) $this->setFrequency($frequency);
        if ($equipmentId) $this->setEquipment_id($equipmentId);

		
    }
    public static function defaultEntityMaps(){
        // Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getRoom");
        $entityMaps[] = EntityMap::lazy("getPrincipal_investigator");
        $entityMaps[] = EntityMap::lazy("getPrincipalInvestigators");


		return $entityMaps;
	}

    private $room_id;
    private $room;

    private $principal_investigator_id;
    private $principalInvestigators;
    private $certification_date;
    private $fail_date;
    private $due_date;
    private $report_path;
    private $quote_path;
    private $decon_path;
    private $equipment_id;
    private $equipment_class;
    private $comment;
    private $status;
    private $frequency;
    private $is_uncertified;
    private $priorStatus;


    // Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
        //return array_merge(parent::$COLUMN_NAMES_AND_TYPES, this:);
		return self::$COLUMN_NAMES_AND_TYPES;
	}

    public static $PIS_RELATIONSHIP = array(
        "className"	=>	"PrincipalInvestigator",
        "tableName"	=>	"principal_investigator_equipment_inspection",
        "keyName"	=>	"principal_investigator_id",
        "foreignKeyName"	=>	"inspection_id"
    );

    public function getRoom_id(){
		return $this->room_id;
	}
	public function setRoom_id($room_id){
		$this->room_id = $room_id;
	}

    public function getRoom(){
		if($this->room == null && $this->room_id != null) {
			$roomDao = new RoomDAO();
			$this->room = $roomDao->getById($this->room_id);
		}
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

	public function getCertification_date(){
		return $this->certification_date;
	}
	public function setCertification_date($certification_date){
		$this->certification_date = $certification_date;
        // new instance will be spawned in the controller
	}

    public function getFail_date(){
		return $this->fail_date;
	}
	public function setFail_date($fail_date){
		$this->fail_date = $fail_date;
        // new instance will be spawned in the controller
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

    public function getQuote_path(){
		return $this->quote_path;
	}
	public function setQuote_path($qp){
		$this->quote_path = $qp;
	}

	public function getEquipment_id(){
		return $this->equipment_id;
	}
	public function setEquipment_id($equipment_id){
		$this->equipment_id = $equipment_id;
	}

	public function getEquipment_class(){
		return $this->equipment_class;
	}
	public function setEquipment_class($equipment_class){
		$this->equipment_class = $equipment_class;
	}

    public function getComment(){
		return $this->comment;
	}
	public function setComment($comment){
		$this->comment = $comment;
	}

    public function getStatus(){
        if($this->hasPrimaryKeyValue() &&
            $this->getEquipment_class() == "BioSafetyCabinet"){
            //Cabinets that are certified have non-null status of either "PASS" or "FAIL" persisted in the DB
            //Therefore we can assume that all cabinets that don't have a status saved are either new, overdue, or pending certification

            //cabinets that haven't yet been certified, ever, or had a due date assigned are new
            if($this->getDue_date() == NULL && $this->getCertification_date() == null && $this->getFail_date() == null){
                $this->status = "NEW";
            }else if($this->fail_date != null){
                $this->status = "FAIL";
            }
            //equipment did not fail this year, but did fail it's previous inspection
            else if($this->is_uncertified){
                $this->priorStatus = true;
                $this->status = NULL;

            }
            //all other cabinets that don't have a persisted status are either Overdue or pending a certification
            else if($this->fail_date == null && $this->certification_date == null && $this->is_uncertified !== true){
                $startOfToday = strtotime('today midnight');
                if(strtotime($this->getDue_date()) > $startOfToday){
                    $this->status = "PENDING";
                }else{
                    $this->status = "OVERDUE";
                }
            }
        }

        return $this->status;
	}
	public function setStatus($status){
		$this->status = $status;
	}

    public function getFrequency(){
		return $this->frequency;
	}
	public function setFrequency($frequency){
		$this->frequency = $frequency;
	}

    /**
     * Summary of getPrincipalInvestigators
     * @return PrincipalInvestigator[]
     */
    public function getPrincipalInvestigators(){
		if($this->principalInvestigators == null && !is_array($this->principalInvestigators)) {
			$thisDAO = new GenericDAO($this);
			$pis = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$PIS_RELATIONSHIP));

            $this->principalInvestigators = array();
            foreach($pis as $pi){
                if(!$pi->getIs_active()){
                    if($pi->getDate_last_modified() >= $this->date_created){
                        $this->principalInvestigators[] = $pi;
                    }
                }else{
                    $this->principalInvestigators[] = $pi;
                }
            }
        }
		return $this->principalInvestigators;
	}
	public function setPrincipalInvestigators($principalInvestigators){ $this->principalInvestigators = $principalInvestigators; }

    public function getIs_uncertified(){ return (boolean) $this->is_uncertified; }
	public function setIs_uncertified( $is_uncertified ){	$this->is_uncertified = $is_uncertified; }

    public function getPriorStatus(){ return (boolean) $this->priorStatus; }
	public function setPriorStatus( $bool ){	$this->priorStatus = $bool; }

    public function getDecon_path(){ return $this->decon_path; }
	public function setDecon_path($decon_path){ $this->decon_path = $decon_path; }
}
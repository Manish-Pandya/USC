<?php
/**
 *
 *
 *
 * @author David Hamiter
 */
class EquipmentInspection extends GenericCrud{

	/** Name of the DB Table */
	protected static $TABLE_NAME = "equipment_inspection";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
        "room_id"		            	=> "integer",
        "principal_investigator_id"		=> "principal_investigator_id",
        "certification_date"          	=> "timestamp",
        "due_date"     		    		=> "timestamp",
        "report_path"		        	=> "text",
        "equipment_id"                  => "integer",
        "equipment_class"               => "text",
        "comment"                       => "text",
        "frequency"                     => "text",
				
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

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getRoom");
        $entityMaps[] = new EntityMap("lazy","getPrincipal_investigator");
		$this->setEntityMaps($entityMaps);
	}
    
    private $room_id;
    private $principal_investigator_id;
    private $certification_date;
    private $due_date;
    private $report_path;
    private $equipment_id;
    private $equipment_class;
    private $comment;
    private $status;
    private $frequency;
    
    // Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
        //return array_merge(parent::$COLUMN_NAMES_AND_TYPES, this:);
		return self::$COLUMN_NAMES_AND_TYPES;
	}

    public function getRoom_id(){
		return $this->room_id;
	}
	public function setRoom_id($room_id){
		$this->room_id = $room_id;
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

    public function getDue_date(){
        $LOG = Logger::getLogger(__Class__);
        if ($this->getFrequency() == null) {
            return null;
        }
        if($this->due_date == null){
		    $dueDate = new DateTime($this->getDate_created());            
		    if($this->getFrequency() == "Annually"){
			    $dueDate->modify('+1 year');
		    }else if($this->getFrequency() == "Semi-annually"){
			    $dueDate->modify('+6 months'); // twice a year
            }
		    $this->setDue_date($dueDate->format('Y-m-d H:i:s'));
        }
		
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
    
}
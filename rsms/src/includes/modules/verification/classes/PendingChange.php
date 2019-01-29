<?php
/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class PendingChange extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "pending_change";
	
	/** Key/Value Array listing column names mapped to their types */
	protected $COLUMN_NAMES_AND_TYPES = array(
			"parent_id" 		=> "integer",
			"verification_id"	=> "integer",
			"new_status"		=> "text",
			"parent_class"		=> "text",
			"approval_date"		=> "timestamp",
			"adding"			=> "boolean",
			"answer"			=> "text",
			"emergency_phone"	=> "text",
			"building_name"		=> "text",
			"name"				=> "name",
			"phone_approved"	=> "boolean",
            "role"		    	=> "text",
				
			//GenericCrud
			"key_id"			=> "integer",
			"date_created"		=> "timestamp",
			"date_last_modified"	=> "timestamp",
			"is_active"			=> "boolean",
			"last_modified_user_id"			=> "integer",
			"created_user_id"	=> "integer"
	);
	
	protected $verification_id;
	protected $parent_id;
	protected $parent_class;
	protected $new_status;
	protected $approval_date;
	protected $adding;	
	protected $answer;
	protected $emergency_phone;
	protected $name;
	protected $phone_approved;
	
	public function __construct(){
	
		// Define which subentities to load
		$entityMaps = array();
		//$entityMaps[] = new EntityMap("lazy","getDeficiencySelection");
		$entityMaps[] = new EntityMap("eager","getParent_id");
		$this->setEntityMaps($entityMaps);
	
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return $this->COLUMN_NAMES_AND_TYPES;
	}
	
	public function getVerification_id(){
		
		return $this->verification_id;
	}
	public function setVerification_id($verification_id){$this->verification_id = $verification_id;}
	
	public function getParent_id(){
		$LOG = Logger::getLogger(__CLASS__);
		//$LOG->fatal('calling it');
		//$LOG->fatal($this->parent_id);
		return $this->parent_id;
	}
	public function setParent_id($parent_id){
		$this->parent_id = $parent_id;
	}
	
	public function getNew_status(){return $this->new_status;}	
	public function setNew_status($new_status){$this->new_status = $new_status;}
	
	public function getApproval_date(){return $this->approval_date;}
	public function setApproval_date($date){$this->approval_date = $date;}
	
	public function getAdding(){return (boolean) $this->adding;}
	public function setAdding($add){$this->adding = $add;}
	
	public function getParent_class(){return $this->parent_class;}
	public function setParent_class($parent_class){$this->parent_class = $parent_class;}
	
	public function getAnswer(){return $this->answer;}
	public function setAnswer($answer){$this->answer = $answer;}
    
    public function getRole(){return $this->role;}
	public function setRole($role){$this->role = $role;}
	
	public function getEmergency_phone(){
		return $this->emergency_phone;
	}
	public function setEmergency_phone($phone){
		$this->emergency_phone = $phone;
	}
	
	public function getPhone_approved(){
		return $this->phone_approved;
	}
	public function setPhone_approved($phone){
		$this->phone_approved = $phone;
	}
	
	public function getName(){
		if($this->name != null)return $this->name;
		if($this->getParent_id() != null && $this->getParent_class() != null){
			$class = $this->parent_class;
			$dao = new GenericDAO(new $class());
			return $dao->getById($this->getParent_id())->getName();
		}
		return NULL;
	}
	public function setName($name){$this->name = $name;}
	
	/** for PendingRoomChange only.  In parent class so that property and getter exist to match db column 
	 * 	hooray for PHP "polymorphism"
	 * **/
	
	protected $building_name;	
	public function getBuilding_name(){return $this->building_name;}
	public function setBuilding_name($building_name){$this->building_name = $building_name;}
	

}
?>
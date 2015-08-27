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
	protected static $COLUMN_NAMES_AND_TYPES = array(
			"parent_id" 		=> "integer",
			"verification_id"	=> "integer",
			"new_status"		=> "text",
			"parent_class"		=> "text",
			"approval_date"		=> "timestamp",
			"adding"			=> "boolean",
				
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
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getVerification_id(){
		
		return $this->verification_id;
	}
	public function setVerification_id($verification_id){$this->verification_id = $verification_id;}
	
	public function getParent_id(){
		$LOG = Logger::getLogger(_Class_);
		$LOG->fatal('calling it');
		$LOG->fatal($this->parent_id);
		return $this->parent_id;
	}
	public function setParent_id($parent_id){
		$this->parent_id = $parent_id;
	}
	
	public function getName(){return $this->name;}
	public function setName($name){$this->name = $name;}
	
	public function getNew_status(){return $this->new_status;}	
	public function setNew_status($new_status){$this->new_status = $new_status;}
	
	public function getApproval_date(){return $this->approval_date;}
	public function setApproval_date($date){$this->approval_date = $date;}
	
	public function getAdding(){return (boolean) $this->adding;}
	public function setAdding($add){$this->adding = $add;}
	
	public function getParent_class(){return $this->parent_class;}
	public function setParent_class($parent_class){$this->parent_class = $parent_class;}

}
?>
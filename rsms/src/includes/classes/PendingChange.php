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
			//deficiency selection is a relationship
			"parent_id" => "integer",
			"verification_id"	=> "verification_id",
			"new_status"		=> "new_status",
			"parent_class"		=> "parent_class",
			"approval_date"		=> "timestamp",
			"adding"				=> "boolean",
				
			//GenericCrud
			"key_id"			=> "integer",
			"date_created"		=> "timestamp",
			"date_last_modified"	=> "timestamp",
			"is_active"			=> "boolean",
			"last_modified_user_id"			=> "integer",
			"completion_date"   =>     "timestamp",
			"promised_date"   =>     "timestamp",
			"created_user_id"	=> "integer"
	);
	
	private $verification_id;
	private $parent_id;
	private $parent_class;
	private $new_status;
	private $approval_date;
	private $adding;
	
	public function __construct(){
	
		// Define which subentities to load
		$entityMaps = array();
		//$entityMaps[] = new EntityMap("lazy","getDeficiencySelection");
		$this->setEntityMaps($entityMaps);
	
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getVerification_id(){return $this->verification_id;}
	public function setVerification_id($verification_id){$this->verification_id = $verification_id;}
	
	public function getParent_id(){return $this->parent_id;}
	public function setParent_id($parent_id){$this->parent_id = $parent_id ;}
	
	public function getName(){return $this->name;}
	public function setName($name){$this->name = $name;}
	
	public function getNew_status(){return $this->new_status;}	
	public function setNew_status($new_status){$this->new_status = $new_status;}
	
	public function getApproval_date(){return $this->approval_date;}
	public function setApproval_date($date){$this->approval_date = $date;}
	
	public function getAdding(){return (boolean) $this->adding;}
	public function setAdding($add){$this->adding = $add;}
}
?>
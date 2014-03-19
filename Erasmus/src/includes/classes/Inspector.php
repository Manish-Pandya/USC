<?php
/**
 * TODO: DOC
 * 
 * @author Hoke Currie, GraySail LLC
 */
class Inspector extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "inspector";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"user_id" => "integer",

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer"
	);
	
	/** Relationships */
	protected static $INSPECTIONS_RELATIONSHIP = array(
		"className"	=>	"Inspection",
		"tableName"	=>	"inspection_inspector",
		"keyName"	=>	"inspection_id",
		"foreignKeyName"	=>	"inspector_id"
	); 

	/** Base User object that this Inspector represents */
	private $user_id;
	private $user;
	
	/** Array of Inspection entities */
	private $inspections;
	
	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getInspections");
		$entityMaps[] = new EntityMap("lazy","getUser");
		$this->setEntityMaps($entityMaps);
				
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getUser(){
		if($this->user == null) {
			$userDAO = new GenericDAO("User");
			$this->user = $userDAO->getById($this->user_id);
		}
	}
	
	public function setUser($user){
		$this->user = $user; 
	}
	
	public function getUser_id(){ return $this->user_id; }
	public function setUser_id($id){ $this->user_id = $id; }
	
	public function getInspections(){
		if($this->inspections === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->inspections = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$INSPECTIONS_RELATIONSHIP));
		}
		return $this->inspections;
	}
	public function setInspections($inspections){ $this->inspections = $inspections; }
	
}
?>
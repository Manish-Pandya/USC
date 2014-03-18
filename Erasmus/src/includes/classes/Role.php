<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Hoke Currie, GraySail LLC
 */
class Role extends GenericCrud{
	
	// CRUD Meta-Data
	/** Name of the DB Table */
	protected static $TABLE_NAME = "role";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"				=> "text",
		
		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer"
	);
	
	/** Relationships */
	protected static $USERS_RELATIONSHIP = array(
		"className"	=>	"User",
		"tableName"	=>	"user_role",
		"keyName"	=>	"role_id",
		"foreignKeyName"	=>	"user_id"
	); 
	
	// Access information
	
	/** Array of users */
	private $users;
	
	// General Role Info
	
	/** name of this Role */
	private $name;
	
	
	// Constructor(s)
	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getUsers");
		$this->setEntityMaps($entityMaps);
		
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	// Accessors / Mutators
	public function getUsers(){ 
		if($this->users == null) {
			$roleDAO = new GenericDAO($this);
			$this->users = $roleDAO->getRelatedItemsById($this->getKey_Id(), DataRelationShip::fromArray(self::$USERS_RELATIONSHIP));
		}
		return $this->users;
	}
	public function setUsers($users){ $this->users = $users; }
	
	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }
	
}
?>
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
	protected static $TABLE_NAME = "erasmus_role";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"				=> "text",
		"key_id"			=> "int",
		"dateCreated"		=> "timestamp",
		"dateLastModified"	=> "timestamp",
		"isActive"			=> "boolean"
	);
	
	/** Relationships */
	protected static $USERS_RELATIONSHIP = array(
		"className"	=>	"User",
		"tableName"	=>	"erasmus_user_role",
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
			$this->users = $roleDAO->getRelatedItemsById($this->getKeyId(), DataRelationShip::fromArray(self::$USERS_RELATIONSHIP));
		}
		return $this->users;
	}
	public function setUsers($users){ $this->users = $users; }
	
	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }
	
}
?>
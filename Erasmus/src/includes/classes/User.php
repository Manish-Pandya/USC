<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class User extends GenericCrud{
	
	// CRUD Meta-Data
	/** Name of the DB Table */
	protected static $TABLE_NAME = "erasmus_user";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//roles are a relationship
		"username"	=> "text",
		"name"		=> "text",
		"email"		=> "text", 
	);
	
	/** Relationships */
	protected static $ROLES_RELATIONSHIP = array(
		"className"	=>	"Role",
		"tableName"	=>	"erasmus_user_role",
		"keyName"	=>	"user_id",
		"foreignKeyName"	=>	"role_id"
	); 
	
	// Access information
	
	/** Array of roles */
	private $roles;
	
	// General User Info
	
	/** System Name for this User */
	private $username;
	
	/** 'Real' name of this User */
	private $name;
	
	/** Email address of this User */
	private $email;
	
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
	public function getRoles(){ 
		if($this->roles == null) {
			$userDAO = new GenericDAO($this);
			$userDAO->getRelatedItemsById($this->key_id, DataRelationShip::fromArray(self::$ROLES_RELATIONSHIP))
		}

	}
	public function setRoles($roles){ $this->roles = $roles; }
	
	public function getUsername(){ return $this->username; }
	public function setUsername($username){ $this->username = $username; }
	
	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }
	
	public function getEmail(){ return $this->email; }
	public function setEmail($email){ $this->email = $email; }
}
?>
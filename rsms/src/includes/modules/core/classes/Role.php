<?php

/**
 *
 *
 *
 * @author Hoke Currie, GraySail LLC
 */
class Role extends GenericCrud implements JsonSerializable {
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
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer",
		"bit_value" => "integer"
	);
	
	/** Relationships */
	protected static $USERS_RELATIONSHIP = array(
		"className"	=>	"User",
		"tableName"	=>	"user_role",
		"keyName"	=>	"user_id",
		"foreignKeyName"	=>	"role_id"
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

	public static function defaultEntityMaps(){
		return array( EntityMap::lazy("getUsers") );
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	public function jsonSerialize(){
		return array(
			'Key_id' => $this->getKey_id(),
			'Name' => $this->getName()
		);
	}
	
	// Accessors / Mutators
	public function getUsers(){ 
		if($this->users == null) {
			$roleDAO = new GenericDAO($this);
			$this->users = $roleDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$USERS_RELATIONSHIP));
		}
		return $this->users;
	}
	public function setUsers($users){ $this->users = $users; }
	
	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }
	
	public function getBit_value(){ return $this->bit_value; }
	public function setBit_value($bitValue){ $this->bit_value = $bitValue; }
	
}
?>
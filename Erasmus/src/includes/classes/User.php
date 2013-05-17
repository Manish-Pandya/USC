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
		"key_id"	=> "integer",
		"active"	=> "bolean",
		//"roles"	=>
		"username"	=> "text",
		"name"		=> "text",
		"email"		=> "text", 
	);
	
	/** Primary Key for this entity */
	private $key_id;
	
	/** Boolean value specifying if this user account is active or disabled */
	private $active;
	
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
	
	public function __toString(){
		return "[User key_id=$this->key_id]";
	}
	
	// Accessors / Mutators

	public function get_key_id(){ return $this->key_id; }
	public function set_key_id($keyId){ $this->key_id = $keyId; }
	
	public function get_active(){ return $this->active; }
	public function set_active($active){ $this->active = $active; }
	
	public function get_roles(){ return $this->roles; }
	public function set_roles($roles){ $this->roles = $roles; }
	
	public function get_username(){ return $this->username; }
	public function set_username($username){ $this->username = $username; }
	
	public function get_name(){ return $this->name; }
	public function set_name($name){ $this->name = $name; }
	
	public function get_email(){ return $this->email; }
	public function set_email($email){ $this->email = $email; }
}
?>
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
		"supervisor_id"		=> "integer",
							
		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer"
	);
	
	/** Relationships */
	protected static $ROLES_RELATIONSHIP = array(
		"className"	=>	"Role",
		"tableName"	=>	"user_role",
		"keyName"	=>	"role_id",
		"foreignKeyName"	=>	"user_id"
	); 

	protected static $PI_RELATIONSHIP = array(
			"className"	=>	"PrincipalInvestigator",
			"tableName"	=>	"principal_investigator",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"user_id"
	);

	protected static $INSPECTOR_RELATIONSHIP = array(
			"className"	=>	"Inspector",
			"tableName"	=>	"inspector",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"user_id"
	);
	
	
	// Access information
	
	/** Array of roles */
	private $roles;
	
	/** Optional Related PI record (if this user is a PI) */
	private $principalInvestigator;
	
	/** Optional Related Inspector record (if this user is a Inspector) */
	private $inspector;
	
	/** Supervisor Principal Investigator (if this user works for a PI */
	private $supervisor;
	private $supervisor_id;
	
	// General User Info
	
	/** System Name for this User */
	private $username;
	
	/** 'Real' name of this User */
	private $name;
	
	/** Email address of this User */
	private $email;
	
	// Constructor(s)
	public function __construct(){
		
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getPrincipalInvestigator");
		$entityMaps[] = new EntityMap("lazy","getInspector");
		$entityMaps[] = new EntityMap("lazy","getSupervisor");
		$entityMaps[] = new EntityMap("eager","getRoles");
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
	public function getRoles(){ 
		if($this->roles === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->roles = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$ROLES_RELATIONSHIP));
		}
		return $this->roles;
	}
	public function setRoles($roles){ $this->roles = $roles; }
	
	public function getPrincipalInvestigator(){ 
		if($this->principalInvestigator === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$piArray = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$PI_RELATIONSHIP));
			if (isset($piArray[0])) {$this->principalInvestigator = $piArray[0];}
		}
		return $this->principalInvestigator;
	}
	public function setPrincipalInvestigator($principalInvestigator){ $this->principalInvestigator = $principalInvestigator; }
	

	public function getInspector(){ 
		if($this->inspector === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$inspectorArray = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$INSPECTOR_RELATIONSHIP));
			if (isset($inspectorArray[0])) {$this->inspector = $inspectorArray[0];}
		}
		return $this->inspector;
	}
	public function setInspector($inspector){ $this->inspector = $inspector; }
	
	public function getSupervisor_id(){ return $this->supervisor_id; }
	public function setSupervisor_id($id){ $this->supervisor_id = $id; }
	
	public function getSupervisor() {
		if($this->supervisor === NULL && $this->hasPrimaryKeyValue()) {
			$superDAO = new GenericDAO(new Supervisor());
			$this->supervisor = $superDAO->getById($this->supervisor_id);
		}
		return $this->supervisor;
	}
	public function setSupervisor($supervisor) {
		$this->supervisor = $supervisor;
	}
	
	public function getUsername(){ return $this->username; }
	public function setUsername($username){ $this->username = $username; }
	
	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }
	
	public function getEmail(){ return $this->email; }
	public function setEmail($email){ $this->email = $email; }
}
?>
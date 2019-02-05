<?php

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
		"first_name" => "text",
		"last_name"		=> "text",
		"email"		=> "text",
		"lab_phone"      => "text",
		"office_phone"	=>  "text",
		"supervisor_id"		=> "integer",
		"emergency_phone" => "text",
		"primary_department_id" => "integer",
		"position" => "text",

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer"
	);

	/** Relationships */
	public static $ROLES_RELATIONSHIP = array(
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
    /** Optional Related Inspector record's key_id (if this user is a Inspector) */
    private $inspector_id;

	/** Supervisor Principal Investigator (if this user works for a PI */
	private $supervisor;
	private $supervisor_id;

	// General User Info

	/** System Name for this User */
	private $username;

	/** 'Real' name of this User */
	private $name;
	private $first_name;
	private $last_name;

	private $position;

	/** Email address of this User */
	private $email;

	/** lab, emergency and office phone numbers of this user, if this user has any or all of them **/
	private $lab_phone;
	private $emergency_phone;
	private $office_phone;

	/** this user's primary department.  Lab Contacts have a single department **/
	private $primary_department_id;
	private $primary_department;

	// Constructor(s)
	public function __construct(){

		
    }
    public static function defaultEntityMaps(){
        // Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getPrincipalInvestigator");
		$entityMaps[] = new EntityMap("lazy","getInspector");
		$entityMaps[] = new EntityMap("lazy","getSupervisor");
		$entityMaps[] = new EntityMap("eager","getRoles");
		return $entityMaps;

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
			$this->roles = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$ROLES_RELATIONSHIP));
		}
		return $this->roles;
	}
	public function setRoles($roles){ $this->roles = $roles; }

	public function getPrincipalInvestigator(){
		if($this->principalInvestigator === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$piArray = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PI_RELATIONSHIP));
			if (isset($piArray[0])) {$this->principalInvestigator = $piArray[0];}
		}
		return $this->principalInvestigator;
	}
	public function setPrincipalInvestigator($principalInvestigator){ $this->principalInvestigator = $principalInvestigator; }


	public function getInspector(){
		if($this->inspector === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$inspectorArray = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$INSPECTOR_RELATIONSHIP));
			if (isset($inspectorArray[0])) {$this->inspector = $inspectorArray[0];}
		}
		return $this->inspector;
	}
	public function setInspector($inspector){ $this->inspector = $inspector; }

    public function getInspector_id(){
        if($this->inspector_id == null && $this->getInspector() != null){
            $this->inspector_id = $this->inspector->getKey_id();
        }

        return $this->inspector_id;
    }
    public function setInspector_id($id){$this->inspector_id = $id;}

	public function getSupervisor_id(){ return $this->supervisor_id; }
	public function setSupervisor_id($id){ $this->supervisor_id = $id; }

	public function getSupervisor() {
		if($this->supervisor === NULL && $this->hasPrimaryKeyValue() && $this->supervisor_id > 0) {
			$superDAO = new GenericDAO(new PrincipalInvestigator());
			$this->supervisor = $superDAO->getById($this->supervisor_id);
		}
		return $this->supervisor;
	}
	public function setSupervisor($supervisor) {
		$this->supervisor = $supervisor;
	}

	public function getUsername(){ return $this->username; }
	public function setUsername($username){ $this->username = $username; }

	public function getFirst_name(){ return $this->first_name; }
	public function setFirst_name($first_name){ $this->first_name = $first_name; }

	public function getLast_name(){ return $this->last_name; }
	public function setLast_name($last_name){ $this->last_name = $last_name; }

	public function getEmail(){ return $this->email; }
	public function setEmail($email){ $this->email = $email; }

	public function getEmergency_phone(){ return $this->emergency_phone; }
	public function setEmergency_phone($Emergency_phone){ $this->emergency_phone = $Emergency_phone; }

	public function getLab_phone(){ return $this->lab_phone;}
	public function setLab_phone($lab_phone){ $this->lab_phone = $lab_phone;}

	public function getOffice_phone(){ return $this->office_phone; }
	public function setOffice_phone($office_phone){ $this->office_phone = $office_phone;}

	public function getPrimary_department_id(){ return $this->primary_department_id;}
	public function setPrimary_department_id($primary_department_id){ $this->primary_department_id = $primary_department_id; }

	public function getPosition(){ return $this->position; }
	public function setPosition($position){ $this->position = $position;}

	public function getPrimary_department() {
		if($this->getSupervisor_id() != NULL && $this->hasPrimaryKeyValue()) {
            $super = $this->getSupervisor();
            if($super != null){
				$superDao = new PrincipalInvestigatorDAO();
                $this->primary_department = $superDao->getPrimaryDepartment($this->getSupervisor_id());
            }
		}
		return $this->primary_department;
	}
	public function setPrimary_department($primary_department) {
		$this->primary_department = $primary_department;
	}

	//decorator method to return a user's full name as concatenate string
	public function getName(){
		if($this->getFirst_name() != null)return $this->getFirst_name().' '.$this->getLast_name();
		return $this->getLast_name();
	}

}
?>
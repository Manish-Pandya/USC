<?php
/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */

class PIAuthorization extends RadCrud{

	/** Name of the DB Table */
	public static $TABLE_NAME = "pi_authorization";


	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(

			"principal_investigator_id" => "integer",
			"authorization_number" 		=> "text",
			"amendment_number"			=> "integer",
            "termination_date"          => "timestamp",
            "termination_notes"         => "text",
            "new_notes"                 => "text",
            "update_notes"              => "text",
            "approval_date"             => "timestamp",

			//GenericCrud
			"key_id"			=> "integer",
			"date_created"		=> "timestamp",
			"date_last_modified"	=> "timestamp",
			"is_active"			=> "boolean",
			"last_modified_user_id"			=> "integer",
			"created_user_id"	=> "integer",
	);

	public static $ROOMS_RELATIONSHIP = array(
			"className"	=>	"Room",
			"tableName"	=>	"pi_authorization_room",
			"keyName"	=>	"room_id",
			"foreignKeyName" =>	"pi_authorization_id"
	);

	public static $DEPARTMENTS_RELATIONSHIP = array(
			"className"	=>	"Department",
			"tableName"	=>	"pi_authorization_department",
			"keyName"	=>	"department_id",
			"foreignKeyName" =>	"pi_authorization_id"
	);

	public static $USERS_RELATIONSHIP = array(
			"className"	=>	"User",
			"tableName"	=>	"pi_authorization_user",
			"keyName"	=>	"user_id",
			"foreignKeyName" =>	"pi_authorization_id"
	);

	public static $AUTHORIZATIONS_RELATIONSHIP = array(
			"className" =>  "Authorization",
			"tableName" =>  "authorization",
			"keyName"   =>  "key_id",
			"foreignKeyName" => "pi_authorization_id"
	);

    public static $CONDITIONS_RELATIONSHIP = array(
        "className" =>  "RadCondition",
        "tableName" =>  "pi_authorization_rad_condition",
        "keyName"   =>  "condition_id",
        "foreignKeyName" => "pi_authorization_id",
        "orderColumn"    => "order_index"
    );

	private $principal_investigator_id;

	/**
	 * Summary of $rooms
	 * @var Room[]
	 */
	private $rooms;
	/**
	 * Summary of $departments
	 * @var Department[]
	 */
	private $departments;
    /**
     * Summary of $users
     * @var User[]
     */
    private $users;
	private $authorization_number;
	private $amendment_number;

    /**date this authorization was revoked entirely**/
    private $termination_date;
    private $termination_notes;

    private $approval_date;

    private $piName;

	/** Array of Authorizations entities */
	private $authorizations;

    private $new_notes;
    private $update_notes;

    private $conditions;

	public function __construct(){
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager", "getRooms");
		$entityMaps[] = new EntityMap("eager", "getAuthorizations");
		$entityMaps[] = new EntityMap("eager", "getDepartments");
		$entityMaps[] = new EntityMap("eager", "getAuthorizations");
		$this->setEntityMaps($entityMaps);
	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	public function getPrincipal_investigator_id(){return $this->principal_investigator_id;}
	public function setPrincipal_investigator_id($id){$this->principal_investigator_id = $id;}

	public function getAuthorization_number(){return $this->authorization_number;}
	public function setAuthorization_number($authNumber){$this->authorization_number = $authNumber;}

	public function getAmendment_number(){return $this->amendment_number;}
	public function setAmendment_number($num){$this->amendment_number = $num;}

	public function getRooms(){
		if($this->rooms === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->rooms = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$ROOMS_RELATIONSHIP));
		}
		return $this->rooms;
	}
	public function setRooms($rooms){
		$this->rooms = $rooms;
	}

	public function getUsers(){
		if($this->users === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->users = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$USERS_RELATIONSHIP));
		}
		return $this->users;
	}
	public function setUsers($users){
		$this->users = $users;
	}

	public function getDepartments(){
		if($this->departments === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->departments = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$DEPARTMENTS_RELATIONSHIP));
		}
		return $this->departments;
	}
	public function setDepartments($departments){
		$this->departments = $departments;
	}

	public function getAuthorizations() {
		if($this->authorizations === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->authorizations = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$AUTHORIZATIONS_RELATIONSHIP));
		}
		return $this->authorizations;
	}
	public function setAuthorizations($authorizations) {
		$this->authorizations = $authorizations;
	}

    public function getTermination_date(){return $this->termination_date;}
	public function setTermination_date($date){$this->termination_date = $date;}

    public function getTermination_notes(){return $this->termination_notes;}
	public function setTermination_notes($notes){$this->termination_notes = $notes;}

    public function getApproval_date(){return $this->approval_date;}
	public function setApproval_date($date){$this->approval_date = $date;}

    public function getPiName(){
        if($this->piName == null && $this->principal_investigator_id != null){
            $piDao = new GenericDAO(new PrincipalInvestigator());
            $pi = $piDao->getById($this->principal_investigator_id);
            $this->piName = $pi->getUser()->getLast_name() . ", " . $pi->getUser()->getFirst_name();
        }
        return $this->piName;
    }

    public function getNew_notes(){return $this->new_notes;}
    public function setNew_notes($notes){$this->new_notes = $notes;}

    public function getUpdate_notes(){return $this->update_notes;}
    public function setUpdate_notes($notes){$this->update_notes = $notes;}

    public function getConditions(){
		if($this->conditions === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->conditions = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$CONDITIONS_RELATIONSHIP));
		}
		return $this->conditions;
	}
	public function setConditions($conditions){ $this->conditions = $conditions; }

}

?>
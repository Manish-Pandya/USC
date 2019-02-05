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
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer"
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
    //convenience access to name of user associated with this inspector
    private $name;

	/** Array of Inspection entities */
	private $inspections;

	public function __construct(){

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getInspections");
		$entityMaps[] = EntityMap::lazy("getUser");
		return $entityMaps;
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
			$userDAO = new GenericDAO(new User());
			$this->user = $userDAO->getById($this->user_id);
		}
		return $this->user;
	}

	public function setUser($user){
		$this->user = $user;
	}

	public function getUser_id(){ return $this->user_id; }
	public function setUser_id($id){ $this->user_id = $id; }

	public function getInspections(){
		if($this->inspections === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->inspections = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$INSPECTIONS_RELATIONSHIP));
		}
		return $this->inspections;
	}
	public function setInspections($inspections){ $this->inspections = $inspections; }

    public function getName(){
        if($this->user != null){
            $this->name = $this->getUser()->getName();
		}
		else {
			// Query for just the user's Name; no need to pull in the whole User entity
			$sql = "SELECT CONCAT_WS(' ', COALESCE(first_name, ''), last_name) as full_name FROM erasmus_user WHERE key_id=:userId";
			$stmt = DBConnection::prepareStatement($sql);
			$stmt->bindParam(':userId', $this->user_id);
			$stmt->execute();
			$this->name = $stmt->fetchColumn();
		}

        return  $this->name;
    }

}
?>
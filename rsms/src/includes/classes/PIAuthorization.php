<?php
/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */

class PIAuthorization extends RadCrud{
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "pi_authorization";
	
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
			
			"principal_investigator_id" => "integer",
			"authorization_number" 		=> "text",
			
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
	
	
	public static $AUTHORIZATIONS_RELATIONSHIP = array(
			"className" =>  "Authorization",
			"tableName" =>  "authorization",
			"keyName"   =>  "key_id",
			"foreignKeyName" => "pi_authorization_id"
	);
	
	private $principal_investigator_id;
	
	private $rooms;
	private $departments;
	private $authorization_number;
	
	/** Array of Authorizations entities */
	private $authorizations;
	
	public function __construct(){
		
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
}

?>
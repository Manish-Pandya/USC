<?php
/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */

class Verification extends GenericCrud{
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "verification";
	
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
			
			"principal_investigator_id" => "integer",
			"notification_date"			=> "timestamp",
			"due_date"					=> "timestamp",
			"completed_date"			=> "timestamp",
			
			//GenericCrud
			"key_id"			=> "integer",
			"date_created"		=> "timestamp",
			"date_last_modified"	=> "timestamp",
			"is_active"			=> "boolean",
			"last_modified_user_id"			=> "integer",
			"created_user_id"	=> "integer",
			"cap_complete"      => "integer",
			"is_rad"			=> "boolean"
	);
	
	public static $PENDING_ROOM_CHANGES_RELATIONSHIP = array(
			"className"	=>	"PendingRoomChange",
			"tableName"	=>	"pending_change",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"parent_id"
	);
	
	public static $PENDING_USER_CHANGES_RELATIONSHIP = array(
			"className"	=>	"PendingUserChange",
			"tableName"	=>	"pending_change",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"parent_id"
	);
	
	public static $PENDING_HAZARD_CHANGES_RELATIONSHIP = array(
			"className"	=>	"PendingHazardChange",
			"tableName"	=>	"pending_change",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"parent_id"
	);
	
	private $principal_investigator_id;
	private $notification_date;
	private $due_date;
	private $completed_date;
	
	private $pendingRoomChanges;
	private $pendingUserChanges;
	private $pendingHazardChanges;
	
	public function __construct(){}
	
	public function getPrincipal_investigator_id(){return $this->principal_investigator_id;}
	public function setPrincipal_investigator_id($id){$this->principal_investigator_id = $id;}
	
	public function getNotification_date(){return $this->notification_date;}
	public function setNotification_datw($date){$this->notification_date = $date;}
	
	public function getDue_date(){return $this->due_date;}
	public function setDue_datw($date){$this->due_date = $date;}
	
	public function getCompleted_date(){return $this->completed_date;}
	public function setCompleted_date($date){$this->completed_date = $date;}
	
	public function getPendingRoomChanges(){
		if($this->pendingRoomChanges === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->pendingRoomChanges = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PENDING_ROOM_CHANGES_RELATIONSHIP));
		}
		return $this->pendingRoomChanges;
	}
	
	public function getPendingUserChanges(){
		if($this->pendingUserChanges === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->pendingUserChanges = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PENDING_USER_CHANGES_RELATIONSHIP));
		}
		return $this->pendingUserChanges;
	}
	
	public function getPendingHazardChanges(){
		if($this->pendingHazardChanges === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->pendingHazardChanges = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PENDING_HAZARD_CHANGES_RELATIONSHIP));
		}
		return $this->pendingHazardChanges;
	}
}

?>
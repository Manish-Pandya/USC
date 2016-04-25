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
			"step"						=> "integer",
			
			//GenericCrud
			"key_id"			=> "integer",
			"date_created"		=> "timestamp",
			"date_last_modified"	=> "timestamp",
			"is_active"			=> "boolean",
			"last_modified_user_id"			=> "integer",
			"created_user_id"	=> "integer",
	);
	
	private $principal_investigator_id;
	private $notification_date;
	private $due_date;
	private $completed_date;
	private $step;
	
	private $pendingRoomChanges;
	private $pendingUserChanges;
	private $pendingHazardDtoChanges;
	
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
	
	public function getNotification_date(){return $this->notification_date;}
	public function setNotification_datw($date){$this->notification_date = $date;}
	
	public function getDue_date(){return $this->due_date;}
	public function setDue_datw($date){$this->due_date = $date;}
	
	public function getCompleted_date(){return $this->completed_date;}
	public function setCompleted_date($date){$this->completed_date = $date;}
	
	public function getStep(){return $this->step;}
	public function setStep($step){$this->step = $step;}
	
	public function getPendingRoomChanges(){
		if($this->pendingRoomChanges === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO(new PendingRoomChange());
			$whereClauseGroup = new WhereClauseGroup(
					array(
							new WhereClause("parent_class", "=", "Room"),
							new WhereClause("verification_id", "=", $this->getKey_id())
					)
			);
			$this->pendingRoomChanges = $thisDAO->getAllWhere($whereClauseGroup);
		}
		return $this->pendingRoomChanges;
	}
	
	public function getPendingUserChanges(){
		if($this->pendingUserChanges === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO(new PendingUserChange());
			$whereClauseGroup = new WhereClauseGroup(
				array(
					new WhereClause("parent_class", "=", "User"),
					new WhereClause("verification_id", "=", $this->getKey_id())
				)
			);
			$this->pendingUserChanges = $thisDAO->getAllWhere($whereClauseGroup);
		}
		return $this->pendingUserChanges;
	}
	
	public function getPendingHazardDtoChanges(){
		if($this->pendingHazardChanges === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO(new PendingHazardDtoChange());
			$whereClauseGroup = new WhereClauseGroup(
				array(
					new WhereClause("parent_class", "=", "PrincipalInvestigatorHazardRoomRelation"),
					new WhereClause("verification_id", "=", $this->getKey_id())
				)
			);
			$this->pendingHazardDtoChanges = $thisDAO->getAllWhere($whereClauseGroup);
		}
		return $this->pendingHazardDtoChanges;
	}
}

?>
<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Matt Breede, GraySail LLC
 */
class PrincipalInvestigatorHazardRoomRelation extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "principal_investigator_hazard_room";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(

		//GenericCrud
		"key_id"	=> "integer",
		"hazard_id" => "status",
		"room_id"	=> "integer",
		"principal_investigator_id"	=> "integer",
		"status"	=> "text"
	);
	
	private $room_id;
	private $principal_investigator_id;
	private $hazard_id;
	private $status;

	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getRoom_id");
		$entityMaps[] = new EntityMap("eager","getPrincipal_investigator_id");
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
	public function getRoom_id(){ return $this->room_id; }
	public function setRoom_id($room_id){ $this->room_id = $room_id; }

	public function getPrincipal_investigator_id(){ return $this->principal_investigator_id; }
	public function setPrincipal_investigator_id($principal_investigator_id){ $this->principal_investigator_id = $principal_investigator_id; }

	public function getHazard_id(){return $this->hazard_id;}
	public function setHazard_id($hazard_id){$this->hazard_id = $hazard_id;}
	
	public function getStatus(){return $this->status;}
	public function setStatus($status){$this->status = $status;}
}
?>
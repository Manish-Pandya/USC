<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class PrincipalInvestigatorRoomRelation extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "principal_investigator_room";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(

		//GenericCrud
		"key_id"	=> "integer",
		"room_id"	=> "integer",
		"principal_investigator_id"	=> "integer",
							);
	private $room_id;
	private $principal_investigator_id;

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


}
?>
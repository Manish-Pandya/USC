<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Hazard_room_relation extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "hazard_room";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(

		//GenericCrud
		"room_id"			=> "integer",
		"hazard_id"         => "integer",
		"equipment_serial_number"	=> "text"
							);
	private $room_id;
	private $hazard_id;
	private $equipment_serial_number;


	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getRoom_id");
		$entityMaps[] = new EntityMap("eager","getHazard_id");
		$entityMaps[] = new EntityMap("eager","getEquipment_serial_number");
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

	public function getHazard_id(){ return $this->hazard_id; }
	public function setHazard_id($hazard_id){ $this->hazard_id = $hazard_id; }

	public function getEquipment_serial_number(){ return $this->equipment_serial_number; }
	public function setEquipment_serial_number($equipment_serial_number){ $this->equipment_serial_number = $equipment_serial_number; }



}
?>
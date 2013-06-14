<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Building {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "erasmus_building";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//rooms is a relationship
	);
	
	/** Array of Room entities contained within this Building */
	private $rooms;
	
	public function __construct(){

	}
	
	public function getRooms(){ return $this->rooms; }
	public function setRooms($rooms){ $this->rooms = $rooms; }
	
}
?>
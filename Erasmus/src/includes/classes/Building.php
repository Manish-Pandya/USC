<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Building extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "building";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//rooms is a relationship
	);
	
	/** Name of Building */
	private $name;
	
	/** Array of Room entities contained within this Building */
	private $rooms;
	
	public function __construct(){

	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }
	
	public function getRooms(){ return $this->rooms; }
	public function setRooms($rooms){ $this->rooms = $rooms; }
	
}
?>
<?php

include_once 'GenericCrud.php';
include_once 'Hazard.php';

/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Room extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "erasmus_room";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"key_id"	=> "integer",
		"active"	=> "bolean",
		"name"		=> "text",
	);
	
	private $key_id;
	private $active;
	private $name;
	
	/** Reference to the Building entity that contains this Room */
	private $building;
	
	/** Array of PricipalInvestigator entities that manage this Room */
	private $principalInvestigators;
	
	/** Array of Hazard entities contained in this Room */
	private $hazards;
	
	public function __construct(){
	
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	// Accessors / Mutators
	public function get_key_id(){ return $this->key_id; }
	public function set_key_id($key_id){ $this->key_id = $key_id; }
	
	public function get_active(){ return $this->active; }
	public function set_active($active){ $this->active = $active; }
	
	public function get_name(){ return $this->name; }
	public function set_name($name){ $this->name = $name; }
	
}
?>
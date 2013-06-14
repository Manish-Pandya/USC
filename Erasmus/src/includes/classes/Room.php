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
		"active"	=> "bolean",
		"name"		=> "text",
	);
	
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
	public function getActive(){ return $this->active; }
	public function setActive($active){ $this->active = $active; }
	
	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }
	
}
?>
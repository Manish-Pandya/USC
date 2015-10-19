<?php

include_once 'GenericCrud.php';

/**
 * 
 * @author Perry Cate, GraySail LLC
 */

class Isotope extends GenericCrud {
	
	/** Name of the DB Tabe */
	protected static $TABLE_NAME = "isotope";
	
	/** Key/value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"					=> "text",
		"half_life"				=> "float",
		"emitter_type"			=> "text",
		
		//GenericCrud
		"key_id"				=> "integer",
		"is_active"				=> "boolean",
		"date_created"			=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"last_modified_user_id"	=> "integer",
		"created_user_id"		=> "integer"
	);
	
	/** String containing the name of this isotope */
	private $name;
	
	/** Float containing the half life of of this isotope */
	private $half_life;
	
	/** String containing type of emmitter this isotope produces (Alpha, Beta, or Gamma) */
	private $emitter_type;
	
	
	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}
	
	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	// Accessors / Mutators
	public function getName() { return $this->name; }
	public function setName($newName) {$this->name = $newName; }
	
	public function getHalf_life() { return $this->half_life; }
	public function setHalf_life($newHalfLife) { $this->half_life = $newHalfLife; }
	
	public function getEmitter_type() { return $this->emitter_type; }
	public function setEmitter_type($newEmitterType) { $this->emitter_type = $newEmitterType; }
	
}

?>
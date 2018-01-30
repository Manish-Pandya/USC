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
		"display_half_life"		=> "float",
		"unit"					=> "text",
		"auth_limit"            => "float",
        "is_mass"               => "boolean",
        "license_line_item"     => "string",
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

	/** Sting containing the unit of time the half-life of this isotope is stored in */
	private $unit;

	/** Float containing half life of this isotope to be displayed by user selected unit */
	private $display_half_life;

    private $auth_limit;
    private $is_mass;

    private $license_line_item;

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

	public function getUnit(){return $this->unit;}
	public function setUnit($unit){$this->unit = $unit;}

	public function getDisplay_half_life(){return $this->display_half_life;}
	public function setDisplay_half_life($life){$this->display_half_life = $life;}

	public function getEmitter_type() { return $this->emitter_type; }
	public function setEmitter_type($newEmitterType) { $this->emitter_type = $newEmitterType; }

    public function getAuth_limit(){
		return $this->auth_limit;
	}
	public function setAuth_Limit($limit){
		$this->auth_limit = $limit;
	}

	public function getIs_mass(){
		return (boolean) $this->is_mass;
	}
	public function setIs_mass($is_mass){
		$this->is_mass = $is_mass;
	}

    public function getLicense_line_item(){
		return (string) $this->license_line_item;
	}

	public function setLicense_line_item($license_line_item){
		$this->license_line_item = $license_line_item;
	}
}

?>
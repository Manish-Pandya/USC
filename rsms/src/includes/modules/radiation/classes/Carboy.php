<?php

/**
 *
 *
 *
 * @author Perry Cate, GraySail LLC
 */
class Carboy extends RadCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "carboy";

	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"commission_date"				=> "timestamp",
		"retirement_date"				=> "timestamp",
		"carboy_number"					=> "text",

		//GenericCrud
		"key_id"						=> "integer",
		"is_active"						=> "boolean",
		"date_last_modified"			=> "timestamp",
		"last_modified_user_id"			=> "integer",
		"date_created"					=> "timestamp",
		"created_user_id"				=> "integer"
	);

	public function __construct() {

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getCarboy_use_cycles");
		return $entityMaps;
	}
	
	/** Relationships */
	protected static $CARBOY_USE_CYCLES_RELATIONSHIP = array(
			"className" => "CarboyUseCycle",
			"tableName" => "carboy_use_cycle",
			"keyName"	=> "key_id",
			"foreignKeyName"	=> "carboy_id"
	);
	//access information

	/** timestamp with the date this carboy was made */
	private $commission_date;

	private $carboy_number;

	/** timestamp with the date this carboy should be thrown away */
	private $retirement_date;

	/** all use cycles this carboy has ever had **/
	private $carboy_use_cycles;
	
	/** the carboy use cycle this carboy is currently in */
	private $current_carboy_use_cycle;

	
	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}

	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors / Mutators
	public function getCommission_date() { return $this->commission_date; }
	public function setCommission_date($newDate) { $this->commission_date = $newDate; }

	public function getRetirement_date() { return $this->retirement_date; }
	public function setRetirement_date($newDate) { $this->retirement_date = $newDate; }


	public function getCarboy_number()
	{
	    return $this->carboy_number;
	}

	public function setCarboy_number($carboy_number)
	{
	    $this->carboy_number = $carboy_number;
	}

	public function getCarboy_use_cycles(){
		if($this->carboy_use_cycles === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->carboy_use_cycles = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$CARBOY_USE_CYCLES_RELATIONSHIP));
		}
		return $this->carboy_use_cycles;
	}
	public function setCarboy_use_cycles($cycles)
	{
	    $this->carboy_use_cycles = $cycles;
	}
	
	public function getCurrent_carboy_use_cycle(){
		$cycles = $this->getCarboy_use_cycles();

		// Current cycle is the most recent one
		// TODO: Order by date?
		$cycleCount = count($cycles);
		if( $cycleCount > 0 ){
			// Return last one
			return $cycles[$cycleCount - 1];
		}

		return null;
	}

	public function __toString(){
		return '[' . get_class($this)
			. " key_id=" . $this->getKey_Id()
			. ($this->is_active ? '' : ' is_active=false')
			. " label=$this->carboy_number"
			. "]";
	}
}
?>
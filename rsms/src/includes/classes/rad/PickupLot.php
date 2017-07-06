<?php

/**
 * PickupLot short summary.
 *
 * PickupLot description.
 *
 * @version 1.0
 * @author Matt Breeden





 */
class PickupLot extends RadCrud
{

	/** Name of the DB Table */
	protected static $TABLE_NAME = "pickup_lot";

    protected static $COLUMN_NAMES_AND_TYPES = array(
            "pickup_id"					=> "integer",
            "isotope_id"				=> "integer",
            "curie_level"				=> "float",
            "waste_type_id"		        => "integer",
            "waste_bag_id"              => "integer",
            "drum_id"              => "integer",

            //GenericCrud
            "key_id"						=> "integer",
            "is_active"						=> "boolean",
            "date_last_modified"			=> "timestamp",
            "last_modified_user_id"			=> "integer",
            "date_created"					=> "timestamp",
            "created_user_id"				=> "integer"
        );

    // Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}

	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}

    private $waste_bag_id;
    private $waste_type_id;
    private $isotope_id;
    private $curie_level;
    private $drum_id;
    private $pickup_id;

    public function getWaste_bag_id(){
		return $this->waste_bag_id;
	}
	public function setWaste_bag_id($id){
		$this->waste_bag_id = $id;
	}

    public function getWaste_type_id(){
		return $this->waste_type_id;
	}
	public function setWaste_type_id($id){
		$this->waste_type_id = $id;
	}

	public function getIsotope_id(){
		return $this->isotope_id;
	}
	public function setIsotope_id($isotope_id){
		$this->isotope_id = $isotope_id;
	}

	public function getCurie_level(){
		return $this->curie_level;
	}
	public function setCurie_level($curie_level){
		$this->curie_level = $curie_level;
	}

	public function getDrum_id(){
		return $this->drum_id;
	}
	public function setDrum_id($drum_id){
		$this->drum_id = $drum_id;
	}

    public function getPickup_id(){return $this->pickup_id;}
    public function setPickup_id($id){$this->pickup_id = $id;}
}
<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class ParcelWipeTest extends RadCrud {
	/** Name of the DB Table */
	protected static $TABLE_NAME = "parcel_wipe_test";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
			"parcel_id"						=> "integer",
			"transportation_index"			=> "float",
			"box_background"				=> "float",
			"one_meter_background"			=> "float",
	
			//GenericCrud
			"key_id"						=> "integer",
			"is_active"						=> "boolean",
			"date_last_modified"			=> "timestamp",
			"last_modified_user_id"			=> "integer",
			"date_created"					=> "timestamp",
			"created_user_id"				=> "integer"
	);
	
	/** Relationships */
	public static $PARCEL_WIPE_RELATIONSHIP = array(
			"className" => "ParcelWipe",
			"tableName" => "parcel_wipe",
			"keyName"   => "key_id",
			"foreignKeyName" => "parcel_wipe_test_id"
	);
	
	public function __construct(){
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getParcel");
		$this->setEntityMaps($entityMaps);
	}
	
	//access information
	// Required for GenericCrud
	public function getTableName() {
		return self::$TABLE_NAME;
	}
	
	public function getColumnData() {
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	// Accessors / Mutators
	private $parcel_id;
	private $parcel;
	
	private $parcel_wipes;
	
	private $transportation_index;
	private $box_background;
	private $one_meter_background;
	
	public function getParcel_id(){return $this->parcel_id;}
	public function setParcel_id($id){$this->parcel_id = $id;}
	
	public function getParcel(){
		$parcelDAO = new GenericDAO(new Parcel());
		$this->parcel = $parcelDAO->getById($this->parcel_id);
		return $this->parcel;
	}
	
	public function getParcel_wipes() {
		if($this->parcel_wipes == null && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->parcel_wipes = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PARCEL_WIPE_RELATIONSHIP));
		}
		return $this->parcel_wipes;
	}
	public function getTransportation_index() {return $this->transportation_index;}
	public function setTransportation_index($transportation_index) {	$this->transportation_index = $transportation_index;}
	
	public function getBox_background() {return $this->box_background;}
	public function setBox_background($box_background) {$this->box_background = $box_background;}
	
	public function getOne_meter_background() {return $this->one_meter_background;}
	public function setOne_meter_background($one_meter_background) {$this->one_meter_background = $one_meter_background;}
	
}
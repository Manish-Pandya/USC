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
	
	//access information
	
	private $parcel_id;
	private $parcel;
	
	public function getParcel_id(){return $this->parcel_id;}
	public function setParcel_id($id){$this->parcel_id = $id;}
	
	public function getParcel(){
		$parcelDAO = new GenericDAO(new Parcel());
		$this->parcel = $parcelDAO->getById($this->parcel_id);
		return $this->parcel;
	}
}
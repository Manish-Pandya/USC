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
		"name"				=> "text",
		
		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer"
													);

	/** Relationships */
	protected static $ROOMS_RELATIONSHIP = array(
			"className"	=>	"Room",
			"tableName"	=>	"room",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"building_id"
	);
	
	
	/** Name of Building */
	private $name;
	
	/** Array of Room entities contained within this Building */
	private $rooms;
	
	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getRooms");
		$this->setEntityMaps($entityMaps);
		
		
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
	
	public function getRooms(){ 
		if($this->rooms == null) {
			$buildingDAO = new GenericDAO($this);
			$this->users = $buildingDAO->getRelatedItemsById($this->getKey_Id(), DataRelationShip::fromArray(self::$ROOMS_RELATIONSHIP));
		}
		return $this->users;
	}
	public function setRooms($rooms){ $this->rooms = $rooms; }
	
}
?>
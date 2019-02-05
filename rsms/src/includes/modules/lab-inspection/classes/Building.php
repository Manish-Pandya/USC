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
        "alias"             => "text",
		"campus_id"			=> 'integer',
		"physical_address"  => "text",

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"   => "integer"
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

	/** The key_id of this Building's Campus **/
	private $campus_id;

	/** This Building's Campus  **/
	private $campus;

    private $alias;

	/** This Building's Address  **/
	private $physical_address;

	public function __construct(){

		
    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getRooms");
		$entityMaps[] = new EntityMap("eager","getCampus");
		$entityMaps[] = new EntityMap("eager","getCampus_id");
		$entityMaps[] = new EntityMap("eager","getPhysical_address");
		return $entityMaps;
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


	public function getAlias(){ return $this->alias; }
	public function setAlias($alias){ $this->alias = $alias; }

	public function getRooms(){
		if($this->rooms == null) {
			$buildingDAO = new GenericDAO($this);
			$this->rooms = $buildingDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$ROOMS_RELATIONSHIP));
		}
		return $this->rooms;
	}
	public function setRooms($rooms){ $this->rooms = $rooms; }

	public function getCampus(){
		if($this->campus == null) {
			$campusDao = new GenericDAO(new Campus());
			$this->campus = $campusDao->getById($this->campus_id);
		}
		return $this->campus;
	}


	public function getCampus_id()
	{
	    return $this->campus_id;
	}

	public function setCampus_id($campus_id)
	{
	    $this->campus_id = $campus_id;
	}

	public function setCampus($campus)
	{
	    $this->campus = $campus;
	}

	public function getPhysical_address()
	{
	    return $this->physical_address;
	}

	public function setPhysical_address($physical_address)
	{
	    $this->physical_address = $physical_address;
	}
}
?>
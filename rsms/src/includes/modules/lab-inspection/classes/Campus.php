<?php

/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Campus extends GenericCrud {

	/** Name of the DB Table */
	public const TABLE_NAME = 'campus';
	protected static $TABLE_NAME = self::TABLE_NAME;

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"		=> "text",
		"alias"		=> "text",

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer"
	);


	protected static $BUILDINGS_RELATIONSHIP = array(
			"className"	=>	"Building",
			"tableName"	=>	"building",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"campus_id"
	);


	private $name;
	private $alias;

	private $buildings;

	public function __construct(){

		
    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getBuildings");
		return $entityMaps;

	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors / Mutators
	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }

	public function getAlias(){ return $this->alias; }
	public function setAlias($alias){ $this->alias = $alias; }

	public function getBuildings(){
		if($this->buildings == null) {
			$thisDAO = new GenericDAO($this);
			$this->buildings = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$BUILDINGS_RELATIONSHIP));
		}
		return $this->buildings;
	}
	public function setBuildings($buildings){
		$this->buildings = $buildings;
	}


}
?>
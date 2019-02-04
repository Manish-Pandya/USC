<?php
/**
 * EmailDependencyObj short summary.
 *
 * EmailDependencyObj description.
 *
 * @version 1.0
 * @author intoxopox
 */

class EmailDependencyObj extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "email_dependency_obj";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"dependency_entity_id"		=> "integer",
		//GenericCrud
		"key_id"				=> "integer",
		"date_created"			=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"				=> "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"		=> "integer"
		);

	/**
	 * Id of entity containing data to construct recipient list and other EmailGen macro swap data.
	 * @var mixed
	 */
	protected $dependency_entity_id;

	/**
	 * Summary of __construct
	 */
	public function __construct() {

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getDependency_entity_id");
		$this->setEntityMaps($entityMaps);
	}

	// Required for GenericCrud //
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors //
	public function getDependency_entity_id(){ return $this->dependency_entity_id; }
	public function setDependency_entity_id($dependency_entity_id){ $this->dependency_entity_id = $dependency_entity_id; }

	/**
	 * Runs through the corpus string and replaces all string parts matching macroMap keys with their corrisponding value.
	 * @return mixed
	 */
	public function buildRecipients() {

	}

}
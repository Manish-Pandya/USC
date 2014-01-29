<?php
/**
 * Wrapper class that provides Data Access Operations around an entity.
 * Instances of DAO should be instantiated with a GenericCrud object
 * that will be used as a basis for operations, as GenericCrud classes
 * provide descriptors for the entity's data table.
 * 
 * @author GraySail
 * @author Hoke
 * @author Matt
 * @author Mitch
 */

class GenericDAO {
	
	/** Logger */
	protected $LOG;
	
	/** Prefix string used in log messages */
	protected $logprefix;
	
	/** Object used as a model from which to obtain class and table data */
	protected $modelObject;
	
	/** Class name of the model object */
	protected $modelClassName;
	
	/**
	 * Constructs a new Data Access Object for the type of the given object.
	 * @param GenericCrud $model_object
	 */
	public function __construct(GenericCrud $model_object){
		$this->LOG = Logger::getLogger( __CLASS__ );
		
		$this->modelObject = $model_object;
		$this->modelClassName = get_class( $model_object );
		
		$this->logprefix = "[$this->modelClassName" . "DAO]";
	}
	
	/**
	 * @return boolean True if the associated table exists
	 */
	public function doesTableExist(){
		$tableName = $this->modelObject->getTableName();
		$result = mysql_query("SHOW TABLES LIKE '$tableName'");
		$tableExists = mysql_num_rows($result) > 0;
		return $tableExists;
	}
	
	//TODO: Move to ErrorHandler?
	public function handleError($pearResult){
		$message = $pearResult->getMessage();
		$info = $pearResult->getDebugInfo();
		
		$this->LOG->error("$message: $info");
		
		die("----PEAR Error----\nMessage: $message\nDebugInfo: $info");
	}
	
	/**
	 * Populates this entity with the data for the entity with the given ID
	 *
	 * @param unknown $id
	 * @return GenericCrud
	 */
	function getById($id){
		$this->LOG->debug("$this->logprefix Looking up entity with keyid $id");
		
		// Get the db connection
		global $mdb2;
	
		//Query the table by key_id
		$result =& $mdb2->query('SELECT * FROM ' . $this->modelObject->getTableName() . ' WHERE key_id = ' . $id);
		if (PEAR::isError($result)) {
			$this->handleError($result);
		}
	
		//Get the first row of query results (should be only one row)
		$record = $result->fetchRow();
		
		//Ensure there is a record
		if( $record !== NULL ){
			//Build an object to return
			$object = new $this->modelClassName();
			
			//Iterate through the columns and make this object match the values from the database
			$object->populateFromDbRecord($record);
		}
		else{
			//No Record; return NULL
			$object = NULL;
		}
		
		return $object;
	}
	
	/**
	 * Retrieves a list of all entities of this type.
	 *
	 * @return Array of entities
	 */
	function getAll( $sortColumn = NULL, $sortDescending = FALSE ){
		$this->LOG->debug("$this->logprefix Looking up all entities" . ($sortColumn == NULL ? '' : ", sorted by $sortColumn"));
		
		// Get the db connection
		global $mdb2;
		$resultList = array();
	
		// Build query
		$query_string = 'SELECT * FROM ' . $this->modelObject->getTableName();
		if( $sortColumn != NULL ){
			//Default to ascending, which requires no keyword
			$sortDirection = '';
			
			//Check for Descending sort
			if ( $sortDescending ){
				$sortDirection = 'DESC';
			}
			
			//Sort is specified; add ORDER BY clause
			$query_string .= " ORDER BY $sortColumn $sortDirection";
		}
		
		//Query the table by key_id
		$result =& $mdb2->query( $query_string );
		if (PEAR::isError($result)) {
			$this->handleError($result);
		}
	
		//Iterate the rows and push to result list
		while ($record = $result->fetchRow()){
	
			//Create a new instance and sync it for each row
			$item = new $this->modelClassName();
			$item->populateFromDbRecord( $record );
	
			// Add the results to an array
			array_push($resultList, $item);
		}
	
		return $resultList;
	}
	
	/**
	 * Retrieves all entities of this type, ordered by the given field
	 *
	 * @param unknown $sortColumn
	 * @return Array:
	 */
	function getAllSorted( $sortColumn, $sortDescending = FALSE ){
		return $this->getAll( $sortColumn, $sortDescending );
	}
	
	/**
	 * Commits the values of this entity to the database
	 * 
	 * @param GenericCrud $object: Object to save. If null, the model object will be used instead
	 * @return GenericCrud
	 */
	function save(GenericCrud $object = NULL){
		$this->LOG->debug("$this->logprefix Saving entity");
		
		//Make sure we have an object to save
		if( $object == NULL ){
			$object = $this->modelObject;
		}
		//If $object is given, make sure it's the right type
		else if( get_class($object) != $this->modelClassName ){
			// we have a problem!
			$this->LOG->error("Attempting to save entity of class " . get_class($object) . ", which does not match model object class of $this->modelClassName");
		
			//NULL return indicates error
			return NULL;
		}
		//else use $object as-is!
		
		// Get the db connection
		global $mdb2;
	
		// Initiate an array that contains the values to be saved
		$dataClause = array();
		$dataTypesArray = array();
		foreach ($object->getColumnData() as $columnName => $type){
			$getter = "get$columnName";
			$getter[3] = strtoupper($getter[3]);
			$dataClause[$columnName] = $object->$getter();
			
			$dataTypesArray[] = $type;
		}
		
		$table = $object->getTableName();
	
		// Check to see if this item has a key_id
		//  If it does, we assume it's an existing record and issue an UPDATE
		if ( $object->hasPrimaryKeyValue() ) {
			$this->LOG->debug("$this->logprefix Updating existing entity with keyid " . $object->getKey_Id());
			
			$affectedRow = $mdb2->autoExecute(
				$table,
				$dataClause,
				DATABASE_AUTOQUERY_UPDATE,
				'key_id = ' . $mdb2->quote($object->getKey_Id(), 'integer'),
				$dataTypesArray
			);
			
			if (PEAR::isError($affectedRow)) {
				$this->handleError($affectedRow);
			}
		}
		// Otherwise, issue an INSERT
		else {
			$this->LOG->debug("$this->logprefix Inserting new entity");
			$affectedRow = $mdb2->autoExecute(
				$table,
				$dataClause,
				DATABASE_AUTOQUERY_INSERT,
				null,
				$dataTypesArray
			);
	
			if (PEAR::isError($affectedRow)) {
				$this->handleError($affectedRow);
			}
			
			// since this is a new record, get the new key_id issued by the database and add it to this object.
			$id = $mdb2->getOne( "SELECT LAST_INSERT_ID() FROM " . $table );
			
			if (PEAR::isError($id)) {
				$this->handleError($id);
			}
			
			$object->setKey_Id( $id );
		}

		$this->LOG->debug("$this->logprefix Successfully updated or inserted entity with key_id=" . $object->getKey_Id());
	
		// return the updated object
		return $object;
	}
	
	/**
	 * Retrieves a list of related items for the entity of this type
	 * with the givevn ID.
	 *
	 * @param unknown $id
	 * @param DataRelationship $relationship
	 * @return Array:
	 */
	function getRelatedItemsById($id, DataRelationship $relationship){
		$this->LOG->debug("$this->logprefix Retrieving related items for " . get_class($this->modelObject) . " entity with id=$id");
		
		// Get the db connection
		global $mdb2;
	
		//print_r($relationship);
		// get the relationship parameters needed to build the query
		$className		= $relationship->getClassName();
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();
	
		// initialize the return array
		$resultList = array();
	
		//Query the table by date range
		$result =& $mdb2->query("SELECT " . $foreignKeyName . " FROM " . $tableName . " WHERE " . $keyName . " = " . $id);
		if (PEAR::isError($result)) {
			$this->handleError($result);
		}
	
		//Iterate the rows
		while ($record = $result->fetchRow()){
			//Create a new instance and sync it for each row
			$item = new $className();
			$itemDao = new GenericDAO( $item );
			$item = $itemDao->getById( $record->$foreignKeyName );
	
			// Add the results to an array
			array_push($resultList, $item);
		}
	
		return $resultList;
	}
	
	/**
	 * Save a new related item with the given values described by the given DataRelationship
	 * 
	 * @param unknown $key_id
	 * @param unknown $foreignKey_id
	 * @param DataRelationship $relationship
	 * @return unknown
	 */
	function addRelatedItems($key_id, $foreignKey_id, DataRelationship $relationship) {
		$this->LOG->debug("$this->logprefix Inserting new related item for entity with id=$id");
		
		// Get the db connection
		global $mdb2;
		
		//print_r($relationship);
		// get the relationship parameters needed to build the query
		$className		= $relationship->getClassName();
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();
		
		// Initiate an array that contains the values to be saved
		$dataClause = array();
		$dataClause[$keyName] = $key_id;
		$dataClause[$foreignKeyName] = $foreignKey_id;
		
		$types = array("integer", "integer");
		
		$affectedRow = $mdb2->autoExecute($tableName, $dataClause, DATABASE_AUTOQUERY_INSERT, null, $types);
		
		if (PEAR::isError($affectedRow)) {
			$this->handleError($affectedRow);
		}
		
		// since this is a new record, get the new key_id issued by the database and add it to this object.
		$id = $mdb2->getOne( "SELECT LAST_INSERT_ID()");
		if (PEAR::isError($id)) {
			$this->handleError($id);
		}
		
		return $id;
	}
	
	/**
	 * Delete a related item with the given values described by the given DataRelationship
	 * 
	 * @param unknown $key_id
	 * @param unknown $foreignKey_id
	 * @param DataRelationship $relationship
	 * @return boolean
	 */
	function removeRelatedItems($key_id, $foreignKey_id, DataRelationship $relationship) {
		$this->LOG->debug("$this->logprefix Deleting related item (foreign key = $foreignKey_id) for entity with id=$id");
		
		// Get the db connection
		global $mdb2;
	
		//print_r($relationship);
		// get the relationship parameters needed to build the query
		$className		= $relationship->getClassName();
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();
	
		$returnFlag = false;
	
		//Remove
		$result =& $mdb2->query('DELETE FROM ' . $tableName . ' WHERE ' . $keyName . ' = ' . $key_id . ' AND ' . $foreignKeyName . ' = ' . $foreignKey_id);
		
		if (PEAR::isError($result)) {
			$this->handleError($affectedRow);
			//FIXME: Does this actually get returned after die()?
			return false;
		} else {
			$returnFlag = true;
		}
	
		return $returnFlag;
	}
}
?>
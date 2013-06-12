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
class DAO {
	
	private $LOG;
	private $logprefix;
	public $baseObject;
	
	/**
	 * Constructs a new Data Access Object for the type of the given object.
	 * @param GenericCrud $obj
	 */
	public function __construct(GenericCrud $obj){
		$this->baseObject = $obj;
		$this->LOG = Logger::getLogger( __CLASS__ );
		$this->logprefix = '[' . get_class( $this->baseObject ) . ']';
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
		$result =& $mdb2->query('SELECT * FROM ' . $this->baseObject->getTableName() . ' WHERE key_id = ' . $id);
		if (PEAR::isError($result)) {
			die($result->getMessage());
		}
	
		//Get the first row of query results (should be only one row)
		$record = $result->fetchRow();
	
		//Iterate through the columns and make this object match the values from the database
		$this->baseObject->populateFromDbRecord($record);
	
		return $this->baseObject;
	}
	
	/**
	 * Retrieves a list of all entities of this type.
	 *
	 * @return Array of entities
	 */
	function getAll(){
		$this->LOG->debug("$this->logprefix Looking up all entities");
		
		// Get the db connection
		global $mdb2;
		$className = get_class( $this->baseObject );
		$resultList = array();
	
		//Query the table by key_id
		$result =& $mdb2->query('SELECT * FROM ' . $this->baseObject->getTableName());
		if (PEAR::isError($result)) {
			die($result->getMessage());
		}
	
		//Iterate the rows and push to result list
		while ($record = $result->fetchRow()){
	
			//Create a new instance and sync it for each row
			$item = new $className();
			$item->populateFromDbRecord( $record );
	
			// Add the results to an array
			array_push($resultList, $item);
		}
	
		return $resultList;
	}
	
	/**
	 * Retrieves all entities of this type, ordered by the given field
	 *
	 * @param unknown $col
	 * @return Array:
	 */
	function getAllSorted($col){
		$this->LOG->debug("$this->logprefix Looking up all entities, sorted by '$col'");
		
		// Get the db connection
		global $mdb2;
		$className = get_class($this->baseObject);
		$resultList = array();
	
		//Query the table by key_id
		$result =& $mdb2->query('SELECT * FROM ' . $this->baseObject->getTableName() . ' ORDER BY ' . $col);
		if (PEAR::isError($result)) {
			die($result->getMessage());
		}
		//Iterate the rows
	
		while ($record = $result->fetchRow()){
	
			//Create a new instance and sync it for each row
			$item = new $className();
			$item->populateFromDbRecord( $record );
	
			// Add the results to an array
			array_push($resultList, $item);
		}
	
		return $resultList;
	}
	
	/**
	 * Commits the values of this entity to the database
	 * @return GenericCrud
	 */
	function save(){
		$this->LOG->debug("$this->logprefix Saving entity");
		
		// Get the db connection
		global $mdb2;
	
		// Initiate an array that contains the values to be saved
		$dataClause = array();
		$dataTypesArray = array();
		foreach ($this->baseObject->getColumnData() as $columnName => $type){
			$getter = "get$columnName";
			$getter[3] = strtoupper($getter[3]);
			$dataClause[$columnName] = $this->$getter();
			
			$dataTypesArray[] = $type;
		}
		
		$table = $this->baseObject->getTableName();
	
		// Check to see if this item has a key_id
		//  If it does, we assume it's an existing record and issue an UPDATE
		if ( $this->baseObject->hasPrimaryKeyValue() ) {
			$this->LOG->debug("$this->logprefix Updating existing entity with keyid $id");
			
			$affectedRow = $mdb2->autoExecute(
				$table,
				$dataClause,
				DB_AUTOQUERY_UPDATE,
				'key_id = ' . $mdb2->quote($this->baseObject->getKeyId(), 'integer'),
				$dataTypesArray
			);
			
			if (PEAR::isError($affectedRow)) {
				die($affectedRow->getDebugInfo());
			}
		}
		// Otherwise, issue an INSERT
		else {
			$this->LOG->debug("$this->logprefix Inserting new entity");
			$affectedRow = $mdb2->autoExecute(
				$table,
				$dataClause,
				DB_AUTOQUERY_INSERT,
				null,
				$dataTypesArray
			);
	
			if (PEAR::isError($affectedRow)) {
				die($affectedRow->getDebugInfo());
			}
			
			// since this is a new record, get the new key_id issued by the database and add it to this object.
			$id = $mdb2->getOne( "SELECT LAST_INSERT_ID() FROM " . $table );
			
			if (PEAR::isError($id)) {
				die($id->getMessage());
			}
			
			$this->baseObject->setKeyId( $id );
		}

		$this->LOG->debug("$this->logprefix Successfully updated or inserted entity with key_id=" . $this->baseObject->getKeyId());
	
		// return the updated object
		return $this->baseObject;
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
		$this->LOG->debug("$this->logprefix Retrieving related items for entity with id=$id");
		
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
			die($result->getMessage());
		}
	
		//Iterate the rows
		while ($record = $result->fetchRow()){
			//Create a new instance and sync it for each row
			$item = new $className();
			$itemDao = new DAO( $item );
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
		
		$affectedRow = $mdb2->autoExecute($tableName, $dataClause, DB_AUTOQUERY_INSERT, null, $types);
		
		if (PEAR::isError($affectedRow)) {
			die($affectedRow->getMessage());
		}
		
		// since this is a new record, get the new key_id issued by the database and add it to this object.
		$id = $mdb2->getOne( "SELECT LAST_INSERT_ID()");
		if (PEAR::isError($id)) {
			die($id->getMessage());
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
			die($result->getDebugInfo());
			return false;
		} else {
			$returnFlag = true;
		}
	
		return $returnFlag;
	}
}
?>
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
	
	public static $AUTO_SET_FIELDS = array(
		'getDate_created',
		'getDate_last_modified',
		'getIs_active',
		'getLast_modified_user_id'
	);
	
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
			global $db;
			
			//Prepare to query the table by key_id
			$stmt = $db->prepare('SELECT * FROM ' . $this->modelObject->getTableName() . ' WHERE key_id = ?');
			$stmt->bindParam(1,$id,PDO::PARAM_INT);
			$stmt->setFetchMode(PDO::FETCH_CLASS, $this->modelClassName);			// Query the db and return one of $this type of object
			if ($stmt->execute()) {
				$result = $stmt->fetch();
			// ... otherwise, die and echo the db error
			} else {
				$error = $stmt->errorInfo();
				die($error[2]);
			}

			return $result;
	}
	
	/**
	 * Deletes the entity with the given ID
	 *
	 * @param unknown $id
	 * @return GenericCrud
	 */
	function deleteById($id){
		$this->LOG->debug("$this->logprefix Deleting entity with key_id $id");
		
			// Get the db connection
			global $db;
			
			//Prepare to delete from the table by key_id
			$stmt = $db->prepare('DELETE FROM ' . $this->modelObject->getTableName() . ' WHERE key_id = ?');
			$stmt->bindParam(1,$id,PDO::PARAM_INT);
			if ($stmt->execute()) {
			// ... otherwise, die and echo the db error
			} else {
				$error = $stmt->errorInfo();
				die($error[2]);
			}
			return $true;
	}
	
	/**
	 * Retrieves a list of all entities of this type.
	 *
	 * @return Array of entities
	 */
	function getAll( $sortColumn = NULL, $sortDescending = FALSE ){

		$this->LOG->debug("$this->logprefix Looking up all entities" . ($sortColumn == NULL ? '' : ", sorted by $sortColumn"));
		
		// Get the db connection
		global $db;
		$className = get_class($this);
		
		//Prepare to query all from the table
		$stmt = $db->prepare('SELECT * FROM ' . $this->modelObject->getTableName() . ' ' . ($sortColumn == NULL ? '' : " ORDER BY $sortColumn " . ($sortDescending ? 'DESC' : 'ASC')) );
			
		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClassName);
			// ... otherwise, die and echo the db error
		} else {
			$error = $stmt->errorInfo();
			die($error[2]);
		}
		
		return $result;
		
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
			global $db;
			//print_r($db);

			// Check to see if this item has a key_id
			//  If it does, we assume it's an existing record and issue an UPDATE
			if ($object->getKey_id() != null) {
				
			    $_SESSION["DEBUG"] = "Calling db update...";

				$stmt = $this->createUpdateStatement($db,$object);
				$stmt = $this->bindColumns($stmt,$object);
				$stmt->execute();
			// Otherwise, issue an INSERT
			} else {
		    	$_SESSION["DEBUG"] = "Calling db insert...";
				 //echo  "Calling db insert...";

				$stmt = $this->createInsertStatement($db,$object);
			   	$stmt = $this->bindColumns($stmt,$object);
				$stmt->execute();
				// since this is a new record, get the new key_id issued by the database and add it to this object.
				$object->setKey_id($db->lastInsertId());
			}

		

		$this->LOG->debug("$this->logprefix Successfully updated or inserted entity with key_id=" . $object->getKey_Id());
				
		// Re-load the whole record so that updated Date fields (and any field auto-set by DB) are updated
		$this->LOG->debug("$this->logprefix Reloading updated/inserted entity with key_id=" . $object->getKey_Id() );
		$object = $this->getById( $object->getKey_Id() );
	
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
		global $db;
	
		//print_r($relationship);
		// get the relationship parameters needed to build the query
		$className		= $relationship->getClassName();
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();
	
		//Query the related table using the foreign key
		$queryString = "SELECT " . $keyName . " FROM " . $tableName . " WHERE " . $foreignKeyName . " = ?" ;
		$this->LOG->debug($queryString . " [? == $id] ...");
		$stmt = $db->prepare($queryString);
		$stmt->bindParam(1,$id,PDO::PARAM_INT);

		// Query the db and return an array of key_ids for matching records
		if ($stmt->execute() ) {
			$keys = $stmt->fetchAll();
			$this->LOG->debug( "... returned " . count($keys) . " related records.");
		// ... otherwise, die and echo the db error
		} else {
			$error = $stmt->errorInfo();
			die($error[2]);
		}
		
		$resultList = array();
		
		//Iterate the rows
		foreach ($keys as $record){
			//Create a new instance and sync it for each row
			$item = new $className();
			$itemDao = new GenericDAO( $item );
			$item = $itemDao->getById( $record[$keyName] );
	
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
		$this->LOG->debug("$this->logprefix Inserting new related item for entity with id=$foreignKey_id");
		
		// Get the db connection
		global $db;
		
		//print_r($relationship);
		// get the relationship parameters needed to build the query
		$className		= $relationship->getClassName();
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();
		
		$sql = "INSERT INTO  $tableName ($foreignKeyName, $keyName) VALUES (:foreignKey_id, :key_id) ";
		
		$this->LOG->debug("Preparing insert statement [$sql]");
		
		$stmt = $db->prepare($sql);
		// Bind the params.
		$stmt->bindParam(":foreignKey_id",$foreignKey_id,PDO::PARAM_INT);
		$stmt->bindParam(":key_id",$key_id,PDO::PARAM_INT);
		
		// Insert the record and return true
		if ($stmt->execute() ) {
			$this->LOG->debug( "Inserted new related item with key_id [$key_id]");
			return true;
		// ... otherwise, die and echo the db error
		} else {
			$error = $stmt->errorInfo();
			die($error[2]);
		}
	}
	
	function removeRelatedItems($key_id, $foreignKey_id, DataRelationship $relationship) {
		$this->LOG->debug("$this->logprefix Removing related item for entity with id=$foreignKey_id");
		
		// Get the db connection
		global $db;
		
		//print_r($relationship);
		// get the relationship parameters needed to build the query
		$className		= $relationship->getClassName();
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();
		
		$sql = "DELETE FROM $tableName WHERE $foreignKeyName =  :foreignKey_id AND $keyName = :key_id";
	
		$this->LOG->debug("Preparing delete statement [$sql]");
		
		$stmt = $db->prepare($sql);
		// Bind the params.
		$stmt->bindParam(":foreignKey_id",$foreignKey_id,PDO::PARAM_INT);
		$stmt->bindParam(":key_id",$key_id,PDO::PARAM_INT);
		
		// Delete the record and return true
		if ($stmt->execute() ) {
			$this->LOG->debug( "Remove related item with key_id [$key_id]");
			return true;
		// ... otherwise, die and echo the db error
		} else {
			$error = $stmt->errorInfo();
			die($error[2]);
		}
	}
	
	function bindColumns($stmt,$object) {
		foreach ($object->getColumnData() as $key=>$value){
			if ($value == "integer") {$type = PDO::PARAM_INT;}
			if ($value == "text") {$type = PDO::PARAM_STR;}
			if ($value == "float") {$type = PDO::PARAM_INT;}
			if ($value == "boolean") {$type = PDO::PARAM_BOOL;}
			if ($value == "datetime") {$type = PDO::PARAM_STR;}
			if ($value == "timestamp") {$type = PDO::PARAM_INT;}
			
			// build the implied getter
			$key2 = $key;
			$key2[0] = strtoupper($key2[0]);
			$getter = "get" . $key2;
			
			//$this->LOG->debug("Binding $key (a $value) as PDO type $type");

			// build the binding statement.
			$stmt->bindParam(":" . $key,$object->$getter(),$type);
			//echo $col . ":" . $this->$col . " - " . $this->types[$index] . "<br/>";
		}
		return $stmt;
	}
	
	function createInsertStatement ($db,$object){
			
		$sql = "INSERT INTO " . $this->modelObject->getTableName() . " ( ";
		
		
		foreach ($object->getColumnData() as $key=>$value){
			$sql .= $key . ",";
		}
		$sql = rtrim($sql,",");
		$sql .= ") VALUES ( ";
		foreach ($object->getColumnData() as $key=>$value){
			$sql .= ":" . $key . ",";
		}
		$sql = rtrim($sql,",");
		$sql .= ")";
			
		$this->LOG->debug("Preparing insert statement [$sql]");
		
		$stmt = $db->prepare($sql);
		//var_export($stmt->queryString);
		return $stmt;
			
	}
	
	function createUpdateStatement ($db,$object){
			
		$sql = "UPDATE " . $this->modelObject->getTableName() . " SET ";
		foreach ($object->getColumnData() as $key=>$value){
			if ($key != "key_id"){
				$sql .= $key . " = :" . $key . " ,";
			}
		}
		$sql = rtrim($sql,",");
		$sql .= " WHERE key_id = :key_id";
		$this->LOG->debug("Preparing update statement [$sql]");
		$stmt = $db->prepare($sql);
		//var_export($stmt->queryString);
		return $stmt;
			
	}
	
	
}
?>
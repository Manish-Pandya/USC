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

	public function setModelObject(GenericCrud $new_model_object) {
		$this->modelObject = $new_model_object;
		$this->modelClassName = get_class($new_model_object);
		$this->logprefix = "[$this->modelClassName" . "DAO]";
	}

	/**
	 * @return boolean True if the associated table exists
	 */
	public function doesTableExist(){
		$tableName = $this->modelObject->getTableName();
		global $db;
		$result = $db->query("SHOW TABLES LIKE '$tableName'");
		$tableExists = $result->fetch(PDO::FETCH_NUM) > 0;
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
		//$this->LOG->trace("$this->logprefix Looking up entity with keyid $id");

		// Get the db connection
		global $db;

		//Prepare to query the table by key_id
		$sql = 'SELECT * FROM ' . $this->modelObject->getTableName() . ' WHERE key_id = ?';
		$stmt = $db->prepare($sql);
		$stmt->bindParam(1,$id,PDO::PARAM_INT);
		$stmt->setFetchMode(PDO::FETCH_CLASS, $this->modelClassName);			// Query the db and return one of $this type of object
		if ($stmt->execute()) {
			$result = $stmt->fetch();

			// $result being false indicates no rows returned.
			if(!$result) {
				//$this->LOG->trace('No Rows returned. Returning ActionError');
				//return;
				return new ActionError('No rows returned');
			}
		// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();
			$result = new QueryError($error);
			$this->LOG->error('Returning QueryError with message: ' . $result->getMessage());
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
			return true;
	}

	/**
	 * Retrieves a list of all entities of this type.
	 *
	 * @return Array of entities
	 */
	function getAll( $sortColumn = NULL, $sortDescending = FALSE, $activeOnly = FALSE ){

		//$this->LOG->trace("$this->logprefix Looking up all entities" . ($sortColumn == NULL ? '' : ", sorted by $sortColumn"));

		// Get the db connection
		global $db;
		$className = get_class($this);

		//Prepare to query all from the table
		$stmt = $db->prepare('SELECT * FROM ' . $this->modelObject->getTableName() . ' ' . ($activeOnly ? 'WHERE is_active = 1 ' : '') .  ($sortColumn == NULL ? '' : " ORDER BY $sortColumn " . ($sortDescending ? 'DESC' : 'ASC')) );

		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClassName);
			// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();
			$result = new QueryError($error);
			$this->LOG->error('Returning QueryError with message: ' . $result->getMessage());
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
	 * Retrieves all active entities of this type
	 *
	 * @param unknown $sortColumn
	 * @return Array:
	 */
	function getAllActive(){
		return $this->getAll( null, false, true );
	}
	
	/**
	 * 
	 * Retrieves all entities of this $this->modelObject's type matching the WhereClause objects that comprise the WhereClauseGroup
	 * 
	 * @param WhereClauseGroup $whereClauseGroup
	 * @return Array $result
	 */
	function getAllWhere( $whereClauseGroup, $junction = "AND" ){
		
		// Get the db connection
		global $db;
		$className = get_class($this);
		
		$sql = 'SELECT * FROM ' . $this->modelObject->getTableName() . ' ';
		$whereClauses = $whereClauseGroup->getClauses();
		
		//White lists for queries to safeguard against injection
		$columnWhiteList = $this->modelObject->getColumnData();
		//Likely operators.  Add to this list as needed (Only operators commonly used with SELECT statements should be here)
		$operatorWhiteList = array("=", "IS", "IS NOT", "BETWEEN", "AND", "&&", "<", "<=", ">", ">=", "IN");
		$junctionWhiteList = array("AND", "&&", "OR", "||");
		
		foreach($whereClauses as $key=>$clause){
			//Verify that the speficied column and operator are in the whitelist.  If not, return an error and log the possible attempt at sql injection
			$column  =  $clause->getCol();
			$operator = $clause->getOperator();
			//Neither the column nor the operator where in the whitelist
			if(!array_key_exists($column, $columnWhiteList) && !in_array($operator, $operatorWhiteList)){
				$this->LOG->fatal("The operator, $operator, used was not in the white list.");
				$this->LOG->fatal("Query attempted on column, $column, not in white list");
				return new ActionError("MySQL Error");
			}
			//The operator wasn't in the whitelist
			elseif(!in_array($clause->getOperator(), $operatorWhiteList)){
				$this->LOG->fatal("The operator, $operator, used was not in the white list.");
				return new ActionError("MySQL Error");
			}
			//The column wasn't in the whitelist
			elseif(!array_key_exists($column, $columnWhiteList)){
				$this->LOG->fatal("Query attempted on column, $column, not in white list");
				return new ActionError("MySQL Error");
			}

			if($key == 0){
				$sql .= "WHERE " . $clause->getCol() . " " . $clause->getOperator() ;
				//if the operator is "IS" or "IS NOT" we are NULL checking
				//in this case we don't use user input for the value
				// -- "It depends upon what the meaning of the word 'is' is." --  Bill Clinton
				if(strstr($clause->getOperator(), "IS")){
					$sql .= " NULL";
				}else{
					$sql .= " ?";
				}
			}else{
				if ( !in_array($junction, $junctionWhiteList) ) {
					$this->LOG->fatal("The junction, $junction, used was not in the white list.");
					return new ActionError("MySQL Error");
				}
				$sql .= " " . $junction . " " . $clause->getCol() . " " . $clause->getOperator();
				//if the operator is "IS" or "IS NOT" we are NULL checking
				//in this case we don't use user input for the value
				// -- "It depends upon what the meaning of the word 'is' is." --  Bill Clinton
				if(strstr($clause->getOperator(), "IS")){
					$sql .= " NULL";
				}else{
					$sql .= " ?";
				}
			}
		}
		
		$this->LOG->fatal("DIG: $sql");
		//Prepare to query all from the table
		$stmt = $db->prepare($sql);
		foreach($whereClauses as $key=>$clause){
				if($clause->getVal() != NULL)$stmt->bindValue($key+1, $clause->getVal());
		}
			
		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClassName);
			// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();
			$result = new QueryError($error);
			$this->LOG->fatal('Returning QueryError with message: ' . $result->getMessage());
		}
		
		return $result;
	}
	
	/*
	 * 
	 *  Returns an array of objects that are active, have not null collections of related objects, or both
	 * 
	 * 	@param DataRelationship $relationship  The relationship, defined in $this->modelObject
	 *  @return Array $result   A bunch of objects of $this->modelObject's type
	 */
	public function getAllWith(DataRelationship $relationship){
		// Get the db connection
		global $db;
		
		// get the relationship parameters needed to build the query
		$className		= $relationship->getClassName();
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();
		$modelObject    = $this->modelObject;
	
		$sql = "SELECT * FROM " . $modelObject->getTableName() . " WHERE is_active = 1 OR key_id IN(SELECT $foreignKeyName FROM $tableName)";
		
		$stmt = $db->prepare($sql);
		
		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClassName);
			// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();
			$result = new QueryError($error);
			$this->LOG->error('Returning QueryError with message: ' . $result->getMessage());
		}
				
		return $result;
	}
	
	/**
	 * Gets the sum of ParcelUseAmounts for a given authorization for a given date range with a given waste type
	 *
	 * @param string $startDate mysql timestamp formatted date representing beginning of the period
	 * @param string $enddate mysql timestamp formatted date representing end of the period
	 * @param integer $wasteTypeId Key id of the appropriate waste type
	 * @return int $sum
	 */
	public function getUsageAmounts(  $startDate, $endDate, $wasteTypeId ){
					
		$sql = "SELECT SUM(`curie_level`) 
				FROM `parcel_use_amount`
				WHERE `parcel_use_id` IN 
				(select key_id from `parcel_use` WHERE `parcel_id` 
				IN (select key_id from `parcel` WHERE `authorization_id` = ?))
				AND `date_last_modified` BETWEEN ? AND ? 
				AND `waste_type_id` = ?";

		// Get the db connection
		global $db;
		$stmt = $db->prepare($sql);
		$stmt->bindValue(1, $this->modelObject->getAuthorization_id());
		$stmt->bindValue(2, $startDate);
		$stmt->bindValue(3, $endDate);
		$stmt->bindValue(4, $wasteTypeId);


		$stmt->execute();
		
		$total = $stmt->fetch(PDO::FETCH_NUM);
		$sum = $total[0]; // 0 is the first array. here array is only one.
		if($sum == NULL)$sum = 0;
		return $sum;
	}
	
	/**
	 * Gets the sum of Parcels transfered in or ordered durring a given period
	 *
	 * @param string $startDate mysql timestamp formatted date representing beginning of the period
	 * @param string $enddate mysql timestamp formatted date representing end of the period
	 * @param bool $hasRsNumber true if we are looking for parcels with an Rs_number (those that count as orders), false if those without one (parcels that count as transfer)
	 * @return int $sum
	 */
	public function getTransferAmounts( $startDate, $endDate, $hasRsNumber ){
		
		$sql = "SELECT SUM(`quantity`) 
				FROM `parcel`
				where `authorization_id` = ?
				AND `arrival_date` BETWEEN ? AND ?";
		
		if($hasRsNumber == true){
			$sql .= " AND rs_number IS NOT NULL";
		}else{
			$sql .= " AND rs_number IS NULL";		
		}
		
		// Get the db connection
		global $db;
		$stmt = $db->prepare($sql);
		$stmt->bindValue(1, $this->modelObject->getAuthorization_id());
		$stmt->bindValue(2, $startDate);
		$stmt->bindValue(3, $endDate);

		$stmt->execute();
		
		$total = $stmt->fetch(PDO::FETCH_NUM);
		$sum = $total[0]; // 0 is the first array. here array is only one.
		if($sum == NULL)$sum = 0;
		return $sum;
	}

	/**
	 * Commits the values of this entity to the database
	 *
	 * @param GenericCrud $object: Object to save. If null, the model object will be used instead
	 * @return GenericCrud
	 */
	function save(GenericCrud $object = NULL){
		//$this->LOG->trace("$this->logprefix Saving entity");
		//$this->LOG->trace($object);
		
		//Make sure we have an object to save
		if( $object == NULL ){
			$object = $this->modelObject;
		}
		//If $object is given, make sure it's the right type
		else if( get_class($object) != $this->modelClassName ){
			// we have a problem!
			$this->LOG->error("Attempting to save entity of class " . get_class($object) . ", which does not match model object class of $this->modelClassName");

			return new ModifyError("Entity did not match model object class", $object);
		}
		//else use $object as-is!

		// update the modify timetamp
		$object->setDate_last_modified(date("Y-m-d H:i:s"));

		// Add the creation timestamp
		if ($object->getDate_created() == null) {$object->setDate_created(date("Y-m-d H:i:s"));}

		// Get the db connection
		global $db;
		//print_r($db);

		// Check to see if this item has a key_id
		//  If it does, we assume it's an existing record and issue an UPDATE
		if ($object->getKey_id() != null) {

		    $_SESSION["DEBUG"] = "Calling db update...";
			$stmt = $this->createUpdateStatement($db,$object);
			$stmt = $this->bindColumns($stmt,$object);
			$success = $stmt->execute();
		// Otherwise, issue an INSERT
		} else {
	    	$_SESSION["DEBUG"] = "Calling db insert...";
			 //echo  "Calling db insert...";

	    	// Add the creation timestamp
	    	$object->setDate_created(date("Y-m-d H:i:s"));

			$stmt = $this->createInsertStatement($db,$object);
		   	$stmt = $this->bindColumns($stmt,$object);
			$success = $stmt->execute();

			// since this is a new record, get the new key_id issued by the database and add it to this object.
			$object->setKey_id($db->lastInsertId());
		}

		// Look for db errors
		// If no errors, update and return the object
		if($success && $object->getKey_Id() > 0) {
			//$this->LOG->trace("$this->logprefix Successfully updated or inserted entity with key_id=" . $object->getKey_Id());

			// Re-load the whole record so that updated Date fields (and any field auto-set by DB) are updated
			//$this->LOG->trace("$this->logprefix Reloading updated/inserted entity with key_id=" . $object->getKey_Id() );
			$object = $this->getById( $object->getKey_Id() );

		// Otherwise, the statement failed to execute, so return an error
		} else {
			//$this->LOG->trace("$this->logprefix Object had a key_id of " . $object->getKey_Id());
			$errorInfo = $stmt->errorInfo();
			$object = new ModifyError($errorInfo[2], $object);
			$this->LOG->error('Returning ModifyError with message: ' . $object->getMessage());
		}

		// return the updated object
		return $object;

	}

	/**
	 * Retrieves a list of related items for the entity of this type with the given ID.
	 *
	 * @param unknown $id
	 * @param DataRelationship $relationship
	 * @param String $sortColumn
	 * @param Boolean $activeOnly
	 * @param Boolean $activeOnlyRelated
	 * @return Array:
	 */
	public function getRelatedItemsById($id, DataRelationship $relationship, $sortColumn = null, $activeOnly = false, $activeOnlyRelated = false){
		if (empty($id)) { return array();}
		
		// Get the db connection
		global $db;
	
		// get the relationship parameters needed to build the query
		$className		= $relationship->getClassName();
		$classInstance  = new $className();
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();
		$modelObject    = $this->modelObject;
		//$this->LOG->error("$this->logprefix Retrieving related items for " . get_class($modelObject) . " entity with id=$id");
		
		$whereTag = $activeOnly ? " WHERE is_active = 1 AND " : " WHERE ";
		//$sql = "SELECT * FROM " . $modelObject->getTableName() . $whereTag . "key_id IN(SELECT $keyName FROM $tableName WHERE $foreignKeyName = $id";
		$sql = "SELECT * FROM " . $classInstance->getTableName() . $whereTag . "key_id IN(SELECT $keyName FROM $tableName WHERE $foreignKeyName = $id";
		$sql .= $activeOnlyRelated ? " AND is_active = 1)" : ")";
		
		if ($sortColumn != null){ $sql .= " ORDER BY " . $sortColumn;}
		$stmt = $db->prepare($sql);
	
		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, $className);
			// ... otherwise, generate error message to be returned
		} else {
			$result = array();
			$error = $stmt->errorInfo();
			$resultError = new QueryError($error);
			$this->LOG->error('Returning QueryError with message: ' . $resultError->getMessage());
		}
	
		return $result;
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
		//$this->LOG->trace("$this->logprefix Inserting new related item for entity with id=$foreignKey_id");

		// Get the db connection
		global $db;

		//print_r($relationship);
		// get the relationship parameters needed to build the query
		$className		= $relationship->getClassName();
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();

		$sql = "INSERT INTO  $tableName ($foreignKeyName, $keyName) VALUES (:foreignKey_id, :key_id) ";
		//$this->LOG->trace("Preparing insert statement [$sql]");

		$stmt = $db->prepare($sql);
		// Bind the params.
		$stmt->bindParam(":foreignKey_id",$foreignKey_id,PDO::PARAM_INT);
		$stmt->bindParam(":key_id",$key_id,PDO::PARAM_INT);

		// Insert the record and return true
		if ($stmt->execute() ) {
			//$this->LOG->trace( "Inserted new related item with key_id [$key_id]");
			return true;
		// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();

			// create modify error with human readable error message
			$result = new ModifyError($error[2]);
			$this->LOG->error('Returning ModifyError with message: ' . $result->getMessage());
			return $result;
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
		//$this->LOG->trace("DELETE FROM $tableName WHERE $foreignKeyName =  $foreignKey_id AND $keyName = $key_id");

		$stmt = $db->prepare($sql);
		// Bind the params.
		$stmt->bindParam(":foreignKey_id",$foreignKey_id,PDO::PARAM_INT);
		$stmt->bindParam(":key_id",$key_id,PDO::PARAM_INT);

		// Delete the record and return true
		if ($stmt->execute() ) {
			//$this->LOG->trace( "Remove related item with key_id [$key_id]");
			return true;
		// ... otherwise, generate an error message to be returned
		} else {
			$error = $stmt->errorInfo();

			// create modify error with human readable error message
			$result = new ModifyError($error[2]);
			$this->LOG->error('Returning ModifyError with message: ' . $result->getMessage());

			return $result;
		}
	}

	function bindColumns($stmt,$object) {
		foreach ($object->getColumnData() as $key=>$value){
			if ($value == "integer") {$type = PDO::PARAM_INT;}
			if ($value == "text") {$type = PDO::PARAM_STR;}
			if ($value == "float") {$type = PDO::PARAM_STR;}
			if ($value == "boolean") {$type = PDO::PARAM_BOOL;}
			if ($value == "datetime") {$type = PDO::PARAM_STR;}
			if ($value == "timestamp") {$type = PDO::PARAM_STR;}

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

		//$this->LOG->trace("Preparing insert statement [$sql]");

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

	function getUserByUsername($username){
		$this->LOG->debug("$this->logprefix Looking up entity with keyid $id");

		// Get the db connection
		global $db;

		$user = new User();

		//Prepare to query the user table by username
		$stmt = $db->prepare('SELECT * FROM ' . $user->getTableName() . ' WHERE username = ?');
		$stmt->bindParam(1,$username,PDO::PARAM_STR);
		$stmt->setFetchMode(PDO::FETCH_CLASS, "User");			// Query the db and return one user
		if ($stmt->execute()) {
			$result = $stmt->fetch();
			// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();
			$this->LOG->error('Returning QueryError with message: ' . $result->getMessage());

			$result = new QueryError($error[2]);
		}

		return $result;
	}

	function getInspectionsByYear($year){
		//$this->LOG->trace("$this->logprefix Looking up inspections for $year");

		// Get the db connection
		global $db;

		//Prepare to query all from the table
		$stmt = $db->prepare('SELECT * FROM pi_rooms_buildings WHERE year = ? ORDER BY campus_name, building_name,pi_name');
		$stmt->bindParam(1,$year,PDO::PARAM_STR);

		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, "InspectionScheduleDto");
			// ... otherwise, die and echo the db error
		} else {
			$error = $stmt->errorInfo();
			die($error[2]);
		}

		return $result;
	}

	function getRelationships( $tableName, $resultClass ){
		$this->LOG->debug("about to get relationships from $tableName");

		global $db;

		$stmt = $db->prepare('SELECT * FROM ' . $tableName);

		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, $resultClass);
			foreach($result as $row){
				$row->passFlag = true;
			}
			//$this->LOG->trace($result);
			// ... otherwise, die and echo the db error
		} else {
			$error = $stmt->errorInfo();
			die($error[2]);
		}

		return $result;
	}
	
	function getPIsByHazard($rooms = NULL){
		// Get the db connection
		global $db;
		
		// get the relationship parameters needed to build the query
		$hazard = $this->modelObject;
		//$this->LOG->error("$this->logprefix Retrieving related items for " . get_class($modelObject) . " entity with id=$id");
		
		//if we pass a collection of rooms, we are only getting back PIs that have relationships with those rooms, rather than all the rooms this hazard is in
		//get the key_ids of the rooms
		if($rooms !=NULL){
			$roomIds = array();
			foreach($rooms as $room){
				if($room->getContainsHazard() == TRUE)$roomIds[] = $room->getKey_id();
			}
			$roomsCSV = implode(",", $roomIds);
		}
		
		//$sql = "SELECT * FROM " . $modelObject->getTableName() . $whereTag . "key_id IN(SELECT $keyName FROM $tableName WHERE $foreignKeyName = $id";
		$sql = "SELECT * FROM principal_investigator WHERE key_id";
		
		if(!isset($roomsCSV)){
			$sql .= " IN(SELECT principal_investigator_id from principal_investigator_room WHERE room_id IN(SELECT room_id FROM hazard_room WHERE hazard_id = ".$hazard->getKey_id().") )";			
		}else{
			$sql .= " IN(SELECT principal_investigator_id from principal_investigator_room WHERE room_id IN($roomsCSV))";
		}
		
		$stmt = $db->prepare($sql);
		
		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, "PrincipalInvestigator");
			// ... otherwise, generate error message to be returned
		} else {
			$result = array();
			$error = $stmt->errorInfo();
			$resultError = new QueryError($error);
			$this->LOG->error('Returning QueryError with message: ' . $resultError->getMessage());
		}
		
		return $result;
	}

	function getAllLocations(){
		$LOG = Logger::getLogger(__CLASS__);

		$this->has_hazards = false;
		// Get the db connection
		global $db;

		$queryString = "SELECT a.key_id as room_id, a.building_id as building_id, a.name as room_name, c.key_id as pi_key_id, CONCAT(f.first_name, ' ', f.last_name) as pi_name, e.key_id as department_id, e.name as department_name
						FROM room a
						LEFT JOIN principal_investigator_room b
						ON a.key_id = b.room_id
						LEFT JOIN principal_investigator c
						ON b.principal_investigator_id = c.key_id
						LEFT JOIN principal_investigator_department d
						ON c.key_id = d.principal_investigator_id
						LEFT JOIN department e
						ON d.department_id = e.key_id
						LEFT JOIN erasmus_user f
						ON c.user_id = f.key_id
						ORDER BY a.building_id, c.key_id;";
		$stmt = $db->prepare($queryString);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, "LocationsDto");
	}
	
	function getAllDepartmentsAndCounts(){
		$LOG = Logger::getLogger(__CLASS__);
	
		$this->has_hazards = false;
		// Get the db connection
		global $db;
	
		$queryString = "SELECT a.key_id as department_id, a.name as department_name, a.is_active, count(distinct b.key_id) as pi_count, 
						count(distinct c.key_id) as room_count
						FROM department a 
						LEFT JOIN principal_investigator_department d ON (a.key_id = d.department_id)
						LEFT JOIN principal_investigator b ON (d.principal_investigator_id = b.key_id) AND b.is_active = 1
						LEFT JOIN principal_investigator_room e ON (b.key_id = e.principal_investigator_id)
						LEFT JOIN room c ON (e.room_id = c.key_id)
						GROUP BY a.key_id
						ORDER BY a.name;";
		$stmt = $db->prepare($queryString);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, "DepartmentDto");
	}
	
	function getDepartmentDtoById( $id ){
		$LOG = Logger::getLogger(__CLASS__);
	
		$this->has_hazards = false;
		// Get the db connection
		global $db;

		$queryString = "SELECT a.key_id as department_id, a.name as department_name, a.is_active, count(distinct b.key_id) as pi_count, 
						count(distinct c.key_id) as room_count
						FROM department a 
						LEFT JOIN principal_investigator_department d ON (a.key_id = d.department_id)
						LEFT JOIN principal_investigator b ON (d.principal_investigator_id = b.key_id) AND b.is_active = 1
						LEFT JOIN principal_investigator_room e ON (b.key_id = e.principal_investigator_id)
						LEFT JOIN room c ON (e.room_id = c.key_id)
						WHERE a.key_id = :id;";
		$stmt = $db->prepare($queryString);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, "DepartmentDto");
	}


}
?>

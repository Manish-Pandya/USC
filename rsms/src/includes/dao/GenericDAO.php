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
				$errorInfo = $stmt->errorInfo();
                $object = new ModifyError($errorInfo[2], $object);
                $this->LOG->fatal('Returning ModifyError with message: ' . $object->getMessage());
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
	function getAllWhere( WhereClauseGroup $whereClauseGroup, $junction = "AND", $sortColumn = null ){
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

        if($sortColumn != null && array_key_exists($sortColumn, $columnWhiteList)){
            $sql .= " ORDER BY " . $sortColumn;
        }
        //$this->LOG->fatal($sql);
		//Prepare to query all from the table
		$stmt = $db->prepare($sql);
        //$this->LOG->fatal($stmt);

		$i = 1;
		foreach($whereClauses as $clause){
			if($clause->getVal() != NULL){
				$stmt->bindValue($i, $clause->getVal());
				$i++;
			}
		}

		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClassName);
			// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();
			$result = new QueryError($error);
			$this->LOG->fatal('Returning QueryError with message: ' . $result->getMessage());
            $this->LOG->fatal($stmt);
            $this->LOG->fatal($this->modelObject);
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


		$sql = "SELECT ROUND(SUM(a.curie_level),7)
                FROM `parcel_use_amount` a
                LEFT JOIN parcel_use b
                ON a.parcel_use_id = b.key_id
                LEFT JOIN parcel c
                ON b.parcel_id = c.key_id
                LEFT JOIN waste_bag f
                ON a.waste_bag_id = f.key_id
                LEFT JOIN carboy_use_cycle g
                ON a.carboy_id = g.key_id
                LEFT JOIN scint_vial_collection h
                ON a.scint_vial_collection_id = h.key_id
                LEFT OUTER JOIN pickup i
                ON f.pickup_id = i.key_id
                OR g.pickup_id = i.key_id
                OR h.pickup_id = i.key_id
                AND i.status != 'Requested'
				WHERE c.authorization_id = ?
				AND b.date_used BETWEEN ? AND ?
				AND `waste_type_id` = ?
                AND (f.pickup_id IS NOT NULL OR g.pickup_id IS NOT NULL OR h.pickup_id IS NOT NULL )";

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
     * Gets the sum of Parcels ordered in or ordered before a given period
     *
     * @param string $startDate mysql timestamp formatted date representing beginning of the period
     * @param string $enddate mysql timestamp formatted date representing end of the period
     * @return int $sum
     */
	public function getStartingAmount( $startDate = null ){
        $l = Logger::getLogger("transfer amounts");
		$sql = "SELECT SUM(`quantity`)
				FROM parcel a
                WHERE `authorization_id` = ?";

		// Get the db connection
		global $db;
		$stmt = $db->prepare($sql);
		$stmt->bindValue(1, $this->modelObject->getAuthorization_id());
        if($startDate != null){
            $sql .= "AND arrival_date < ?";
            $stmt->bindValue(2, $startDate);
        }

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
	 * @param bool $hasTransferDate true if we are looking for parcels with an transfer_in_date (those that count as transfer), false if those without one (parcels that count as orders), or null for all parcels
	 * @return int $sum
	 */
	public function getTransferAmounts( $startDate, $endDate, $hasTransferDate = null ){
        $l = Logger::getLogger("transfer amounts");
		$sql = "SELECT SUM(`quantity`)
				FROM `parcel`
				where `authorization_id` = ?";

        if($hasTransferDate == true){
            $sql .= " AND transfer_in_date BETWEEN ? AND ?";

        }elseif($hasTransferDate != true){
            $sql .= " AND transfer_in_date IS NULL AND `arrival_date` BETWEEN ? AND ?";
            $l->fatal($sql);
            $l->fatal(array($startDate, $endDate, $hasTransferDate,  $this->modelObject->getAuthorization_id()));
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
     * Gets the sum of Parcels transfered in or ordered durring a given period
     *
     * @param string $startDate mysql timestamp formatted date representing beginning of the period
     * @param string $enddate mysql timestamp formatted date representing end of the period
     * @param bool $hasTransferDate true if we are looking for parcels with an transfer_in_date (those that count as transfer), false if those without one (parcels that count as orders), or null for all parcels
     * @return int $sum
     */
	public function getTransferOutAmounts( $startDate, $endDate ){
		$sql = "SELECT SUM(`quantity`)
				FROM `parcel_use`
				where `parcel_id` in (select key_id from parcel where `authorization_id` = ?)
				AND `date_transferred` BETWEEN ? AND ?";

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
			$this->LOG->fatal("Attempting to save entity of class " . get_class($object) . ", which does not match model object class of $this->modelClassName");

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
	public function getRelatedItemsById($id, DataRelationship $relationship, $sortColumns = null, $activeOnly = false, $activeOnlyRelated = false){
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

		if ($sortColumns != null){
			$sql .= " ORDER BY";
			$max = count($sortColumns);
			foreach($sortColumns as $key=>$column){
				$sql .= " " . $column;
				if($key != $max - 1){
					$sql .= ",";
				}
			}
		}
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

	public function getInspectionSchedule($year = NULL){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        // read the Year value from the request.
        $year = $this->getValueFromRequest('year', $year);

        // If the year is null, choose the current year.
        if ($year == null){
            $year = $this->getCurrentYear();
        }
        // Call the database
		$LOG->fatal('getting schedule for ' . $year);
        $dao = $this->getDao(new Inspection());
        $inspectionSchedules = $dao->getNeededInspectionsByYear($year);

        $roomMaps = array();
        $roomMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
        $roomMaps[] = new EntityMap("lazy","getHazards");
        $roomMaps[] = new EntityMap("lazy","getHazard_room_relations");
        $roomMaps[] = new EntityMap("lazy","getHas_hazards");
        $roomMaps[] = new EntityMap("lazy","getBuilding");
        $roomMaps[] = new EntityMap("lazy","getSolidsContainers");
        $roomMaps[] = new EntityMap("lazy","getHazardTypesArePresent");

        foreach ($inspectionSchedules as &$is){
            if ($is->getInspection_id() !== null){
                $inspection = $dao->getById($is->getInspection_id());

                $entityMaps = array();
                $entityMaps[] = new EntityMap("eager","getInspectors");
                $entityMaps[] = new EntityMap("lazy","getRooms");
                $entityMaps[] = new EntityMap("lazy","getResponses");
                $entityMaps[] = new EntityMap("lazy","getDeficiency_selections");
                $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigator");
                $entityMaps[] = new EntityMap("lazy","getChecklists");
                $entityMaps[] = new EntityMap("eager","getStatus");

                $inspection->setEntityMaps($entityMaps);

                $filteredRooms = array();
                $rooms = $inspection->getRooms();
                foreach( $rooms as $room ){
                	if( $room->getBuilding_id() == $is->getBuilding_key_id() ){
                        $room->setEntityMaps($roomMaps);
                		array_push($filteredRooms, $room);
                	}
                }
                $is->setInspection_rooms($filteredRooms);
                // $LOG->fatal($is);
                //return $is;
            }

            $piDao = $this->getDao(new PrincipalInvestigator());
            $pi = $piDao->getById($is->getPi_key_id());
            $rooms = $pi->getRooms();
            $pi_bldg_rooms = array();
            foreach ($rooms as $room){
                $room->setEntityMaps($roomMaps);

                if ($room->getBuilding_id() == $is->getBuilding_key_id()){
                    $pi_bldg_rooms[] = $room;
                }
            }
            $is->setBuilding_rooms($pi_bldg_rooms);
        }
        return $inspectionSchedules;
    }

    function getNeededInspectionsByYear($year){
		//$this->LOG->trace("$this->logprefix Looking up inspections for $year");

		// Get the db connection
		global $db;

		//Prepare to query all from the table
		//$stmt = $db->prepare('SELECT * FROM pi_rooms_buildings WHERE year = ? ORDER BY campus_name, building_name, pi_name');
        $sql = "select `a`.`key_id` AS `pi_key_id`,
                concat(`b`.`last_name`,', ',`b`.`first_name`) AS `pi_name`,
                `d`.`name` AS `building_name`,
                `d`.`key_id` AS `building_key_id`,
                `e`.`name` AS `campus_name`,
                `e`.`key_id` AS `campus_key_id`,
                bit_or(`c`.`bio_hazards_present`) AS `bio_hazards_present`,
                bit_or(`c`.`chem_hazards_present`) AS `chem_hazards_present`,
                bit_or(`c`.`rad_hazards_present`) AS `rad_hazards_present`,
                year(curdate()) AS `year`,
                NULL AS `inspection_id` from (((((`principal_investigator` `a` join `erasmus_user` `b`) join `room` `c`) join `building` `d`) join `campus` `e`) join `principal_investigator_room` `f`)
                where ((`a`.`is_active` = 1) and (`c`.`is_active` = 1) and (`b`.`key_id` = `a`.`user_id`) and (`f`.`principal_investigator_id` = `a`.`key_id`) and (`f`.`room_id` = `c`.`key_id`) and (`c`.`building_id` = `d`.`key_id`) and (`d`.`campus_id` = `e`.`key_id`) and (not(`a`.`key_id` in (select `inspection`.`principal_investigator_id`
                from `inspection`
                where (coalesce(year(`inspection`.`date_started`),
                `inspection`.`schedule_year`) = ?))))) group by `a`.`key_id`,concat(`b`.`last_name`,', ',`b`.`first_name`),`d`.`name`,`d`.`key_id`,`e`.`name`,`e`.`key_id`,year(curdate()),
                NULL union select `a`.`key_id` AS `pi_key_id`,
                concat(`b`.`last_name`,', ',`b`.`first_name`) AS `pi_name`,
                `d`.`name` AS `building_name`,
                `d`.`key_id` AS `building_key_id`,
                `e`.`name` AS `campus_name`,`e`.`key_id` AS `campus_key_id`,bit_or(`c`.`bio_hazards_present`) AS `bio_hazards_present`,
                bit_or(`c`.`chem_hazards_present`) AS `chem_hazards_present`,
                bit_or(`c`.`rad_hazards_present`) AS `rad_hazards_present`,
                coalesce(year(`g`.`date_started`),`g`.`schedule_year`) AS `year`,`g`.`key_id`
                AS `inspection_id`
                from ((((((`principal_investigator` `a` join `erasmus_user` `b`) join `room` `c`) join `building` `d`) join `campus` `e`) join `inspection_room` `f`) join `inspection` `g`)
                where ((`a`.`key_id` = `g`.`principal_investigator_id`) and (`b`.`key_id` = `a`.`user_id`) and (`g`.`key_id` = `f`.`inspection_id`) and (`c`.`key_id` = `f`.`room_id`)
                and (`c`.`building_id` = `d`.`key_id`) and (`d`.`campus_id` = `e`.`key_id`) and (coalesce(year(`g`.`date_started`),
                `g`.`schedule_year`) = ?) )
                group by `a`.`key_id`,concat(`b`.`last_name`,', ',`b`.`first_name`),`d`.`name`,`d`.`key_id`,`e`.`name`,`e`.`key_id`,coalesce(year(`g`.`date_started`),`g`.`schedule_year`),`f`.`inspection_id` ORDER BY campus_name, building_name, pi_name";
        $stmt = $db->prepare($sql);
		$stmt->bindParam(1,$year,PDO::PARAM_STR);
        $stmt->bindParam(2,$year,PDO::PARAM_STR);

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

    function getInspectionsByYear($year){
        //`inspection` where (coalesce(year(`inspection`.`date_started`),`inspection`.`schedule_year`) = ?)
        global $db;

		//Prepare to query all from the table
		//$stmt = $db->prepare('SELECT * FROM pi_rooms_buildings WHERE year = ? ORDER BY campus_name, building_name, pi_name');
        $sql = "select * from inspection where (coalesce(year(`inspection`.`date_started`),`inspection`.`schedule_year`) = ?)";
        $stmt = $db->prepare($sql);
		$stmt->bindParam(1,$year,PDO::PARAM_STR);

		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, "Inspection");
			// ... otherwise, die and echo the db error
		} else {
			$error = $stmt->errorInfo();
			die($error[2]);
		}

		return $result;
    }

	/*
	 * @param RelationMapping relationship
	 */

	function getRelationships( RelationMapping $relationship ){
		$this->LOG->debug("about to get relationships from $tableName");

		global $db;

		//sometimes, in many to many relationships, we are asking for what we usually think of as the child objects to get their collection of parents
		//in those cases, we reverse the relationships
		if($relationship->getIsReversed()){
			$parentColumn = $relationship->getChildColumn();
			$childColumn  = $relationship->getParentColumn();
		}else{
			$parentColumn = $relationship->getParentColumn();
			$childColumn  = $relationship->getChildColumn();
		}
		$stmt = "SELECT $parentColumn as parentId, $childColumn as childId FROM " . $relationship->getTableName();
		$this->LOG->fatal($relationship);
		$this->LOG->fatal($stmt);
		$stmt = $db->prepare($stmt);

		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, "RelationDto");
			foreach($result as &$dto){
				$dto->setTable($relationship->getTableName());
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

		$queryString = "SELECT d.name as department_name, d.is_active as is_active, c.name as campus_name, d.key_id as department_id, d.specialty_lab as specialty_lab, c.key_id as campus_id,
						count(distinct e.key_id) room_count,
						count(distinct a.principal_investigator_id) pi_count,
						count(distinct f.key_id) building_count

						FROM
						department d LEFT OUTER JOIN
						principal_investigator_department b ON (d.key_id = b.department_id) LEFT OUTER JOIN
						principal_investigator_room a ON (a.principal_investigator_id = b.principal_investigator_id) LEFT OUTER JOIN
						room e ON (e.key_id = a.room_id) LEFT OUTER JOIN
						building f ON (f.key_id = e.building_id) LEFT OUTER JOIN
						campus c ON (c.key_id = f.campus_id)

						GROUP BY c.name, d.name
						ORDER BY d.name, c.name";
		$stmt = $db->prepare($queryString);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, "DepartmentDto");
	}

	function getDepartmentDtoById( $id ){
		$LOG = Logger::getLogger(__CLASS__);

		$this->has_hazards = false;
		// Get the db connection
		global $db;

		$queryString = "SELECT d.name as department_name, d.is_active as is_active, c.name as campus_name, d.key_id as department_id, d.specialty_lab as specialty_lab, c.key_id as campus_id,
						count(distinct e.key_id) room_count,
						count(distinct a.principal_investigator_id) pi_count,
						count(distinct f.key_id) building_count

						FROM
						department d LEFT OUTER JOIN
						principal_investigator_department b ON (d.key_id = b.department_id) LEFT OUTER JOIN
						principal_investigator_room a ON (a.principal_investigator_id = b.principal_investigator_id) LEFT OUTER JOIN
						room e ON (e.key_id = a.room_id) LEFT OUTER JOIN
						building f ON (f.key_id = e.building_id) LEFT OUTER JOIN
						campus c ON (c.key_id = f.campus_id)
						WHERE d.key_id = :id
						GROUP BY c.name, d.name
						ORDER BY d.name, c.name";
		$stmt = $db->prepare($queryString);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, "DepartmentDto");
	}

	public function getDepartmentsByCampusId(){
		global $db;

		$queryString = "SELECT a.key_id as department_id, a.name as department_name, a.is_active, a.specialty_lab,
						g.name as campus_name, g.key_id as campus_id
						FROM department a
						LEFT JOIN principal_investigator_department d ON (a.key_id = d.department_id)
						LEFT JOIN principal_investigator b ON (d.principal_investigator_id = b.key_id) AND b.is_active = 1
						LEFT JOIN principal_investigator_room e ON (b.key_id = e.principal_investigator_id)
						LEFT JOIN building f ON (e.key_id = e.room_id)
						LEFT JOIN campus g ON (g.key_id = f.campus_id);";
		$stmt = $db->prepare($queryString);
		//$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, "DepartmentDto");
	}

	function getHazardRoomDtosByPIId( $pIId, $roomId = null ){
		$LOG = Logger::getLogger(__CLASS__);

		// Get the db connection
		global $db;

		//get this pi's rooms
		if($roomId == null){
			$roomsQueryString = "SELECT a.key_id as room_id, a.building_id, a.name as room_name, COALESCE(NULLIF(b.alias, ''), b.name) as building_name from room a
								 LEFT JOIN building b on a.building_id = b.key_id
								 where a.key_id in (select room_id from principal_investigator_room where principal_investigator_id = :id)";
			$stmt = $db->prepare($roomsQueryString);
			$stmt->bindParam(':id', $pIId, PDO::PARAM_INT);
		}else{
			$roomsQueryString = "SELECT a.key_id as room_id, a.building_id, a.name as room_name, COALESCE(NULLIF(b.alias, ''), b.name) as building_name from room a
								 LEFT JOIN building b on a.building_id = b.key_id
								 where a.key_id = :roomId";
			$stmt = $db->prepare($roomsQueryString);
			$stmt->bindParam(':roomId', $roomId, PDO::PARAM_INT);
		}
		$stmt->execute();
		$rooms = $stmt->fetchAll(PDO::FETCH_CLASS, "PIHazardRoomDto");

		$roomIds = array();
		foreach($rooms as $room){
			$roomIds[] = $room->getRoom_id();
		}

		//get a dto for every hazard
		$queryString = "SELECT key_id as hazard_id, order_index, key_id, name as hazard_name, is_equipment, parent_hazard_id as parent_hazard_id, (SELECT EXISTS(SELECT 1 from hazard where parent_hazard_id = hazard_id) ) as hasChildren from hazard WHERE is_active = 1;";
		$stmt = $db->prepare($queryString);
		$stmt->execute();
		$dtos = $stmt->fetchAll(PDO::FETCH_CLASS, "HazardDto");

		foreach($dtos as $dto){
			$dto->setRoomIds($roomIds);
			$dto->setPrincipal_investigator_id($pIId);
			//make a new collection of rooms so we won't pass a reference
			$roomDtos = array();
			foreach($rooms as $key=>$room){
				$roomDtos[] = clone $room;
				$roomDtos[$key]->setPrincipal_investigator_id($pIId);
				$roomDtos[$key]->setHazard_id($dto->getHazard_id());
			}
			$dto->setAndFilterInspectionRooms($roomDtos);
		}
		return $dtos;
	}

	function getPisByHazardAndRoomIDs( $roomIds, $hazardId = null){
		$LOG = Logger::getLogger(__CLASS__);

		// Get the db connection
		global $db;
		$inQuery = implode(',', array_fill(0, count($roomIds), '?'));

        $LOG->fatal("yop");

        $LOG->fatal($inQuery);
		if($hazardId != null){
			$queryString .= " AND hazard_id = $hazardId";
			//get this pi's rooms
			$queryString = 'SELECT *
							FROM principal_investigator
							WHERE key_id IN(select principal_investigator_id from principal_investigator_hazard_room where room_id IN (' . $inQuery . ')';
		}else{
			$queryString = 'SELECT *
							FROM principal_investigator
							WHERE key_id IN(select principal_investigator_id from principal_investigator_room where room_id IN (' . $inQuery . ')';
		}

		$queryString .= ')';


		$stmt = $db->prepare($queryString);
		/*
		if($hazardId != null){
			$stmt->bindValue(":hazardId", $hazardId, PDO::PARAM_INT);
		}	*/
		// bindvalue is 1-indexed, so $k+1
		foreach ($roomIds as $k => $id){
		    $stmt->bindValue(($k+1), $id);
		}
		$stmt->execute();
		$pis = $stmt->fetchAll(PDO::FETCH_CLASS, "PrincipalInvestigator");

		return $pis;
	}


    function getPIHazardRoomsByRoomAndHazardIds($roomIds, $hazardId, $piIds){
        global $db;
        $newPiIds = implode(',', array_fill(0, count($piIds), '?'));
        $queryString = "SELECT a.*,
                        concat(c.first_name, ' ', c.last_name) as piName
                        FROM principal_investigator_hazard_room a
                        JOIN principal_investigator b
                        ON b.key_id = a.principal_investigator_id
                        JOIN erasmus_user c
                        ON c.key_id = b.user_id
                        WHERE a.room_id IN ( $newRoomIds )
                        AND a.principal_investigator_id IN ( $newPiIds )
                        AND a.hazard_id = ?";

		$stmt = $db->prepare($queryString);
        // bindvalue is 1-indexed, so $k+1

		foreach ($roomIds as $k => $id){
		    $stmt->bindValue(($k+1), $id);
		}
        $skips = $k+2;
        // bindvalue is 1-indexed, so $k+1
		foreach ($piIds as $k => $id){
		    $stmt->bindValue(($k+$skips), $id);
		}

        $stmt->bindValue(($k+$skips+1), $hazardId);
        $stmt->execute();
        $piHazRooms = $stmt->fetchAll(PDO::FETCH_CLASS, "PrincipalInvestigatorHazardRoomRelation");

        return $piHazRooms;
    }

    function getCurrentInvetoriesByPiId($id){

        global $db;

        $queryString = "SELECT a.principal_investigator_id as principal_investigator_id,
                        b.isotope_id,
                        b.key_id as authorization_id,
                        ROUND(SUM(d.quantity) ,7) as ordered,
                        c.name as isotope_name,
                        b.form as authorized_form,
                        ROUND(b.max_quantity, 7) as auth_limit,
                        ROUND( b.max_quantity - (SUM(d.quantity) - picked_up.amount_picked_up) ) as max_order,
                        ROUND(picked_up.amount_picked_up, 7) as amount_picked_up,
                        ROUND(SUM(d.quantity) - picked_up.amount_picked_up, 7) as amount_on_hand,
                        ROUND(total_used.amount_picked_up, 7) as amount_disposed,
                        ROUND(SUM(d.quantity) - total_used.amount_picked_up, 7) as usable_amount
                        from pi_authorization a
                        LEFT OUTER JOIN authorization b
                        ON b.pi_authorization_id = a.key_id
                        LEFT OUTER JOIN isotope c
                        ON c.key_id = b.isotope_id
                        LEFT OUTER JOIN parcel d
                        ON d.authorization_id = b.key_id
                        LEFT OUTER JOIN (
	                        select sum(a.curie_level) as amount_picked_up, e.name as isotope, e.key_id as isotope_id
	                        from parcel_use_amount a
	                        join parcel_use b
	                        on a.parcel_use_id = b.key_id
	                        JOIN parcel c
	                        ON b.parcel_id = c.key_id
	                        JOIN authorization d
	                        ON c.authorization_id = d.key_id
	                        JOIN isotope e
	                        ON d.isotope_id = e.key_id
	                        left join waste_bag f
	                        ON a.waste_bag_id = f.key_id
	                        left join carboy_use_cycle g
	                        ON a.carboy_id = g.key_id
	                        left join scint_vial_collection h
	                        ON a.scint_vial_collection_id = h.key_id
	                        join pickup i
	                        ON f.pickup_id = i.key_id
	                        OR g.pickup_id = i.key_id
	                        OR h.pickup_id = i.key_id
	                        AND i.status != 'REQUESTED'
	                        group by e.name, e.key_id
                        ) as picked_up
                        ON picked_up.isotope_id = b.isotope_id
                        LEFT OUTER JOIN (
	                        select sum(a.curie_level) as amount_picked_up, e.name as isotope, e.key_id as isotope_id
	                        from parcel_use_amount a
	                        join parcel_use b
	                        on a.parcel_use_id = b.key_id
	                        JOIN parcel c
	                        ON b.parcel_id = c.key_id
	                        JOIN authorization d
	                        ON c.authorization_id = d.key_id
	                        JOIN isotope e
	                        ON d.isotope_id = e.key_id
	                        group by e.name, e.key_id
                        ) as total_used
                        ON total_used.isotope_id = b.isotope_id
                        where a.principal_investigator_id = ?
                        group by b.key_id, b.form, b.max_quantity,c.name, c.key_id, a.principal_investigator_id";

        $stmt = $db->prepare($queryString);

        $stmt->bindValue(1, $id);
        $stmt->execute();
        $inventories = $stmt->fetchAll(PDO::FETCH_CLASS, "CurrentIsotopeInventoryDto");
        return $inventories;

    }

    /*
     * gets the inspections for an Equipment, of child class of Equipment, that have a due_date or certification_date matching the current year
     *
     * @param Equipment $equipment   Piece of Equipment, or child class thereof
     * @return Array $currentInspections   Array of inspections of $equipment either due or certified in the current year
     *
     */
    public function getCurrentInspectionsByEquipment(Equipment $equipment){
        global $db;
        $queryString = 'select * from equipment_inspection
                        WHERE equipment_class = :class
                        AND equipment_id = :id
                        AND
                        (
							(
								year(certification_date) = EXTRACT(year FROM (NOW()))
								OR year(due_date) = EXTRACT(year FROM (NOW()))
							)
							OR
							(certification_date IS NULL AND due_date IS NULL)

                        );';

        $stmt = $db->prepare($queryString);
        $stmt->bindParam(':class', get_class($equipment), PDO::PARAM_STR);
        $stmt->bindParam(':id', $equipment->getKey_id(), PDO::PARAM_INT);

        $stmt->execute();
        $currentInspections = $stmt->fetchAll(PDO::FETCH_CLASS, "EquipmentInspection");
        return $currentInspections;
    }

}
?>

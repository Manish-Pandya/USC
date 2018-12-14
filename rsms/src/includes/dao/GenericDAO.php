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
		$this->setModelObject($model_object);
	}

	public function setModelObject(GenericCrud $new_model_object) {
		$this->modelObject = $new_model_object;
		$this->modelClassName = get_class($new_model_object);
		$this->logprefix = "[$this->modelClassName" . "DAO]";

		$this->LOG = Logger::getLogger( __CLASS__ . "." . $this->modelClassName );
	}

	/**
	 * @return boolean True if the associated table exists
	 */
	public function doesTableExist(){
		$tableName = $this->modelObject->getTableName();
		$db = DBConnection::get();
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
	 * @param string|integer $id
	 * @return GenericCrud
	 */
	function getById($id){
		if (!$this->validateKeyId($id)) {
			return new ActionError("$this->modelClassName.getById: No ID provided", 404);
		}

		$this->LOG->debug("Looking up entity with keyid '$id'");

		//Prepare to query the table by key_id
		$sql = 'SELECT * FROM ' . $this->modelObject->getTableName() . ' WHERE key_id = ?';
		$this->LOG->debug("Executing: $sql");

		$stmt = DBConnection::prepareStatement($sql);
		$stmt->bindParam(1,$id,PDO::PARAM_INT);
		$stmt->setFetchMode(PDO::FETCH_CLASS, $this->modelClassName);			// Query the db and return one of $this type of object
		if ($stmt->execute()) {
			$result = $stmt->fetch();

			// $result being false indicates no rows returned.
			if(!$result) {
				// 'close' the statment
				$stmt = null;

				$this->LOG->warn("No Rows returned for fetch by ID $id ($sql)");
				//return;
				return new ActionError('No rows returned');
			}

			if( $this->LOG->isTraceEnabled() ){
				$cnt = is_array($result) ? count($result) : $result != null ? 1 : 0;
				$this->LOG->trace("Result count: $cnt");
			}
		// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();
			$result = new QueryError($error);
			$this->LOG->error('Returning QueryError with message: ' . $result->getMessage());
		}

		// 'close' the statment
		$stmt = null;
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

			//Prepare to delete from the table by key_id
			$stmt = DBConnection::prepareStatement('DELETE FROM ' . $this->modelObject->getTableName() . ' WHERE key_id = ?');
			$stmt->bindParam(1,$id,PDO::PARAM_INT);
			if ($stmt->execute()) {
			// ... otherwise, die and echo the db error
			} else {
				$errorInfo = $stmt->errorInfo();
                $object = new ModifyError($errorInfo[2], $object);
                $this->LOG->fatal('Returning ModifyError with message: ' . $object->getMessage());
			}

			// 'close' the statment
			$stmt = null;

			return true;
	}

	/**
	 * Retrieves a list of all entities of this type.
	 *
	 * @return Array of entities
	 */
	function getAll( $sortColumn = NULL, $sortDescending = FALSE, $activeOnly = FALSE ){

		$this->LOG->debug("Looking up all entities" . ($sortColumn == NULL ? '' : ", sorted by $sortColumn"));

		$className = get_class($this);

		//Prepare to query all from the table
		$sql = 'SELECT * FROM ' . $this->modelObject->getTableName() . ' ' . ($activeOnly ? 'WHERE is_active = 1 ' : '') .
	        ($sortColumn == NULL ? '' : " ORDER BY  CAST($sortColumn AS UNSIGNED), $sortColumn " . ($sortDescending ? 'DESC' : 'ASC'));
		$stmt = DBConnection::prepareStatement($sql);

		$this->LOG->debug("Executing: $sql");
		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClassName);
			// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();
			$result = new QueryError($error);
			$this->LOG->error('Returning QueryError with message: ' . $result->getMessage());
		}
        /*
        foreach($result as $r){
            $this->LOG->fatal("hello");
            $this->cacheIfNeeded($r);
        }
		*/

		// 'close' the statment
		$stmt = null;

		$this->LOG->trace("Result: " . count($result));
		return $result;
	}

	function getPage( $paging, $sortColumn = NULL, $sortDescending = FALSE, $activeOnly = FALSE ) {
		$this->LOG->debug("Looking up all entities" . ($sortColumn == NULL ? '' : ", sorted by $sortColumn"));

		//Prepare to query all from the table
		$sql = 'SELECT * FROM ' . $this->modelObject->getTableName() . ' ' . ($activeOnly ? 'WHERE is_active = 1 ' : '') .
				($sortColumn == NULL ? '' : " ORDER BY  CAST($sortColumn AS UNSIGNED), $sortColumn " . ($sortDescending ? 'DESC' : 'ASC'));

		$transformToType = $this->modelClassName;
		return $this->queryPage( $paging, $sql, function($stmt){
            return $stmt->fetchAll(PDO::FETCH_CLASS, $transformToTypee);
		});
	}

	/**
	 * Helper function which executes a query limited to a specific page
	 */
	function queryPage( $paging, $sql, $fetch_fn ){
		if( $paging != null ){
			// First, count the records
			$count_stmt = DBConnection::prepareStatement($sql);
			$count_stmt->execute();
			$total_results_count = $count_stmt->rowCount();

			$page = $paging['page'];
			$recordsPerPage = $paging['size'] ? $paging['size'] : 100;
			$fromRecordNum = ($recordsPerPage * $page) - $recordsPerPage;

			// Amend the data selection to limit to a page
			$sql .= " LIMIT $fromRecordNum, $recordsPerPage";

			// 'close' the statment
			$count_stmt = null;
		}

		$stmt = DBConnection::prepareStatement($sql);

		$this->LOG->debug("Executing: $sql");
		if ($stmt->execute() ) {
			$result = call_user_func($fetch_fn, $stmt);
		} else {
			$error = $stmt->errorInfo();
			$result = new QueryError($error);
			$this->LOG->error('Returning QueryError with message: ' . $result->getMessage());
		}

		// 'close' the statment
		$stmt = null;

		$this->LOG->trace("Result: " . count($result));

		// Wrap result object with Paging info
		return new ResultPage($result, $total_results_count, $page, $recordsPerPage);
	}

    function cacheIfNeeded(GenericCrud $target){
        return;
        $db = DBConnection::get();

        $id = $target->getKey_id();
        $class = get_class($target);
        $stmt = DBConnection::prepareStatement("SELECT * FROM dumb_cache WHERE parent_id = '$id' AND parent_class = '$class'");
        // Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClassName);
			// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();
			$result = new QueryError($error);
			$this->LOG->fatal('Returning QueryError with message: ' . $result->getMessage());
		}
        if(count($result) == 0 ){

            $value = json_encode(JsonManager::encode($target));
            $stmt = DBConnection::prepareStatement("INSERT INTO dumb_cache (parent_id, parent_class, cached_value) VALUES ('$id', '$class', '$value');" );
            // Query the db and return an array of $this type of object
            if ($stmt->execute() ) {
                $this->LOG->fatal('success');

                // ... otherwise, generate error message to be returned
            } else {
                $error = $stmt->errorInfo();
                $result = new QueryError($error);
                $this->LOG->fatal('Returning QueryError with message: ' . $result->getMessage());
            }
        }

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
		$this->LOG->debug("getAllWhere");

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
				}else if ($clause->getOperator() == "IN"){
                    if(is_array($clause->getVal())){
                        $values = $clause->getVal();
                        $inQuery = implode(',', array_fill(0, count($values), '?'));
                        $sql .= "($inQuery)";
                    }
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
		//Prepare to query all from the table
		$this->LOG->debug("Executing: $sql");
		$stmt = DBConnection::prepareStatement($sql);

		$i = 1;
		foreach($whereClauses as $clause){
			if($clause->getVal() != NULL && strtolower($clause->getVal()) != "null") {
                if( !is_array( $clause->getVal() ) ){
				    $stmt->bindValue( $i, $clause->getVal() );
				    $i++;
                }else{
                    foreach($clause->getVal() as $val){
                        $stmt->bindValue( $i, $val );
                        $i++;
                    }
                }
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
            return $stmt->debugDumpParams();

		}

		// 'close' the statment
		$stmt = null;

		$this->LOG->debug("Retrieved " . count($result) . " Results");
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

		// get the relationship parameters needed to build the query
		$className		= $relationship->getClassName();
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();
		$modelObject    = $this->modelObject;

		$sql = "SELECT * FROM " . $modelObject->getTableName() . " WHERE is_active = 1 OR key_id IN(SELECT $foreignKeyName FROM $tableName)";

		$stmt = DBConnection::prepareStatement($sql);

		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClassName);
			// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();
			$result = new QueryError($error);
			$this->LOG->error('Returning QueryError with message: ' . $result->getMessage());
		}
        foreach($result as $r){
            $this->cacheIfNeeded($r);
		}
		
		// 'close' the statment
		$stmt = null;

		return $result;
	}

	/**
	 * Commits the values of this entity to the database
	 *
	 * @param GenericCrud $object: Object to save. If null, the model object will be used instead
	 * @return GenericCrud
	 */
	function save(GenericCrud $object = NULL){
		$this->LOG->debug("Saving entity: $object");
		if( $this->LOG->isTraceEnabled()){
			$this->LOG->trace($object);
		}

		//Make sure we have an object to save
		if( $object == NULL ){
			$object = $this->modelObject;
		}

		//If $object is given, make sure it's the right type
		else if( get_class($object) != $this->modelClassName ){
			// we have a problem!
			$this->LOG->fatal("Attempting to save entity of class " . get_class($object) . ", which does not match model object class of $this->modelClassName");
            $this->LOG->fatal($object);
			return new ModifyError("Entity did not match model object class", $object);
		}
		//else use $object as-is!

		// Get DB Connection
		$db = DBConnection::get();

		// update the modify timetamp
		$object->setDate_last_modified(date("Y-m-d H:i:s"));

		// Add the creation timestamp
		if ($object->getDate_created() == null) {
			$object->setDate_created(date("Y-m-d H:i:s"));
		}

		//set created user and last modified user ids if we can and need to
		if(isset($_SESSION["USER"]) && $_SESSION["USER"]->getKey_id() != null){
			$object->setLast_modified_user_id($_SESSION["USER"]->getKey_id());
			if($object->getCreated_user_id() == null)
				$object->setCreated_user_id($_SESSION["USER"]->getKey_id());
		}

		// Check to see if this item has a key_id
		//  If it does, we assume it's an existing record and issue an UPDATE
		if ($object->getKey_id() != null) {
			$this->LOG->debug("Entity exists; UPDATE");
			$stmt = $this->createUpdateStatement($db,$object);
			$stmt = $this->bindColumns($stmt,$object);
			$success = $stmt->execute();
		// Otherwise, issue an INSERT
		} else {
			$this->LOG->debug("Entity does not exist; INSERT");

	    	// Add the creation timestamp
	    	$object->setDate_created(date("Y-m-d H:i:s"));

			$stmt = $this->createInsertStatement($db,$object);
			$stmt = $this->bindColumns($stmt,$object);
			$success = $stmt->execute();

			// since this is a new record, get the new key_id issued by the database and add it to this object.
			$this->LOG->debug("Set key ID of new entity");
			$object->setKey_id($db->lastInsertId());
		}

		// Look for db errors
		// If no errors, update and return the object
		if($success && $object->getKey_Id() > 0) {
			$this->LOG->trace("Successfully updated or inserted entity with key_id=" . $object->getKey_Id());

			// Re-load the whole record so that updated Date fields (and any field auto-set by DB) are updated
			//$this->LOG->trace("$this->logprefix Reloading updated/inserted entity with key_id=" . $object->getKey_Id() );
			$object = $this->getById( $object->getKey_Id() );

		// Otherwise, the statement failed to execute, so return an error
		} else {
			$this->LOG->trace("Failed to update/insert entity. Object had a key_id of " . $object->getKey_Id());
			$errorInfo = $stmt->errorInfo();

			$object = new ModifyError($errorInfo[2], $object);
			$this->LOG->error('Returning ModifyError with message: ' . $object->getMessage());
			$this->LOG->fatal($object);
		}
		
		// 'close' the statment
		$stmt = null;

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
	public function getRelatedItemsById($id, DataRelationship $relationship, $sortColumns = null, $activeOnly = false, $activeOnlyRelated = false, $limit=0){

		if (empty($id)) { return array();}

		// get the relationship parameters needed to build the query
		$className		= $relationship->getClassName();
		$classInstance  = new $className();
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();
        if($relationship->orderColumn != null) {
            $orderColumn = $relationship->orderColumn;
            $class = $this->modelClassName;
            $parentTable = $class::$TABLE_NAME;
            $parentId = $this->modelObject->getKey_id();
        }
		$modelObject    = $this->modelObject;
		//$this->LOG->error("$this->logprefix Retrieving related items for " . get_class($modelObject) . " entity with id=$id");

		$whereTag = $activeOnly ? " WHERE is_active = 1 AND " : " WHERE ";
		//$sql = "SELECT * FROM " . $modelObject->getTableName() . $whereTag . "key_id IN(SELECT $keyName FROM $tableName WHERE $foreignKeyName = $id";
		if(!isset($orderColumn)){
            $sql = "SELECT * FROM " . $classInstance->getTableName() . $whereTag . "key_id IN(SELECT $keyName FROM $tableName WHERE $foreignKeyName = $id";
            $sql .= $activeOnlyRelated ? " AND is_active = 1)" : ")";
        }else{
            $this->LOG->trace("getting ordered");
            $this->LOG->trace($this->modelObject);
            /*
             * SELECT a.*, b.order_index as order_index
            FROM rad_condition a
            LEFT OUTER JOIN pi_authorization_rad_condition b
            ON b.condition_id = a.key_id
            LEFT OUTER JOIN pi_authorization c
            ON c.key_id = b.pi_authorization_id
            where c.key_id = 55;
             */
            $sql = "SELECT a.*, b.$orderColumn as $orderColumn
                    FROM " . $classInstance->getTableName() . " a
                    LEFT OUTER JOIN $tableName b
                    ON a.key_id = b.$keyName
                    LEFT OUTER JOIN $parentTable c
                    ON c.key_id = b.$foreignKeyName
                    WHERE c.key_id = $parentId";
            /*
            $sql = "SELECT a.*, b.$orderColumn as $orderColumn
                    FROM a." . $classInstance->getTableName() . $whereTag . "key_id IN(SELECT $keyName FROM $tableName b WHERE $foreignKeyName = $id";
        */
        }

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

		if( $limit > 0 ){
			$sql .= " LIMIT $limit";
		}

		$this->LOG->debug("Executing: $sql");
		$stmt = DBConnection::prepareStatement($sql);

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
		
		// 'close' the statment
		$stmt = null;

		$this->LOG->debug("Retrieved " . count($result) . " results");
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
	function addRelatedItems($key_id, $foreignKey_id, DataRelationship $relationship, $index = null) {
		$this->LOG->debug("Inserting new related item: fkey=$foreignKey_id and key_id=$key_id: $relationship");

		//print_r($relationship);
		// get the relationship parameters needed to build the query
		$className		= $relationship->getClassName();
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();
        if($relationship->orderColumn != null) $orderColumn = $relationship->orderColumn;

		if($index == null || !isset($orderColumn)){
            $sql = "INSERT INTO  $tableName ($foreignKeyName, $keyName) VALUES (:foreignKey_id, :key_id) ";
        }else{
            $this->LOG->fatal("inserting in ELSE");
            $this->LOG->fatal($foreignKeyName . " " . $keyName . " " . $orderColumn );
            $this->LOG->fatal($foreignKey_id . " " . $key_id . " " . $index );
            $sql = "INSERT INTO  $tableName ( $foreignKeyName, $keyName, $orderColumn) VALUES ( :foreignKey_id, :key_id, :index) ";
        }
		//$this->LOG->trace("Preparing insert statement [$sql]");

		$stmt = DBConnection::prepareStatement($sql);
		// Bind the params.
		$stmt->bindParam(":foreignKey_id",$foreignKey_id,PDO::PARAM_INT);
		$stmt->bindParam(":key_id",$key_id,PDO::PARAM_INT);
        if(isset($index)) $stmt->bindParam(":index", $index, PDO::PARAM_INT);

		// Insert the record and return true
		if ($stmt->execute() ) {
		
			// 'close' the statment
			$stmt = null;

			//$this->LOG->trace( "Inserted new related item with key_id [$key_id]");
			return true;
		// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();

			// 'close' the statment
			$stmt = null;

			// create modify error with human readable error message
			$result = new ModifyError($error[2]);
			$this->LOG->fatal('Returning ModifyError with message: ' . $result->getMessage());
			return $result;
		}
	}

	function removeRelatedItems($key_id, $foreignKey_id, DataRelationship $relationship) {
		$this->LOG->debug("$this->logprefix Removing related item for entity with id=$foreignKey_id");

		//print_r($relationship);
		// get the relationship parameters needed to build the query
		$className		= $relationship->getClassName();
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();

		$sql = "DELETE FROM $tableName WHERE $foreignKeyName =  :foreignKey_id AND $keyName = :key_id";
		//$this->LOG->trace("DELETE FROM $tableName WHERE $foreignKeyName =  $foreignKey_id AND $keyName = $key_id");

		$stmt = DBConnection::prepareStatement($sql);
		// Bind the params.
		$stmt->bindParam(":foreignKey_id",$foreignKey_id,PDO::PARAM_INT);
		$stmt->bindParam(":key_id",$key_id,PDO::PARAM_INT);

		// Delete the record and return true
		if ($stmt->execute() ) {
			// 'close' the statment
			$stmt = null;

			//$this->LOG->trace( "Remove related item with key_id [$key_id]");
			return true;
		// ... otherwise, generate an error message to be returned
		} else {		
			// 'close' the statment
			$stmt = null;

			$error = $stmt->errorInfo();

			// create modify error with human readable error message
			$result = new ModifyError($error[2]);
			$this->LOG->error('Returning ModifyError with message: ' . $result->getMessage());

			return $result;
		}
	}

	function bindColumns($stmt,$object) {
		$this->LOG->debug("Binding columns for $object");
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

			// build the binding statement.
			$getter_value = $object->$getter();

			$this->LOG->trace("Binding $key (a $value) as PDO type $type");
			$stmt->bindValue(":" . $key, $getter_value, $type);
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

		if( $this->LOG->isTraceEnabled() ){
			$this->LOG->trace("Preparing insert statement [$sql]");
		}

		$stmt = DBConnection::prepareStatement($sql);

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
		$stmt = DBConnection::prepareStatement($sql);
		//var_export($stmt->queryString);
		return $stmt;
	}

	// TODO: The following are not generic functions, and should be moved into their own DAO type

	function getUserByUsername($username){
		$this->LOG->debug("Looking up user with username $username");

		$user = new User();

		//Prepare to query the user table by username
		$stmt = DBConnection::prepareStatement('SELECT * FROM ' . $user->getTableName() . ' WHERE username = ?');
		$stmt->bindParam(1,$username,PDO::PARAM_STR);
		$stmt->setFetchMode(PDO::FETCH_CLASS, "User");			// Query the db and return one user
		if ($stmt->execute()) {
			$result = $stmt->fetch();
			// ... otherwise, generate error message to be returned
		} else {
			$error = $stmt->errorInfo();
			$this->LOG->error('Returning QueryError with message: ' . $error->getMessage());

			$result = new QueryError($error[2]);
		}

		// 'close' the statment
		$stmt = null;

		return $result;
	}

	public function getInspectionSchedule($year = NULL){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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

		//Prepare to query all from the table
		//$stmt = DBConnection::prepareStatement('SELECT * FROM pi_rooms_buildings WHERE year = ? ORDER BY campus_name, building_name, pi_name');
        $sql = "select `a`.`key_id` AS `pi_key_id`,
                concat(`b`.`last_name`,', ',`b`.`first_name`) AS `pi_name`,
                `d`.`name` AS `building_name`,
                `d`.`key_id` AS `building_key_id`,
                `e`.`name` AS `campus_name`,
                `e`.`key_id` AS `campus_key_id`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 1)) AS `bio_hazards_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10009)) AS `chem_hazards_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10010)) AS `rad_hazards_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10016)) AS `lasers_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10015)) AS `xrays_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 2)) AS `recombinant_dna_present`,
                bit_or(
                    c.key_id in(
                    select room_id from principal_investigator_room q
				    where q.principal_investigator_id = a.key_id
                    AND q.principal_investigator_id in
                    (select principal_investigator_id from principal_investigator_department where department_id = 2)
			    )) AS `animal_facility`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id IN (10430, 10433))) AS `toxic_gas_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10434)) AS `corrosive_gas_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10435)) AS `flammable_gas_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id IN(10677,10679))) AS `hf_present`,

                year(curdate()) AS `year`,
                NULL AS `inspection_id` from (((((`principal_investigator` `a` join `erasmus_user` `b`) join `room` `c`) join `building` `d`) join `campus` `e`) join `principal_investigator_room` `f`)
                where ((`a`.`is_active` = 1) and (`c`.`is_active` = 1) and (`b`.`key_id` = `a`.`user_id`) and (`f`.`principal_investigator_id` = `a`.`key_id`) and (`f`.`room_id` = `c`.`key_id`) and (`c`.`building_id` = `d`.`key_id`) and (`d`.`campus_id` = `e`.`key_id`) and

                (not(`a`.`key_id` in
                (select `inspection`.`principal_investigator_id`
                from `inspection`
                where
                `d`.`key_id` IN (
					select `building_id` from `room`
                    where `room`.key_id IN (
                    select `room_id` from inspection_room where inspection_id IN(
                    select key_id from inspection where key_id IN (
						select key_id from inspection where principal_investigator_id = a.key_id
                    )
                    AND
					(coalesce(year(`inspection`.`date_started`),
					`inspection`.`schedule_year`) = ?)) AND (is_rad IS NULL OR is_rad = 0)
                    )
                ))

                ))) group by `a`.`key_id`,concat(`b`.`last_name`,', ',`b`.`first_name`),`d`.`name`,`d`.`key_id`,`e`.`name`,`e`.`key_id`,year(curdate()),

				NULL union select `a`.`key_id` AS `pi_key_id`,
                concat(`b`.`last_name`,', ',`b`.`first_name`) AS `pi_name`,
                `d`.`name` AS `building_name`,
                `d`.`key_id` AS `building_key_id`,
                `e`.`name` AS `campus_name`,`e`.`key_id` AS `campus_key_id`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 1)) AS `bio_hazards_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10009)) AS `chem_hazards_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10010)) AS `rad_hazards_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10016)) AS `lasers_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10015)) AS `xrays_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 2)) AS `recombinant_dna_present`,
                bit_or(
                    c.key_id in(
                    select room_id from principal_investigator_room q
				    where q.principal_investigator_id = a.key_id
                    AND q.principal_investigator_id in
                    (select principal_investigator_id from principal_investigator_department where department_id = 2)
			    )) AS `animal_facility`,

                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id IN (10430, 10433))) AS `toxic_gas_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10434)) AS `corrosive_gas_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id = 10435)) AS `flammable_gas_present`,
                bit_or(c.key_id IN (select room_id from principal_investigator_hazard_room where hazard_id IN(10677,10679))) AS `hf_present`,


                coalesce(year(`g`.`date_started`),`g`.`schedule_year`) AS `year`,`g`.`key_id`
                AS `inspection_id`
                from ((((((`principal_investigator` `a` join `erasmus_user` `b`) join `room` `c`) join `building` `d`) join `campus` `e`) join `inspection_room` `f`) join `inspection` `g`)
                where ((`a`.`key_id` = `g`.`principal_investigator_id`) and (`b`.`key_id` = `a`.`user_id`) and (`g`.`key_id` = `f`.`inspection_id`) and (`c`.`key_id` = `f`.`room_id`)
                and (`c`.`building_id` = `d`.`key_id`) and (`d`.`campus_id` = `e`.`key_id`) and (coalesce(year(`g`.`date_started`),
                `g`.`schedule_year`) = ?) )
                group by `a`.`key_id`,concat(`b`.`last_name`,', ',`b`.`first_name`),`d`.`name`,`d`.`key_id`,`e`.`name`,`e`.`key_id`,coalesce(year(`g`.`date_started`),`g`.`schedule_year`),`f`.`inspection_id` ORDER BY campus_name, building_name, pi_name";
        $stmt = DBConnection::prepareStatement($sql);
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
		
		// 'close' the statment
		$stmt = null;

		return $result;
	}

    function getInspectionsByYear($year){
        //`inspection` where (coalesce(year(`inspection`.`date_started`),`inspection`.`schedule_year`) = ?)

		//Prepare to query all from the table
		//$stmt = DBConnection::prepareStatement('SELECT * FROM pi_rooms_buildings WHERE year = ? ORDER BY campus_name, building_name, pi_name');
        $sql = "select * from inspection where (coalesce(year(`inspection`.`date_started`),`inspection`.`schedule_year`) = ?)";
        $stmt = DBConnection::prepareStatement($sql);
		$stmt->bindParam(1,$year,PDO::PARAM_STR);

		// Query the db and return an array of $this type of object
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_CLASS, "Inspection");
			// ... otherwise, die and echo the db error
		} else {
			$error = $stmt->errorInfo();
			die($error[2]);
		}

		// 'close' the statment
		$stmt = null;

		return $result;
    }

	function getPIsByHazard($rooms = NULL){

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

		if(!isset($roomsCSV) || empty($roomsCSV)){
			$sql .= " IN(SELECT principal_investigator_id from principal_investigator_room WHERE room_id IN(SELECT room_id FROM hazard_room WHERE hazard_id = ".$hazard->getKey_id().") )";
		}else{
			$sql .= " IN(SELECT principal_investigator_id from principal_investigator_room WHERE room_id IN($roomsCSV))";
		}

		$this->LOG->debug("Preparing SQL: $sql");
		$stmt = DBConnection::prepareStatement($sql);

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
		
		// 'close' the statment
		$stmt = null;

		return $result;
	}

	function getAllLocations(){
		$LOG = Logger::getLogger(__CLASS__);

		$this->has_hazards = false;

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
		$stmt = DBConnection::prepareStatement($queryString);
		$stmt->execute();
		$locations = $stmt->fetchAll(PDO::FETCH_CLASS, "LocationsDto");
		
		// 'close' the statment
		$stmt = null;

		return $locations;
	}

	function getCampusCountsForDepartment( $deptId ){
		$LOG = Logger::getLogger(__CLASS__);

		$queryString = "SELECT
			dept.name as department_name,
			dept.is_active as is_active,
			campus.name as campus_name,
			dept.key_id as department_id,
			COALESCE(dept.specialty_lab, false) as specialty_lab,
			campus.key_id as campus_id,
			count(distinct room.key_id) room_count,
			count(distinct pi_room.principal_investigator_id) pi_count,
			count(distinct building.key_id) building_count

		FROM department dept
		LEFT OUTER JOIN principal_investigator_department pi_dept
			ON (dept.key_id = pi_dept.department_id)
		LEFT OUTER JOIN principal_investigator_room pi_room
			ON (pi_room.principal_investigator_id = pi_dept.principal_investigator_id)
		LEFT OUTER JOIN room room
			ON (room.key_id = pi_room.room_id)
		LEFT OUTER JOIN building building
			ON (building.key_id = room.building_id)
		LEFT OUTER JOIN campus campus
			ON (campus.key_id = building.campus_id)

		WHERE pi_room.room_id IS NOT NULL AND dept.key_id = :deptId

		GROUP BY campus.name, dept.name
		ORDER BY dept.name, campus.name";

		$stmt = DBConnection::prepareStatement($queryString);
		$stmt->bindParam(":deptId", $deptId, PDO::PARAM_INT);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_CLASS, "DepartmentCampusInfoDto");

		// 'close' the statment
		$stmt = null;

		return $data;
	}

	// FIXME: Deprecate this function
	function getAllDepartmentsAndCounts(){
		$LOG = Logger::getLogger(__CLASS__);

		$this->has_hazards = false;

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
		$stmt = DBConnection::prepareStatement($queryString);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_CLASS, "DepartmentDto");
		
		// 'close' the statment
		$stmt = null;

		return $data;
	}

	// FIXME: Deprecate this function
	function getDepartmentDtoById( $id ){
		$LOG = Logger::getLogger(__CLASS__);

		$this->has_hazards = false;

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
		$stmt = DBConnection::prepareStatement($queryString);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_CLASS, "DepartmentDto");
		
		// 'close' the statment
		$stmt = null;

		return $data;
	}

	public function getDepartmentsByCampusId(){

		$queryString = "SELECT a.key_id as department_id, a.name as department_name, a.is_active, a.specialty_lab,
						g.name as campus_name, g.key_id as campus_id
						FROM department a
						LEFT JOIN principal_investigator_department d ON (a.key_id = d.department_id)
						LEFT JOIN principal_investigator b ON (d.principal_investigator_id = b.key_id) AND b.is_active = 1
						LEFT JOIN principal_investigator_room e ON (b.key_id = e.principal_investigator_id)
						LEFT JOIN building f ON (e.key_id = e.room_id)
						LEFT JOIN campus g ON (g.key_id = f.campus_id);";
		$stmt = DBConnection::prepareStatement($queryString);
		//$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$data = $stmt->fetchAll(PDO::FETCH_CLASS, "DepartmentDto");

		// 'close' the statement
		$stmt = null;

		return $data;
	}

	function getHazardRoomDtosByPIId( $pIId, $roomIds = null ){
		$LOG = Logger::getLogger(__CLASS__);
        $LOG->info( "Get Hazard Rooms (" . ($roomIds == null ? '' : implode(', ', $roomIds)) . ") for PI #$pIId");

		//get this pi's rooms
		if($roomIds == null){
			$roomsQueryString = "SELECT a.key_id as room_id, a.building_id, a.name as room_name, COALESCE(NULLIF(b.alias, ''), b.name) as building_name from room a
								 LEFT JOIN building b on a.building_id = b.key_id
								 where a.key_id in (select room_id from principal_investigator_room where principal_investigator_id = :id)";
			$stmt = DBConnection::prepareStatement($roomsQueryString);
			$stmt->bindParam(':id', $pIId, PDO::PARAM_INT);
		}else{
            $inQuery = implode(',', array_fill(0, count($roomIds), '?'));

			$roomsQueryString = "SELECT a.key_id as room_id, a.building_id, a.name as room_name, COALESCE(NULLIF(b.alias, ''), b.name) as building_name from room a
								 LEFT JOIN building b on a.building_id = b.key_id
								 where a.key_id IN ";
            $roomsQueryString .= "($inQuery)";
			$stmt = DBConnection::prepareStatement($roomsQueryString);
            foreach($roomIds as $key=>$id){
            	$stmt->bindValue($key+1, $id, PDO::PARAM_INT);
            }
		}
		if($stmt->execute()){
            $rooms = $stmt->fetchAll(PDO::FETCH_CLASS, "PIHazardRoomDto");
        }else{
			// 'close' the statement
			$stmt = null;

			$error = $stmt->errorInfo();
			$result = new QueryError($error);
			$this->LOG->fatal('Returning QueryError with message: ' . $result->getMessage());
            return $result;
		}

		// 'close' the statement
		$stmt = null;

		$roomIds = array();
		foreach($rooms as $room){
			$roomIds[] = $room->getRoom_id();
		}

		//get a dto for every hazard
		$queryString = "SELECT key_id as hazard_id, order_index, key_id, name as hazard_name, is_equipment, parent_hazard_id as parent_hazard_id, (SELECT EXISTS(SELECT 1 from hazard where parent_hazard_id = hazard_id) ) as hasChildren from hazard WHERE is_active = 1;";
		$stmt = DBConnection::prepareStatement($queryString);
		$stmt->execute();
		$dtos = $stmt->fetchAll(PDO::FETCH_CLASS, "HazardDto");

		// 'close' the statement
		$stmt = null;

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

		$inQuery = implode(',', array_fill(0, count($roomIds), '?'));

        $LOG->fatal("yop");

        $LOG->fatal($inQuery);
		if($hazardId != null){
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


		$stmt = DBConnection::prepareStatement($queryString);
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

		// 'close' the statement
		$stmt = null;

		return $pis;
	}

    function getPiHazardRoomsByPiAndHazard($piId, $hazardId){
        $query = "SELECT * from principal_investigator_hazard_room WHERE hazard_id = ? AND principal_investigator_id = ?";
        $stmt = DBConnection::prepareStatement($query);
        $stmt->bindValue(1, $hazardId);
        $stmt->bindValue(2, $piId);
        if($stmt->execute()){
            $result = $stmt->fetchAll(PDO::FETCH_CLASS, "PrincipalInvestigatorHazardRoomRelation");
        }else{
            $error = $stmt->errorInfo();
			$result = new QueryError($error);
			$this->LOG->error('Returning QueryError with message: ' . $result->getMessage());
		}

		// 'close' the statement
		$stmt = null;

        return $result;
    }

    function getPIHazardRoomsByRoomAndHazardIds($roomIds, $hazardId, $piIds){
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

		$stmt = DBConnection::prepareStatement($queryString);
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

		// 'close' the statement
		$stmt = null;

        return $piHazRooms;
    }

    function getPendingHazardChangeByVerificationAndHazard($hazardId,  $verificationId){
        $l = Logger::getLogger(__FUNCTION__);
        $l->fatal("CALLED");
        $l->fatal($hazardId);
        $l->fatal($verificationId);

        $query = "select * from pending_change where hazard_id = ? AND verification_id = ?";
        $l->fatal($query);
        $stmt = DBConnection::prepareStatement($query);
        $stmt->bindValue(1, $hazardId);
        $stmt->bindValue(2, $verificationId);
        if($stmt->execute()){
            $result = $stmt->fetchAll(PDO::FETCH_CLASS, "PendingHazardDtoChange");
        }else{
            $error = $stmt->errorInfo();
			$result = new QueryError($error);
			$this->LOG->error('Returning QueryError with message: ' . $result->getMessage());
		}
        $l->fatal($result);

		// 'close' the statement
		$stmt = null;

        return $result;
    }

    function getCurrentInvetoriesByPiId($piId, $authId){

		$queryString = "SELECT
			a.principal_investigator_id as principal_investigator_id,
			b.isotope_id,
			b.key_id as authorization_id,
			ROUND(SUM(d.quantity) ,7) as ordered,
			c.name as isotope_name,
			ROUND(b.max_quantity, 7) as auth_limit,
			ROUND( b.max_quantity - (SUM(d.quantity) - picked_up.amount_picked_up-amount_transferred.amount_used) ) as max_order,

			other_disposed.other_amount_disposed as _other_disposed,
			picked_up.amount_picked_up as _picked_up,
			amount_transferred.amount_used as _transferred,

			ROUND(COALESCE(picked_up.amount_picked_up, 0) + COALESCE(other_disposed.other_amount_disposed, 0), 7) as amount_picked_up,
			ROUND(SUM(d.quantity) - picked_up.amount_picked_up, 7) as amount_on_hand,
			ROUND(total_used.amount_used, 7) as amount_disposed,
			ROUND(SUM(d.quantity) - total_used.amount_used, 7) as usable_amount,
			ROUND(amount_transferred.amount_used, 7) as amount_transferred

			from pi_authorization a

			LEFT OUTER JOIN authorization b
			ON b.pi_authorization_id = a.key_id

			LEFT OUTER JOIN isotope c
			ON c.key_id = b.isotope_id

			LEFT OUTER JOIN parcel d
			ON d.authorization_id = b.key_id AND d.status IN ('Delivered')

			LEFT OUTER JOIN (
				select sum(a.curie_level) as amount_picked_up,
				e.name as isotope,
				e.key_id as isotope_id
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
				left join other_waste_container owc
					ON a.other_waste_container_id = owc.key_id
				left join pickup i
					ON f.pickup_id = i.key_id
					OR g.pickup_id = i.key_id
					OR h.pickup_id = i.key_id
				WHERE i.principal_investigator_id = ?
					AND (i.status != 'REQUESTED' OR owc.close_date IS NOT NULL)
					AND b.is_active = 1
					AND a.is_active = 1
				group by e.name, e.key_id, d.isotope_id
			) as picked_up
			ON picked_up.isotope_id = b.isotope_id

			LEFT OUTER JOIN (
				select sum(a.curie_level) as other_amount_disposed,
				e.name as isotope,
				e.key_id as isotope_id
				from parcel_use_amount a
				join parcel_use b
					on a.parcel_use_id = b.key_id AND b.is_active = 1
				JOIN parcel c
					ON b.parcel_id = c.key_id
				JOIN authorization d
					ON c.authorization_id = d.key_id
				JOIN isotope e
					ON d.isotope_id = e.key_id
				join other_waste_container owc
					ON a.other_waste_container_id = owc.key_id AND owc.close_date IS NOT NULL
				WHERE c.principal_investigator_id = ?
					AND b.is_active = 1
					AND a.is_active = 1
				group by e.name, e.key_id, d.isotope_id
			) other_disposed
			ON other_disposed.isotope_id = b.isotope_id

			-- RSMS-780 Join to experiment uses (do not check parcel_use_amount, as this is too granular since parcel_use specifis enough)
			LEFT OUTER JOIN (
				select sum(b.quantity) as amount_used,
				e.name as isotope,
				e.key_id as isotope_id
				from parcel_use b
				JOIN parcel c
					ON b.parcel_id = c.key_id
				JOIN authorization d
					ON c.authorization_id = d.key_id
				JOIN isotope e
					ON d.isotope_id = e.key_id
				WHERE c.principal_investigator_id = ?
					AND b.date_transferred IS NULL
					AND b.is_active = 1
				group by e.name, e.key_id, d.isotope_id
			) as total_used
			ON total_used.isotope_id = b.isotope_id


			LEFT OUTER JOIN (
				select sum(a.curie_level) as amount_used,
				e.name as isotope,
				e.key_id as isotope_id
				from parcel_use_amount a
				join parcel_use b
					on a.parcel_use_id = b.key_id
				JOIN parcel c
					ON b.parcel_id = c.key_id
				JOIN authorization d
					ON c.authorization_id = d.key_id
				JOIN isotope e
					ON d.isotope_id = e.key_id
				WHERE c.principal_investigator_id = ?
					AND a.waste_type_id = 6
				group by e.name, e.key_id, d.isotope_id
			) as amount_transferred
			ON amount_transferred.isotope_id = b.isotope_id

			where a.key_id = ?

			group by b.isotope_id ,c.name, c.key_id, a.principal_investigator_id";

        $stmt = DBConnection::prepareStatement($queryString);

        $stmt->bindValue(1, $piId);
        $stmt->bindValue(2, $piId);
        $stmt->bindValue(3, $piId);
        $stmt->bindValue(4, $piId);
		$stmt->bindValue(5, $authId);

		if( $this->LOG->isDebugEnabled()){
			$this->LOG->debug("Executing SQL with params piId=$piId: " . $queryString);
		}

        $stmt->execute();
        $inventories = $stmt->fetchAll(PDO::FETCH_CLASS, "CurrentIsotopeInventoryDto");

		// 'close' the statement
		$stmt = null;

        return $inventories;

    }

    public function getIsotopeTotalsReport(){
		$this->LOG->info('Building Isotope Report');

        $queryString = "SELECT
			isotope_id,
			isotope_name,
			auth_limit,
			ROUND( COALESCE(SUM( parcel_quantity ), 0), 7) AS total_ordered,
			ROUND( SUM( total_disposed ), 7) AS disposed,
			ROUND( SUM( total_waste ), 7) AS waste,
			ROUND( COALESCE(SUM( parcel_quantity ), 0) - SUM( total_disposed ), 7) AS total_quantity,
			ROUND( COALESCE(SUM( parcel_quantity ), 0) - SUM( total_disposed ) - SUM( total_waste ), 7) AS total_unused
		FROM (
			SELECT
				isotope_id,
				isotope_name,
				auth_limit,
				parcel_id,
				parcel_quantity - COALESCE(SUM( transfer_amount ), 0) AS parcel_quantity,
				COALESCE(SUM( use_quantity ), 0) AS amount,
				COALESCE(SUM( disposed_amount ), 0) AS total_disposed,
				COALESCE(SUM( waste_amount ), 0) AS total_waste
			FROM(
				SELECT
					iso.key_id AS isotope_id,
					iso.name AS isotope_name,
					iso.auth_limit AS auth_limit,
					parcel.key_id AS parcel_id,
					parcel.quantity AS parcel_quantity,
                    parcel.comments AS parcel_comments,
                    use_log.key_id AS use_log_id,
					amt.curie_level AS use_quantity,
                    amt.key_id AS use_amt_id,
                    amt.comments AS transfer_comments,
					IF(container.is_disposed = 1, amt.curie_level, 0) AS disposed_amount,
                    IF(amt.waste_type_id = 6, amt.curie_level, 0) AS transfer_amount,
					IF(amt.waste_type_id != 6 && IFNULL(container.is_disposed, 0) = 0, amt.curie_level, 0) AS waste_amount
				FROM isotope iso
		
				LEFT OUTER JOIN parcel parcel ON
				parcel.authorization_id in (SELECT key_id FROM authorization WHERE isotope_id = iso.key_id)
				AND parcel.status IN('Arrived', 'Wipe Tested', 'Delivered')
	
				LEFT OUTER JOIN parcel_use use_log ON parcel.key_id = use_log.parcel_id

				-- Granulate to individual usage amounts
				LEFT OUTER JOIN parcel_use_amount amt ON amt.is_active AND use_log.key_id = amt.parcel_use_id

				-- Generalize container usages, determine if they're disposed or not
				LEFT OUTER JOIN (
					SELECT
						c.type AS type,
						c.key_id AS key_id,
						c.close_date AS close_date,
						c.pickup_id AS pickup_id,
						c.drum_id AS drum_id,
						c.carboy_pour_date AS carboy_pour_date,
						drum.pickup_date AS drum_ship_date,
						IF(	c.carboy_pour_date IS NOT NULL
							OR (c.drum_id IS NOT NULL AND drum.pickup_date IS NOT NULL)
							OR (c.type = 'other_waste_container' AND c.close_date IS NOT NULL), 1, 0
                        ) AS is_disposed
					FROM (
						SELECT 'scint_vial_collection' AS type, key_id, pickup_id, close_date, drum_id, NULL AS carboy_status, NULL AS carboy_pour_date FROM scint_vial_collection   UNION ALL
						SELECT 'waste_bag'             AS type, key_id, pickup_id, close_date, drum_id, NULL AS carboy_status, NULL AS carboy_pour_date FROM waste_bag               UNION ALL
						SELECT 'other_waste_container' AS type, key_id, pickup_id, close_date, drum_id, NULL AS carboy_status, NULL AS carboy_pour_date FROM other_waste_container   UNION ALL
						SELECT 'carboy_use_cycle'      AS type, key_id, pickup_id, close_date, drum_id, status AS carboy_status, pour_date AS carboy_pour_date FROM carboy_use_cycle 
					) c
					LEFT OUTER JOIN drum drum ON drum.key_id = c.drum_id
		
				) container ON (
					(container.type = 'scint_vial_collection' AND container.key_id = amt.scint_vial_collection_id)
					OR (container.type = 'waste_bag' AND container.key_id = amt.waste_bag_id)
					OR (container.type = 'other_waste_container' AND container.key_id = amt.other_waste_container_id)
					OR (container.type = 'carboy_use_cycle' AND container.key_id = amt.carboy_id)
				)

			) package
		
			GROUP BY package.isotope_id, package.parcel_id
		
		) inventory
		
		GROUP BY isotope_id
		ORDER BY isotope_name";

		$this->LOG->debug("Executing: $queryString");

        $stmt = DBConnection::prepareStatement($queryString);

        if( !$stmt->execute() ){
			$this->LOG->error("ERROR executing inventory report");
			$this->LOG->error($stmt->errorInfo());
		}
		$inventories = $stmt->fetchAll(PDO::FETCH_CLASS, "RadReportDTO");

		// 'close' the statement
		$stmt = null;

		$this->LOG->info(count($inventories) . ' Resulting Isotope Inventories');
		if($this->LOG->isTraceEnabled()){
			$this->LOG->trace($inventories);
		}

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

        $stmt = DBConnection::prepareStatement($queryString);
        $stmt->bindParam(':class', get_class($equipment), PDO::PARAM_STR);
        $stmt->bindParam(':id', $equipment->getKey_id(), PDO::PARAM_INT);

        $stmt->execute();
        $currentInspections = $stmt->fetchAll(PDO::FETCH_CLASS, "EquipmentInspection");

		// 'close' the statement
		$stmt = null;

        return $currentInspections;
    }

    public function getEquipmentByPi($id, $equipmentClass){
        $queryString = 'select * from equipment_inspection a
                        left join principal_investigator_equipment_inspection b
                        on a.key_id = b.inspection_id
                        AND a.equipment_class = :class
                        where b.principal_investigator_id = :id;';

        $stmt = DBConnection::prepareStatement($queryString);
        $stmt->bindParam(':class',$equipmentClass, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();
        $currentInspections = $stmt->fetchAll(PDO::FETCH_CLASS, "EquipmentInspection");

		// 'close' the statement
		$stmt = null;

        return $currentInspections;
    }

    public function getEquipmentRelations($equipmentTypeId, $piId, $roomId){
        $queryString = 'select * from principal_investigator_hazard_room a
                        left join principal_investigator_equipment_inspection b
                        on a.key_id = b.inspection_id
                        AND a.equipment_class = :class
                        where b.principal_investigator_id = :id;';

        $stmt = DBConnection::prepareStatement($queryString);
        $stmt->bindParam(':class',$equipmentClass, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();
        $currentInspections = $stmt->fetchAll(PDO::FETCH_CLASS, "EquipmentInspection");

		// 'close' the statement
		$stmt = null;

        return $currentInspections;
    }

    public function deleteRadData(){
		$this->LOG->warn("Preparing to delete radiation module data");

		// TODO: Miscellaneous waste?
        $sql = 'DELETE FROM parcel_use_amount WHERE key_id > 0;
                DELETE FROM parcel_use WHERE key_id > 0;
                DELETE FROM waste_bag WHERE key_id > 0;
                DELETE FROM other_waste WHERE key_id > 0;
				DELETE FROM carboy WHERE key_id > 0;
                DELETE FROM carboy_reading_amount WHERE key_id > 0;
                DELETE FROM carboy_use_cycle WHERE key_id > 0;
				DELETE FROM drum WHERE key_id > 0;
				DELETE FROM drum_wipe WHERE key_id > 0;
				DELETE FROM drum_wipe_test WHERE key_id > 0;
                DELETE FROM scint_vial_collection WHERE key_id > 0;
                DELETE FROM solids_container WHERE key_id > 0;
                DELETE FROM parcel_wipe WHERE key_id > 0;
                DELETE FROM parcel_wipe_test WHERE key_id > 0;
                DELETE FROM other_waste_container WHERE key_id > 0;
				DELETE FROM other_waste_type WHERE key_id > 0;
				DELETE FROM other_waste_type_pi WHERE key_id > 0;
				DELETE FROM parcel WHERE key_id > 0;
				DELETE FROM pickup WHERE key_id > 0;
				DELETE FROM pickup_lot WHERE key_id > 0;
                DELETE FROM disposal_lot WHERE key_id > 0;
                DELETE FROM pi_quarterly_inventory WHERE key_id > 0;
                DELETE FROM quarterly_inventory WHERE key_id > 0;
                DELETE FROM quarterly_isotope_amount WHERE key_id > 0;
                DELETE FROM authorization WHERE key_id > 0;
                DELETE FROM pi_authorization WHERE key_id > 0;
                DELETE FROM pi_authorization_department WHERE key_id > 0;
                DELETE FROM pi_authorization_rad_condition WHERE key_id > 0;
                DELETE FROM pi_authorization_room WHERE key_id > 0;
                DELETE FROM pi_authorization_user WHERE key_id > 0;
                DELETE FROM purchase_order WHERE key_id > 0;
                DELETE FROM pi_wipe WHERE key_id > 0;
                DELETE FROM pi_wipe_test WHERE key_id > 0;
				DELETE FROM rad_condition WHERE key_id > 0;
				';

		$this->LOG->debug("Executing: $sql");
        $stmt = DBConnection::prepareStatement($sql);
        if($stmt->execute()){
			// 'close' the statement
			$stmt = null;
			$this->LOG->warn("Radiation data has been deleted");
            return true;
		}

		// 'close' the statement
		$stmt = null;

		$this->LOG->error("Failed to delete Radiation data");
        return false;
    }

	function validateKeyId($id){
		return !empty($id) && $id > 0;
	}
}
?>

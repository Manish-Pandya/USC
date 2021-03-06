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

	public static $_ENTITY_CACHE;

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

		if( !isset(self::$_ENTITY_CACHE) ){
			self::$_ENTITY_CACHE = CacheFactory::create('Entity');
		}

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

		$cache_key = AppCache::key_class_id($this->modelClassName, $id);
		$cached = self::$_ENTITY_CACHE->getCachedEntity($cache_key);
		if( isset($cached) ){
			$this->LOG->debug("Returning cached $this->modelClassName entity with keyid '$id'");
			return $cached;
		}

		$this->LOG->debug("Looking up $this->modelClassName entity with keyid '$id'");

		try{
			$q = QueryUtil::selectFrom($this->modelObject)
				->where('key_id', '=', $id, PDO::PARAM_INT);
			$result = $q->getOne();

			// $result being false indicates no rows returned.
			if(!$result) {
				// 'close' the statment
				$stmt = null;

				$this->LOG->warn("No Rows returned for fetch by ID $id");
				//return;
				return new ActionError('No rows returned');
			}

			if( $this->LOG->isTraceEnabled() ){
				$cnt = is_array($result) ? count($result) : $result != null ? 1 : 0;
				$this->LOG->trace("Result count: $cnt");
			}

			self::$_ENTITY_CACHE->cacheEntity($result, $cache_key);
			return $result;
		}
		catch(QueryException $er){
			return new QueryError($er->getMessage());
		}
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

		try{
			$q = QueryUtil::selectFrom($this->modelObject);

			if( $sortColumn != NULL ){
				$entity_table = $this->modelObject->getTableName();
				$q->orderBy($entity_table, $sortColumn, ($sortDescending ? 'DESC' : 'ASC'));
			}

			if( $activeOnly ){
				$q->where('is_active', '=', '1');
			}

			$result = $q->getAll();

			$this->LOG->trace("Result: " . count($result));
			return $result;
		}
		catch(QueryException $er){
			return new QueryError($er->getMessage());
		}
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
	function queryPage( $paging, $sql, $fetch_fn, $bind_fn = null ){
		if( $paging != null ){
			// First, count the records
			$count_stmt = DBConnection::prepareStatement($sql);

			if( isset($bind_fn) ){
				call_user_func($bind_fn, $count_stmt);
			}

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

		if( isset($bind_fn) ){
			call_user_func($bind_fn, $stmt);
		}

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
			if($clause->getVal() != NULL) {
				if( is_array($clause->getVal()) ){
                    foreach($clause->getVal() as $val){
                        $stmt->bindValue( $i, $val );
                        $i++;
                    }
				}
                else if( strtolower($clause->getVal()) != "null" ){
				    $stmt->bindValue( $i, $clause->getVal() );
				    $i++;
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

		// Update created/last-modified audit fields
		$this->updateAuditFields($object);

		// Get DB Connection
		$db = DBConnection::get();

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

			// Evict this item from the cache
			self::$_ENTITY_CACHE->evict($object);

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

	private function updateAuditFields( GenericCrud &$object ){
		$this->LOG->debug("Update audit fields of: $object");

		$now = date("Y-m-d H:i:s");

		$curUserId = null;
		if(isset($_SESSION["USER"]) && $_SESSION["USER"]->getKey_id() != null){
			$curUserId = $_SESSION["USER"]->getKey_id();
		}

		if( $object->hasPrimaryKeyValue() ){
			$this->LOG->trace("Look up existing value for audit fields");

			// Look up existing entity
			$auditInfo = $this->getAuditFields( $object->getKey_id() );

			// Ensure that our value-to-be-saved has the same *Created audit fields
			$object->setDate_created( $auditInfo->date_created );
			$object->setCreated_user_id( $auditInfo->created_user_id );
		}
		else {
			$this->LOG->trace("Initialize created audit fields");

			// This is a new entity; initialize *Created audit fields
			$object->setDate_created( $now );
			$object->setCreated_user_id( $curUserId );
		}

		// Update last-modified audit fields
		$this->LOG->trace("Update modified audit fields");
		$object->setDate_last_modified($now);
		$object->setLast_modified_user_id($curUserId);

		return $object;
	}

	public function getAuditFields( $id ){
		$table = $this->modelObject->getTableName();
		$sql = "SELECT date_created, date_last_modified, last_modified_user_id, created_user_id FROM $table o WHERE o.key_id = ?";
		$stmt = DBConnection::prepareStatement($sql);
		$stmt->bindValue(1, $id);
		$stmt->execute();

		return $stmt->fetchObject();
	}

	/**
	 * Retrieves related-items by first retrieveing related Keys,
	 * then looking up each individually by key. While this is more
	 * work for larger lists, it forces use of the entity cache
	 * and prevents duplicate queries for items.
	 *
	 * Use the other getRelatedItem* methods to prevent this additional
	 * step without caching
	 */
	public function getRelatedItems($id, DataRelationship $relationship){
		$items = array();
		$relationKeys = $this->getRelatedItemKeysById($id, $relationship);

		if( !empty($relationKeys) ){
			$relDao = new GenericDAO(new $relationship->className);
			foreach($relationKeys as $id){
				$items[] = $relDao->getById($id);
			}
		}

		return $items;
	}

	public function getRelatedItemKeysById($id, DataRelationship $relationship){
		if (empty($id)) { return array();}

		// get the relationship parameters needed to build the query
		$tableName		= $relationship->getTableName();
		$keyName		= $relationship->getKeyName();
		$foreignKeyName	= $relationship->getForeignKeyName();

		$sql = "SELECT $keyName FROM $tableName WHERE $foreignKeyName=:id";
		$stmt = DBConnection::prepareStatement($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		if ($stmt->execute() ) {
			$result = $stmt->fetchAll(PDO::FETCH_COLUMN, $keyName);
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

	protected function &_buildQueryFor_getRelatedItemsById($id, DataRelationship $relationship, $sortColumns = null, $activeOnly = false, $activeOnlyRelated = false, $limit=0){
		$joinOnIdField = new Field($relationship->foreignKeyName, $relationship->tableName);
		$q = QueryUtil::selectFrom($this->modelObject, $relationship)
			->where($joinOnIdField, '=', $id, PDO::PARAM_INT);

		if( $activeOnly ){
			$q->where('is_active', '=', 1);
		}

		// TODO: Now that this Query uses Joins instead of a subquery, do we really need to respect 'activeOnlyRelated'?
		/*if( $activeOnlyRelated){
			Logger::getLogger(__CLASS__ . '.' . __FUNCTION__)->warn("Is $relationship->tableName.is_active a real column?");
			//$q->where(Field::create('is_active', $relationship->tableName), '=', 1);
		}*/

		if( $sortColumns != null ){
			foreach($sortColumns as $key=>$column){
				if( $column instanceof Field ){
					$q->orderBy($column->table, $column->name);
				}
				else {
					$q->orderBy($relationship->tableName, $column);
				}

			}
		}

		if( $limit > 0 ){
			$q->limit($limit);
		}

		return $q;
	}

	public function getRelatedItemsById($id, DataRelationship $relationship, $sortColumns = null, $activeOnly = false, $activeOnlyRelated = false, $limit=0){
		try{
			$q = $this->_buildQueryFor_getRelatedItemsById($id, $relationship, $sortColumns, $activeOnly, $activeOnlyRelated, $limit);
			$result = $q->getAll();

			if( $this->LOG->isTraceEnabled() ){
				$cnt = is_array($result) ? count($result) : $result != null ? 1 : 0;
				$this->LOG->trace("Result count: $cnt");
			}

			return $result;
		}
		catch(QueryException $er){
			return new QueryError($er->getMessage());
		}
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
        LEFT OUTER JOIN principal_investigator pi
            ON pi.key_id = pi_dept.principal_investigator_id
		LEFT OUTER JOIN principal_investigator_room pi_room
			ON (pi_room.principal_investigator_id = pi_dept.principal_investigator_id)
		LEFT OUTER JOIN room room
			ON (room.key_id = pi_room.room_id)
		LEFT OUTER JOIN building building
			ON (building.key_id = room.building_id)
		LEFT OUTER JOIN campus campus
			ON (campus.key_id = building.campus_id)

		WHERE pi_room.room_id IS NOT NULL
			AND dept.key_id = :deptId
            AND pi.is_active = 1
            AND room.is_active = 1

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

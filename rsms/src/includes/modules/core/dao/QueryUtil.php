<?php
class QueryException extends Exception{
    public function __construct($msg){ parent::__construct($msg); }
}
class QueryUtil {
    public static function selectFrom( GenericCrud $modelObject ){
        return new QueryUtil($modelObject);
    }

    protected $entity_class;
    protected $entity_table;
    protected $columns;
    protected $joins;
    protected $predicates;
    protected $orders;

    protected $tableAliases;
    protected $fieldAliases;
    protected $joinRels;

    private $valbinderId = 1;

    public function __construct(GenericCrud $modelObject){
        $this->entity_class = get_class($modelObject);
        $this->entity_table = $modelObject->getTableName();
        $this->columns = array();
        $this->joins = array();
        $this->joinRels = array();
        $this->predicates = array();
        $this->orders = array();
        $this->tableAliases = array();
        $this->fieldAliases = array();

        $this->withTableAlias($this->entity_table, $this->entity_table);
        $this->map_fields($this->entity_table, $modelObject->getColumnData());

        if( $modelObject instanceof ISelectWithJoins){
            $joinRels = $modelObject->selectJoinReleationships();
            if( !is_array($joinRels) ){
                $joinRels = array($joinRels);
            }

            foreach($joinRels as $joinRel){
                $this->joinTo($joinRel);
            }
        }
    }

    public function joinTo(DataRelationship $joinRel){
        $this->joinRels[] = $joinRel;
        return $this;
    }

    public function withTableAlias($table, $alias){
        $this->tableAliases[$table] = $alias;
    }

    public function withFieldAlias($table, $field, $alias){
        $table_alias = $this->tableAliases[$table] ?? $table;
        $this->fieldAliases["$table_alias.$field"] = $alias;
    }

    public function _join(DataRelationship $joinRel){
        $join_table = $joinRel->tableName;
        $src_table = $joinRel->sourceTableName ?? $this->entity_table;
        $join_key = $joinRel->keyName;
        $join_foreign = $joinRel->foreignKeyName;

        $this->withTableAlias($join_table, $join_table);

        foreach($joinRel->columnAliases as $field=>$alias){
            $this->withFieldAlias($join_table, $field, $alias);
        }

        $this->map_fields($join_table, $joinRel->columns, $joinRel->columnAliases);

        $this->joins[] = "JOIN $join_table $join_table ON $join_table.$join_foreign = $src_table.$join_key";

        return $this;
    }

    public function where_raw($field, $operator, $val = null, $valPdoType = null ){
        // TODO: validate operator
        $pred = "$field $operator";

        if( isset($val) ){
            $val_id = $this->bind_value($val, $valPdoType);
            $pred .= " $val_id";
        }

        $this->predicates[] = $pred;
        return $this;
    }

    public function where( $name, $operator, $val = null, $valPdoType = null ){
        $field = "$this->entity_table.$name";
        return $this->where_raw($field, $operator, $val, $valPdoType);
    }

    public function orderBy($table, $column, $direction = "ASC"){
        $alias = $this->tableAliases[$table] ?? $table;
        $this->orders[] = "CAST($alias.$column AS UNSIGNED), $alias.$column $direction";
    }

    public function sql(){
        $all_predicates = array();

        $entity_table = $this->entity_table;

        // Process Joins so we can reference fields
        if( count($this->joinRels) > 0 ){
            foreach($this->joinRels as $join){
                $this->_join($join);
            }
        }

        // Process field list
		$all_fields = implode(',', $this->columns);

        $parts = array();
        $parts[] = "SELECT $all_fields FROM $entity_table $entity_table";

        if( count($this->joinRels) > 0 ){
            $all_joins = implode(' ', $this->joins);
            $parts[] = $all_joins;
        }

        if( count($this->predicates) > 0 ){
            // FIXME: GROUPING, OR
            $all_predicates = implode(' AND ', $this->predicates);
            $parts[] = " WHERE $all_predicates";
        }

        if( count($this->orders) > 0 ){
            $parts[] = "ORDER BY";
            $all_orders = implode(',', $this->orders);
            $parts[] = $all_orders;
        }

        $sql = implode(' ', $parts);
        Logger::getLogger(__CLASS__)->debug($sql);

		return $sql;
    }

    public function &prepare(){
        $sql = $this->sql();
        $stmt = DBConnection::prepareStatement($sql);
        if( !empty($this->bindings) ){
            foreach($this->bindings as $param => $val){
                $stmt->bindParam($param, $val[0], $val[1]);
            }
        }

        return $stmt;
    }

    public function getAll($fetchClass = null){
        if( !isset($fetchClass) ){
            $fetchClass = $this->entity_class;
        }

        $stmt = $this->prepare();
        if( !$stmt->execute() ){
			$error = $stmt->errorInfo();
            $result = new QueryException($error[2]);
            $stmt = null;
            throw $result;
        }

        $result = $stmt->fetchAll(PDO::FETCH_CLASS, $fetchClass);
        $stmt = null;
        return $result;
    }

    public function getOne($fetchClass = null){
        if( !isset($fetchClass) ){
            $fetchClass = $this->entity_class;
        }

        $stmt = $this->prepare();
        if( !$stmt->execute() ){
			$error = $stmt->errorInfo();
			$result = new QueryException($error[2]);
            throw $result;
        }

        $result = $stmt->fetchObject($fetchClass);
        $stmt = null;
        return $result;
    }

    private function map_fields($table, $cols, $colAliases = array()){
        $alias = $this->tableAliases[$table] ?? $table;

		foreach( $cols as $field => $type){
            $field_alias = $this->fieldAliases["$alias.$field"] ?? $field;
            $this->columns[] = "$alias.$field as $field_alias";
        }

        return $this;
    }

    private function bind_value(&$value, &$valPdoType){
        $val_id = ':val' . $this->valbinderId++;
        $this->bindings[$val_id] = array($value, $valPdoType);
        return $val_id;
    }
}
?>
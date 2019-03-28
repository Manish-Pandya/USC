<?php

class QueryUtil {

    const JOIN = 'JOIN';
    const JOIN_OUTER_LEFT = 'LEFT OUTER JOIN';

    public static function selectFrom( GenericCrud $modelObject, DataRelationship $relation = null){
        return new QueryUtil($modelObject, $relation);
    }

    protected $limit;

    protected $entity_class;
    protected $entity_table;

    protected $rel_parent_table;
    protected $rel_parent_class;

    protected $columns;
    protected $joins;
    protected $predicates;
    protected $groupBys;
    protected $orders;

    protected $tableAliases;
    protected $fieldAliases;
    protected $joinRels;

    private $valbinderId = 1;

    private $sql;

    public function __construct(GenericCrud $modelObject, DataRelationship $relation = null){

        // init query parts
        $this->columns = array();
        $this->joins = array();
        $this->joinRels = array();
        $this->predicates = array();
        $this->orders = array();
        $this->tableAliases = array();
        $this->fieldAliases = array();
        $this->groupBys = array();

        // Set up entity details

        if( !isset($relation) ){
            $this->entity_class = get_class($modelObject);
            $this->entity_table = $modelObject->getTableName();
            $colData = $modelObject->getColumnData();
        }
        else {
            // Redirect to get items related to the entity class
            $this->rel_parent_table = $modelObject->getTableName();
            $this->rel_parent_class = get_class($modelObject);
            $this->withTableAlias($this->rel_parent_table, $this->rel_parent_table);

            // REASSIGN modelObject to instance of relation class
            $this->entity_class = $relation->className;
            $modelObject = new $this->entity_class;

            $this->entity_table = $modelObject->getTableName();
            $colData = $modelObject->getColumnData();
        }

        $this->withTableAlias($this->entity_table, $this->entity_table);
        $this->map_fields($this->entity_table, $colData);

        // Automatically apply declared Join(s)
        if( isset($relation) ){
            // Copy relation and override keys in order to form the join using legacy DataRelationship data...
            $relatedRel = clone $relation;
            $relatedRel->foreignKeyName = $relation->keyName;
            $relatedRel->keyName = 'key_id';

            // Validate join - prevent joining to entity table
            if( $this->rel_parent_table != $this->entity_table ){
                $this->joinTo($relatedRel);
            }
        }

    }

    public function joinTo(DataRelationship $joinRel, $joinType = self::JOIN){
        $j = clone $joinRel;
        $j->joinType = $joinType;
        $this->joinRels[] = $j;
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

        $join_field = "$join_table.$join_foreign";
        $src_field = "$src_table.$join_key";

        // Validate join to prevent self-joining by key_id
        if( $this->entity_table == $join_table && $join_field == $src_field){
            Logger::getLogger(__CLASS__ . '.' . __FUNCTION__)->debug("Skip joining $src_table to $join_table on $join_field = $src_field");
            return;
        }

        $this->withTableAlias($join_table, $join_table);

        foreach($joinRel->columnAliases as $field=>$alias){
            $this->withFieldAlias($join_table, $field, $alias);
        }

        $this->map_fields($join_table, $joinRel->columns, $joinRel->columnAliases);

        if( isset($joinRel->orderColumn) ){
            $this->map_fields($join_table, array($joinRel->orderColumn => $joinRel->orderColumn));
        }

        $join_type = $joinRel->joinType ?? self::JOIN;
        $this->joins[] = "$join_type $join_table $join_table ON $join_field = $src_field";

        return $this;
    }

    public function where_raw($field, $operator = null, $val = null, $valPdoType = null ){
        // TODO: validate operator
        $pred = implode(' ', array($field, $operator));

        if( isset($val) ){
            if( $operator == 'IN' && is_array($val) ){
                // Bind an array of values
                $bound_valIds = array();
                foreach($val as $v){
                    $val_id = $this->bind_value($v, $valPdoType);
                    $bound_valIds[] = $val_id;
                }

                $pred .= '(' . implode(', ', $bound_valIds) . ')';

            }
            else {
                // Bind a single value
                $val_id = $this->bind_value($val, $valPdoType);
                $pred .= " $val_id";
            }

        }

        $this->predicates[] = $pred;
        return $this;
    }

    public function where( $nameOrField, $operator = null, $val = null, $valPdoType = null ){
        $field = $nameOrField;
        if( !($field instanceof IField) ){
            // TODO: infer table details
            // Find mapped columns which match the name?
            $field = new Field($nameOrField, $this->entity_table);
        }

        $wherePart = $field->write();
        return $this->where_raw($wherePart, $operator, $val, $valPdoType);
    }

    public function groupBy(Field $field){
        $this->groupBys[] = $field->write();
        return $this;
    }

    public function orderBy($table, $column, $direction = "ASC"){
        $alias = $this->tableAliases[$table] ?? $table;
        $this->orders[] = "CAST($alias.$column AS UNSIGNED), $alias.$column $direction";

        return $this;
    }

    public function limit(int $limit){
        $this->limit = $limit;
    }

    public function sql(){
        if( isset($this->sql) ){
            return $this->sql;
        }

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

        if( count($this->groupBys) > 0 ){
            $all_groups = implode(', ', $this->groupBys);
            $parts[] = "GROUP BY $all_groups";
        }

        if( count($this->orders) > 0 ){
            $parts[] = "ORDER BY";
            $all_orders = implode(',', $this->orders);
            $parts[] = $all_orders;
        }

        if( isset($this->limit) ){
            $parts[] = "LIMIT $this->limit";
        }

        $sql = implode(' ', $parts);
        Logger::getLogger(__CLASS__)->debug($sql);

        $this->sql = $sql;
		return $this->sql;
    }

    public function &prepare(){
        $sql = $this->sql();
        $stmt = DBConnection::prepareStatement($sql);
        if( !empty($this->bindings) ){
            foreach($this->bindings as $param => $val){
                // TODO: Infer type
                $value = $val[0];
                $t = $val[1];
                if( !isset($t) ){
                    if( is_numeric($value) ){
                        $t = PDO::PARAM_INT;
                    }
                    else {
                        $t = PDO::PARAM_STR;
                    }
                }
                Logger::getLogger(__CLASS__ . '.' . __FUNCTION__)->debug("Binding $param='$value' as PDO type $t");
                $stmt->bindValue($param, $value, $t);
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
            $er = $this->buildQueryException($stmt);
            $stmt = null;
            throw $er;
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
            $er = $this->buildQueryException($stmt);
            $stmt = null;
            throw $er;
        }

        $result = $stmt->fetchObject($fetchClass);
        $stmt = null;
        return $result;
    }

    protected function buildQueryException(&$stmt){
        $error = $stmt->errorInfo();
        $ex = new QueryException($error[2]);
        Logger::getLogger(__CLASS__ . '.' . __FUNCTION__)->error($ex->getMessage() . "\n" . $this->sql());
        return $ex;
    }

    private function map_fields($table, $cols, $colAliases = array()){
        $alias = $this->tableAliases[$table] ?? $table;

		foreach( $cols as $field => $type){
            $field_alias = $this->fieldAliases["$alias.$field"] ?? $field;
            $this->columns[] = "$alias.$field as `$field_alias`";
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

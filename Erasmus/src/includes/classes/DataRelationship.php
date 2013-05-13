<?php

/**
 *
 * Utility class for representing relationships
 *
 * @author Mitch Martin, GraySail LLC
 */
class DataRelationship {

	public final $className;
	public final $tableName;
	public final $keyName;
	public final $foreignKeyName;
	
	public function __construct($className, $tableName, $keyName, $foreignKeyName){
		$this->className = $className;
		$this->tableName = $tableName;
		$this->keyName = $keyName;
		$this->foreignKeyName = $foreignKeyName;
	}
	
	public function getClassName(){
		return $this->className;
	}
	
	public function getTableName(){
		return $this->tableName;
	}
	
	public function getKeyName(){
		return $this->keyName;
	}
	
	public function getForeignKeyName(){
		$this->foreignKeyName;
	}
	
}

?>
<?php

/**
 *
 * Utility class for representing relationships
 *
 * @author Mitch Martin, GraySail LLC
 */
class DataRelationship {

	public $className;
	public $tableName;
	public $keyName;
	public $foreignKeyName;
	
	public function __construct(){
	}
	
	public static function fromValues( $className, $tableName, $keyName, $foreignKeyName ) {
		$instance = new self();
		$instance->className = $className;
		$instance->tableName = $tableName;
		$instance->keyName = $keyName;
		$instance->foreignKeyName = $foreignKeyName;
		return $instance;
	}
	
	public static function fromArray( array $relationship ) {
		$instance = new self();
		$instance->className = $relationship["className"];
		$instance->tableName = $relationship["tableName"];
		$instance->keyName = $relationship["keyName"];
		$instance->foreignKeyName = $relationship["foreignKeyName"];
		return $instance;
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
		return $this->foreignKeyName;
	}
	
}

?>
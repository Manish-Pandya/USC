<?php
/**
 *  Class to encapsulate relationship information
 *  
 *  @author Perry
 */
class RelationMapping {
	private $classesToCheck;
	private $tableName;
	private $parentColumn;
	private $childColumn;
	
	public function __construct($classA, $classB, $tableName,  $parentColumn, $childColumn) {
		$this->tableName = $tableName;
		$this->className = $className;
		$this->parentColumn = $parentColumn;
		$this->childColumn = $childColumn;
		$this->classesToCheck = array($classA, $classB);
		
	}
	
	// checks if this relationmapping contains the table name relating to the given class names
	public function isPresent($classA, $classB) {
		if( in_array($classA, $this->classesToCheck) && in_array($classB, $this->classesToCheck) ) {
			return true;
		}
		else {
			return false;
		}
	}
	
	public function getTableName() { return $this->tableName; }
	public function getClassName() { return $this->className; }
	
	public function getParentColumn () {return $this->parentColumn;}
	public function getChildColumn  () {return $this->childColumn;}
	
}
<?php
/**
 *  Class to encapsulate relationship information
 *  
 *  @author Perry
 */
class RelationMapping {
	private $classesToCheck;
	private $tableName;
	
	public function __construct($classA, $classB, $tableName) {
		$this->tableName = $tableName;
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
}
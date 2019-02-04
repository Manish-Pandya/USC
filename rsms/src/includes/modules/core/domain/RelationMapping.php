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
	private $isReversed;
    private $override;

	public function __construct($classA, $classB, $tableName,  $parentColumn, $childColumn, $override = null) {

        if($override != null){
            $this->override = $override;
        }

		$this->tableName = $tableName;
		$this->parentColumn = $parentColumn;
		$this->childColumn = $childColumn;
		$this->classesToCheck = array($classA, $classB);
	}

	// checks if this relationmapping contains the table name relating to the given class names
	public function isPresent($classA, $classB, $override = null) {

		if(($this->override != NULL && $override != null && $this->override == $override) || ($this->override == NULL && in_array($classA, $this->classesToCheck) && in_array($classB, $this->classesToCheck)) ) {
			//if the first class passed is the second index of our array, we have asked for the $classB->get$classA()
		
			if($classA == $this->classesToCheck[1]){
				$this->isReversed = true;
			}
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

	public function getIsReversed () {return $this->isReversed;}
	public function setIsReversed ($isReversed) {$this->isReversed = $isReversed;}


}
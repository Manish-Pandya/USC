<?php

/**
 *
 * Utility class for representing related entities and their loading
 *
 * @author Hoke Currie, GraySail LLC
 */
class EntityMap {
	
	private $loadingType;
	private $entityAccessor;	
	
	public function __construct($loadingType, $entityAccessor){
		$this->loadingType = $loadingType;
		$this->entityAccessor = $entityAccessor;
	
	}	

	public function getLoadingType() {
		return $this->loadingType;
	}

	public function setLoadingType($loadingType){
		$this->loadingType = $loadingType;
	}

	public function getEntityAccessor() {
		return $this->entityAccessor;
	}

	public function setEntityAccessor($entityAccessor){
		$this->entityAccessor = $entityAccessor;
	}
	
}


?>

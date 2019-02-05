<?php

/**
 *
 * Utility class for representing related entities and their loading
 *
 * @author Hoke Currie, GraySail LLC
 */
class EntityMap {
	public static $TYPE_EAGER = 'eager';
	public static $TYPE_LAZY = 'lazy';

	public static function eager( $entityAccessor ){
		return new EntityMap( self::$TYPE_EAGER, $entityAccessor );
	}

	public static function lazy( $entityAccessor ){
		return new EntityMap( self::$TYPE_LAZY, $entityAccessor );
	}

	private $loadingType;
	private $entityAccessor;	
	
	public function __construct($loadingType, $entityAccessor){
		$this->loadingType = $loadingType;
		$this->entityAccessor = $entityAccessor;
	
	}

	public function __toString(){
		return "$this->loadingType:$this->entityAccessor";
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

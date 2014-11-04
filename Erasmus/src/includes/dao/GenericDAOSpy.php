<?php
/**
 * Test double of GenericDAO to simplify mocking while unit testing action functions
 *
 * 
 * @author GraySail LLC
 * @author Perry
 */

/* Not extending GenericDAO because if I unintentionally call a method of the real
GenericDao that has not been mocked, I'd rather get an error so I'll know. */
class GenericDaoSpy {

	// object and class of object this GenericDao should return
	private $modelObject;
	
	// number of "modelObjects" to return in getAll()
	public $itemCount;
	
	// associative array, keeps track of how many times methods were called.
	private $callCount;
	
	// keeps track of methods that need to return specific object
	private $methodsToOverride;

	
	/* Helpful functions to give extra info during tests */
	
	// get number of times a method has been called
	public function getCallCount($methodName) {
		return $this->callCount[$methodName];
	}
	
	// determine whether a method has been called
	public function wasItCalled($methodName) {
		return $this->getCallCount($methodName) > 0;
	}
	
	public function getModelObject() {
		return $this->modelObject;
	}
	
	public function overrideMethod($methodName, $thingToReturn) {
		$this->methodsToOverride[$methodName] = $thingToReturn;
	}
	

	/* fake methods to be used by action functions, */

	public function __construct( GenericCrud $model_object, $itemCount = 5 ) {
		$this->setModelObject($model_object);
		$this->itemCount = $itemCount;
		
		foreach( get_class_methods('GenericDaoSpy') as $methodName ) {
			$this->callCount[$methodName] = 0;
		}
	}


	public function setModelObject($model) {
		$this->modelObject = $model;
	}

	public function getById($id) {
		// this method can return a specific object if necessary - check.
		if( array_key_exists('getById', $this->methodsToOverride) ) {
			return $this->methodsToOverride['getById'];
		}

		// indicate method was called
		$this->callCount['getById'] ++;

		//$testObject = new $this->modelObjectClass;
		$testObject = $this->modelObject;
		$testObject->setKey_id($id);

		return $testObject;
	}
	
	public function getAll() {
		// this method can return a specific object if necessary - check.
		if( array_key_exists('getAll', $this->methodsToOverride) ) {
			return $this->methodsToOverride['getAll'];
		}

		// indicate method was called
		$this->callCount['getAll'] ++;

		$testArray = array_fill( 0, $this->itemCount, $this->getById(1) );
		return $testArray;
	}
	
	public function save($objToSave) {
		$this->callCount['save'] ++;
		
		// ActionManager expects object back with key id
		if( $objToSave->getKey_id() === null ) {
			$objToSave->setKey_id(1);
		}
		
		return $objToSave;
	}
}
?>
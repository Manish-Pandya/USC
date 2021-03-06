<?php
/**
 * Test double of GenericDAO to simplify mocking while unit testing action functions
 *
 * 
 * @author GraySail LLC
 * @author Perry
 */
require_once(dirname(__FILE__) . '/../../../test/includes/action_functions/MethodCall.php');


/* Not extending GenericDAO because if I unintentionally call a method of the real
GenericDao that has not been mocked, I'd rather get an error so I'll know. */
class GenericDaoSpy {

	// object and class of object this GenericDao should return
	private $modelObject;
	
	// number of "modelObjects" to return in getAll()
	public $itemCount;
	
	// keeps track of methods that need to return specific object
	private $methodsToOverride;
	
	// list of method calls including method name, argument, and time called
	private $calls;
	
	
	/* Helpful functions to give extra info during tests */
	
	// get number of times a method has been called
	public function getCallCount($methodName) {
		$callCount = 0;
		$calls = $this->getCalls();
		
		foreach($calls as $call) {
			if($call->getMethod() == $methodName) {
				$callCount ++;
			}
		}
		return $callCount;
	}
	
	// determine whether a method has been called
	public function wasItCalled($methodName) {
		return $this->getCallCount($methodName) > 0;
	}
	
	public function getModelObject() {
		return $this->modelObject;
	}
	
	public function overrideMethod($modelToOverride, $methodName, $thingToReturn) {
		$this->methodsToOverride[$methodName]["modelClass"] = $modelToOverride;
		$this->methodsToOverride[$methodName]["result"] = $thingToReturn;
	}
	
	private function isOverriden($methodName) {
		// check if method is overriden
		 if( array_key_exists($methodName, $this->methodsToOverride) ) {
		 	// check if it's overridden for any model class, or just one particular model object.
		 	if( $this->methodsToOverride[$methodName]["modelClass"] === "Any" ) {
		 		return true;
		 	}
		 	else {
		 		return $this->methodsToOverride[$methodName]["modelClass"] === $this->getModelObject();
		 	}
		 }
	 	return false;
	}
	
	private function getOverridenResult($methodName) {
		return $this->methodsToOverride[$methodName]["result"];
	}
	
	public function getCalls() { return $this->calls; }
	
	// returns last call record for method of that name
	public function getLastCall($methodName) {
		
		$calls = $this->getCalls();
		$length = count($calls);

		// if $methodName left blank, defaults to returning last call overall
		if($methodName === null) {
			return $calls[$length - 1];
		}

		// otherwise, search for last entry in calls with given methodName
		$selected = null;

		for($i = 0; $i < $length; $i++ ) {
			$currentCall = $calls[$i];
			if( $currentCall->getMethod() === $methodName ) {
				$selected = $currentCall;
			}
		}
		
		return $selected;
	}
	
	public function addCall($method, $args) {
		$this->calls[] = new MethodCall($method, $args);
	}
	

	public function __construct( GenericCrud $model_object, $itemCount = 5 ) {
		$this->setModelObject($model_object);
		$this->itemCount = $itemCount;
		$this->calls = array();
	}

	/* fake methods to be used by action functions, */


	public function setModelObject($model) {
		$this->modelObject = $model;
	}

	public function getById($id) {
		$args = array($id);
		$this->addCall('getById', $args);
		
		// this method can return a specific object if necessary - check.
		if( $this->isOverriden('getById') ) {
			return $this->getOverridenResult('getById');
		}

		//$testObject = new $this->modelObjectClass;
		$testObject = $this->modelObject;
		$testObject->setKey_id($id);

		return $testObject;
	}
	
	public function getAll() {
		$this->addCall('getAll', array());
		
		// this method can return a specific object if necessary - check.
		if( $this->isOverriden('getAll') ) {
			return $this->getOverridenResult('getAll');
		}

		$testArray = array_fill( 0, $this->itemCount, $this->getById(1) );
		return $testArray;
	}
	
	public function save($objToSave) {
		$args = array($objToSave);
		$this->addCall('save', $args);
		
		// this method can return a specific object if necessary - check.
		if( $this->isOverriden('save') ) {
			return $this->getOverridenResult('save');
		}

		// ActionManager expects object back with key id
		if( $objToSave->getKey_id() === null ) {
			$objToSave->setKey_id(1);
		}
		
		return $objToSave;
	}
	
	public function deleteById($keyId) {
		$args = array($keyId);
		$this->addCall('deleteById', $args);

		// this method can return a specific object if necessary - check.
		if( $this->isOverriden('deleteById') ) {
			return $this->getOverridenResult('deleteById');
		}
		else {
        	return true;
		}
	}
	
	public function removeRelatedItems($key_id, $foreignKey_id, $relationship) {
		$args = array($key_id, $foreignKey_id, $relationship);
		$this->addCall('removeRelatedItems', $args);
		
		// this method can return a specific object if necessary - check.
		if( $this->isOverriden('removeRelatedItems') ) {
			return $this->getOverridenResult('removeRelatedItems');
		}
		else {
			return true;
		}
		
	}

	public function addRelatedItems($key_id, $foreignKey_id, $relationship) {
		$args = array($key_id, $foreignKey_id, $relationship);
		$this->addCall('addRelatedItems', $args);
		
		// this method can return a specific object if necessary - check.
		if( $this->isOverriden('addRelatedItems') ) {
			return $this->getOverridenResult('addRelatedItems');
		}
		else {
			return true;
		}
	}
}
?>
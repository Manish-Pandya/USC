<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../src/includes/ValidationManager.php');
require_once(dirname(__FILE__) . '/../../src/includes/classes/User.php');
	
Mock::generate('ValidationTest');

class TestValidationManager extends UnitTestCase {
	
	public function test_getPrefixNameForValidatableObjectOrArray(){
		$manager = new ValidationManager();
		
		$testObject = new ValidationTest();
		
		//Test array argument
		$arg = array('prefix-name', $testObject);
		$prefixName = $manager->getPrefixNameForValidatableObjectOrArray($arg);
		$this->assertEqual($prefixName, 'prefix-name');
		
		unset($arg);
		unset($prefixName);
		
		//Test object
		$arg = &$testObject;
		$prefixName = $manager->getPrefixNameForValidatableObjectOrArray($arg);
		$this->assertEqual($prefixName, 'validationtest');
	}
	
	public function test_getObjectForValidatableObjectOrArray(){
		$manager = new ValidationManager();
		
		$testObject = new ValidationTest();
		
		//Test array argument
		$arg = array('prefix-name', $testObject);
		$object = $manager->getObjectForValidatableObjectOrArray($arg);
		$this->assertEqual($object, $testObject);
		
		unset($arg);
		unset($object);
		
		//Test object
		$arg = &$testObject;
		$object = $manager->getObjectForValidatableObjectOrArray($arg);
		$this->assertEqual($object, $testObject);
	}
	
	public function test_addValidationRulesToValidator(){
		$manager = new ValidationManager();
		
		$testObject = new ValidationTest();
		$validator = new FormValidator();
		$rules = $testObject->getValidationRules();
		$prefixName = 'test-prefix';
		
		$expectedValidatorObject = new ValidatorObj();
		$expectedValidatorObject->variable_name = 'test-prefix_name';
		$expectedValidatorObject->validator_string = 'req';
		$expectedValidatorObject->error_string = 'name is a required field';
		
		$expectedValidationArray = array( $expectedValidatorObject );
		
		$manager->addValidationRulesToValidator($validator, $rules, $prefixName);
		
		$this->assertEqual($expectedValidationArray, $validator->validator_array);
	}
	
	public function test_getValidationRules(){
		$manager = new ValidationManager();
		
		$testObject = new MockValidationTest();
		
		// Asserts that getValidationRules is called once on $testObject
		$testObject->expectOnce('getValidationRules');
		
		$manager->getValidationRules($testObject);
	}
	
	public function test_processAndAddValidationRules_emptyArgs(){
		$manager = new ValidationManager();
		$validator = new FormValidator();
		$testObject = new ValidationTest();
		
		$expectedValidationArray = array();
		
		//Test with empty args;
		$args = array();
		
		$manager->processAndAddValidationRules($validator, $args);
		
		$this->assertEqual($expectedValidationArray, $validator->validator_array);
	}
	
	public function test_processAndAddValidationRules_arrayArgs(){
		$manager = new ValidationManager();
		$validator = new FormValidator();
		$testObject = new ValidationTest();
		
		$expectedValidatorObject = new ValidatorObj();
		$expectedValidatorObject->variable_name = 'test_name';
		$expectedValidatorObject->validator_string = 'req';
		$expectedValidatorObject->error_string = 'name is a required field';
		$expectedValidationArray = array( $expectedValidatorObject );
		
		//Test with array args
		$args = array( array('test', $testObject) );
		
		$manager->processAndAddValidationRules($validator, $args);
		
		$this->assertEqual($expectedValidationArray, $validator->validator_array);
	}
	
	public function test_processAndAddValidationRules_objectArgs(){
		$manager = new ValidationManager();
		$validator = new FormValidator();
		$testObject = new ValidationTest();
		
		$expectedValidatorObject = new ValidatorObj();
		$expectedValidatorObject->variable_name = 'validationtest_name';
		$expectedValidatorObject->validator_string = 'req';
		$expectedValidatorObject->error_string = 'name is a required field';
		$expectedValidationArray = array( $expectedValidatorObject );
		
		//Test with obj args
		$args = array( $testObject );
		
		$manager->processAndAddValidationRules($validator, $args);
		
		$this->assertEqual($expectedValidationArray, $validator->validator_array);
	}
	
	public function test_getValidator_noArgs(){
		$manager = new ValidationManager();
		
		$expectedValidationArray = array( );
		
		$validator = $manager->getValidator();
		
		//Assert that the array is what we expect
		$this->assertEqual($expectedValidationArray, $validator->validator_array);
	}
	
	public function test_getValidator_args(){
		$manager = new ValidationManager();
		
		$expectedValidatorObject = new ValidatorObj();
		$expectedValidatorObject->variable_name = 'validationtest_name';
		$expectedValidatorObject->validator_string = 'req';
		$expectedValidatorObject->error_string = 'name is a required field';
		
		$expectedValidationArray = array( $expectedValidatorObject );
		
		//Instantiate test class
		$test = new ValidationTest();
		
		$validator = $manager->getValidator($test);
		
		//Assert that the array is what we expect
		$this->assertEqual($expectedValidationArray, $validator->validator_array);
	}
	
}

class ValidationTest {
	function getValidationRules(){
		return array( 
			array(
				'name',
				'req',
				'name is a required field'
			)
		);
	}
}
?>
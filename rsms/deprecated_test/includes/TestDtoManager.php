<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../src/includes/DtoManager.php');
require_once(dirname(__FILE__) . '/../../src/includes/classes/User.php');

/**
 * Test case for testing the DtoManager.
 * 
 * @author Mitch Martin
 */
class TestDtoManager extends UnitTestCase {
	
	function test_autoSetFieldsFromArrayWithPrefix() {
		
		$prefix = 'test';
		
		$array = array(
			$prefix . "_username"=>'USERNAME',
		);
		
		$baseObject = new User();
		$baseObject = DtoManager::autoSetFieldsFromArray($array, $baseObject, $prefix);
		
		$this->assertEqual('USERNAME', $baseObject->getUsername());
	}
	
	function test_autoSetFieldsFromArrayWithNoPrefix() {
	
		$array = array(
				"user_username"=>'USERNAME',
		);
	
		$baseObject = new User();
		$baseObject = DtoManager::autoSetFieldsFromArray($array, $baseObject);
	
		$this->assertEqual('USERNAME', $baseObject->getUsername());
	}
	
	function test_autoSetFieldsFromArrayWithBadFunctionName(){
		$prefix = 'test';
		
		$array = array(
			$prefix . "_doesnotexist"=>'Does Not Exist',
		);
		
		$baseObject = new User();
		$baseObject = DtoManager::autoSetFieldsFromArray($array, $baseObject, $prefix);
	}
	
	function testGetPrefix_emptyValue(){
		$name = "";
		$validPrefix = "";
		$prefix = DtoManager::getPrefix($name);
		$this->assertEqual($validPrefix, $prefix);
	}
	
	function testGetPrefix_withValue(){
		$name = "name";
		$validPrefix = "name_";
		$prefix = DtoManager::getPrefix($name);
		$this->assertEqual($validPrefix, $prefix);
	}
	
	function test_getPrefixedFieldNamesAndValuesFromArray(){
		$baseArray = array(
			"test_username"	=>'USERNAME',
			"test_name"		=>'NAME',
			"test_nickname"	=>'NICKNAME',
		);
		
		$expectedArray = array(
			"username"	=>'USERNAME',
			"name"		=>'NAME',
			"nickname"	=>'NICKNAME',
		);
		
		$array = DtoManager::getPrefixedFieldNamesAndValuesFromArray('test', $baseArray);
		
		$this->assertEqual($expectedArray, $array);
	}
	
	function test_getPrefixedFieldNamesAndValuesFromArray_emptyPrefix(){
		$baseArray = array(
			"username"	=>'USERNAME',
			"name"		=>'NAME',
			"nickname"	=>'NICKNAME',
		);
		
		$expectedArray = array(
			"username"	=>'USERNAME',
			"name"		=>'NAME',
			"nickname"	=>'NICKNAME',
		);
		
		$array = DtoManager::getPrefixedFieldNamesAndValuesFromArray('', $baseArray);
		
		$this->assertEqual($expectedArray, $array);
	}
	
	function test_getFieldNameFromPrefixedKey(){
		
		//Test populated prefix
		$prefix = 'test_';
		$key = 'test_username';
		$expectedValue = 'username';
		$fieldName = DtoManager::getFieldNameFromPrefixedKey($prefix, $key);
		
		$this->assertEqual($expectedValue, $fieldName);
	}
	
	function test_getFieldNameFromPrefixedKey_emptyPrefix(){
	
		//Test empty prefix
		$prefix = '';
		$key = 'username';
		$expectedValue = 'username';
		$fieldName = DtoManager::getFieldNameFromPrefixedKey($prefix, $key);
		$this->assertEqual($expectedValue, $fieldName);
	}
	
	function test_buildSetterName(){
		$fieldName = 'username';
		$expectedValue = 'setUsername';
		$setterName = DtoManager::buildSetterName($fieldName);
		
		$this->assertEqual($expectedValue, $setterName);
	}
	
	function test_getDefaultPrefixNameForObject(){
		$object = new User();
		$expectedValue = 'user';
		$prefixName = DtoManager::getDefaultPrefixNameForObject($object);
		
		$this->assertEqual($expectedValue, $prefixName);
	}
}
?>
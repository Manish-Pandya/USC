<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../src/includes/JsonManager.php');
require_once(dirname(__FILE__) . '/../../src/includes/DtoManager.php');
require_once(dirname(__FILE__) . '/../../src/includes/classes/User.php');

class TestJsonManager extends UnitTestCase {
	
	function test_objectToJson_function(){
		
		$object = new JsonTestUser("username");
		
		$expectedJson = '{"Username":"username"}';
		$actualJson = JsonManager::objectToJson($object);
		
		$this->assertEqual($expectedJson, $actualJson);
	}
	
	function test_objectToJson_inferJson(){
	
		$object = new User();
		$object->setActive(TRUE);
		$object->setEmail('email@host.com');
		$object->setKeyid(1234);
		$object->setName("name");
		$object->setRoles(array('role1', 'role2'));
		$object->setUsername("username");
	
		$expectedJson = '{"Keyid":1234,"Active":true,"Roles":["role1","role2"],"Username":"username","Name":"name","Email":"email@host.com"}';
		$actualJson = JsonManager::objectToJson($object);
	
		$this->assertEqual($expectedJson, $actualJson);
	}
	
	function test_jsonToObject(){
		$expectedObject = new User();
		$expectedObject->setActive(TRUE);
		$expectedObject->setEmail('email@host.com');
		$expectedObject->setKeyid(1234);
		$expectedObject->setName("name");
		$expectedObject->setRoles(array('role1', 'role2'));
		$expectedObject->setUsername("username");
		
		$json = '{"Keyid":1234,"Active":true,"Roles":["role1","role2"],"Username":"username","Name":"name","Email":"email@host.com"}';
		$object = new User();
		$object = JsonManager::jsonToObject($json, $object);
		
		$this->assertEqual($expectedObject, $object);
	}
}

class JsonTestUser {
	private $username;
	public function __construct($username){
		$this->username = $username;
	}
	
	public function toJson(){
		return '{"Username":"' . $this->username . '"}';
	}
}
?>
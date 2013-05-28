<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../src/includes/JsonManager.php');
require_once(dirname(__FILE__) . '/../../src/includes/DtoManager.php');
require_once(dirname(__FILE__) . '/../../src/includes/classes/User.php');

Mock::generate('User');

class TestJsonManager extends UnitTestCase {
	
	function test_encodeJsonKeyValuePairs(){
		//Build array to encode
		$arrayToEncode = array(
			'string1'=>'one',
			'string2'=>'two',
			'string3'=>3,
			'stringfalse'=>FALSE
		);
		
		$expectedArray = array(
			'"string1":"one"',
			'"string2":"two"',
			'"string3":3',
			'"stringfalse":false'
		);
		
		$encodedArray = JsonManager::encodeJsonKeyValuePairs($arrayToEncode);
		
		$this->assertEqual($expectedArray, $encodedArray);
	}
	
	function test_callObjectAccessors(){
		$object = new MockUser();
		
		//Expect all getter methods to be called once
		$object->expectOnce('getActive');
		$object->expectOnce('getEmail');
		$object->expectOnce('getKeyid');
		$object->expectOnce('getName');
		$object->expectOnce('getRoles');
		$object->expectOnce('getUsername');

		//Expect no other method to be called
		$object->expectNever('getTableName');
		$object->expectNever('getColumnData');
		$object->expectNever('setActive');
		$object->expectNever('setEmail');
		$object->expectNever('setKeyid');
		$object->expectNever('setName');
		$object->expectNever('setRoles');
		$object->expectNever('setUsername');
		
		JsonManager::callObjectAccessors($object);
	}
	
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
	
	function test_encode_function(){
	
		$object = new JsonTestUser("username");
	
		$expectedJson = '{"Username":"username"}';
		$actualJson = JsonManager::encode($object);
	
		$this->assertEqual($expectedJson, $actualJson);
	}
	
	function test_encode_inferJson(){
	
		$object = new User();
		$object->setActive(TRUE);
		$object->setEmail('email@host.com');
		$object->setKeyid(1234);
		$object->setName("name");
		$object->setRoles(array('role1', 'role2'));
		$object->setUsername("username");
	
		$expectedJson = '{"Keyid":1234,"Active":true,"Roles":["role1","role2"],"Username":"username","Name":"name","Email":"email@host.com"}';
		$actualJson = JsonManager::encode($object);
	
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
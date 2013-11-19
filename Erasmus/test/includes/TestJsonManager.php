<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../src/includes/JsonManager.php');
require_once(dirname(__FILE__) . '/../../src/includes/DtoManager.php');
require_once(dirname(__FILE__) . '/../../src/includes/classes/User.php');

Mock::generate('User');

class TestJsonManager extends UnitTestCase {
	
	function test_decode(){
		$json = '{"Class":"User","Keyid":1234,"Active":true,"Roles":["role1","role2"],"Username":"username","Name":"name","Email":"email@host.com","IsActive":true}';
		
		$expectedObject = new User();
		$expectedObject->setIsActive(TRUE);
		$expectedObject->setEmail('email@host.com');
		$expectedObject->setKeyId(1234);
		$expectedObject->setName("name");
		$expectedObject->setRoles(array('role1', 'role2'));
		$expectedObject->setUsername("username");
		
		$actualObject = JsonManager::decode($json);
		
		$this->assertEqual($expectedObject, $actualObject);
	}
	
	function test_buildModelObject(){
		$array = array('Class' => 'JsonTestUser');
		
		// Assert that passing no object will result in type inference
		$actualObject = JsonManager::buildModelObject($array);
		$this->assertTrue( is_a($actualObject, 'JsonTestUser') );
		
		// Assert that passing an object results in no type inference
		$modelObject = new User();
		$actualObject = JsonManager::buildModelObject($array, $modelObject);
		$this->assertFalse( is_a($actualObject, 'JsonTestUser'));
	}
	
	function test_assembleObjectFromDecodedArray(){
		$expectedObject = new User();
		$expectedObject->setUsername('Test');
		$expectedObject->setName('Testerson');
		
		$array = array(
			'Class'    => 'User',
			'Username' => 'Test',
			'Name'     => 'Testerson',
		);
		
		$actualObject = JsonManager::assembleObjectFromDecodedArray($array);
		
		$this->assertEqual($expectedObject, $actualObject);
	}
	
	function test_callObjectAccessors(){
		$object = new MockUser();
		
		//Expect all getter methods to be called once
		$object->expectOnce('getIsActive');
		$object->expectOnce('getEmail');
		$object->expectOnce('getKeyid');
		$object->expectOnce('getName');
		$object->expectOnce('getRoles');
		$object->expectOnce('getUsername');

		//Expect no other method to be called
		$object->expectNever('getTableName');
		$object->expectNever('getColumnData');
		$object->expectNever('setIsActive');
		$object->expectNever('setEmail');
		$object->expectNever('setKeyId');
		$object->expectNever('setName');
		$object->expectNever('setRoles');
		$object->expectNever('setUsername');
		
		JsonManager::callObjectAccessors($object);
	}
	
	function test_jsonToObject(){
		$expectedObject = new User();
		$expectedObject->setIsActive(TRUE);
		$expectedObject->setEmail('email@host.com');
		$expectedObject->setKeyId(1234);
		$expectedObject->setName("name");
		$expectedObject->setRoles(array('role1', 'role2'));
		$expectedObject->setUsername("username");
		
		$json = '{"Class":"User","IsActive":true,"Roles":["role1","role2"],"Username":"username","Name":"name","Email":"email@host.com","KeyId":1234}';
		$object = new User();
		$object = JsonManager::jsonToObject($json, $object);
		
		$this->assertEqual($expectedObject, $object);
	}
	
	function test_buildJsonableValue(){
		$expectedObject = array(
			'Class' => 'User',
			'Roles' => array('role1', 'role2'),
			'Username' => 'username',
			'Name' => 'name',
			'Email' => 'email@host.com',
			'KeyId' => 1234,
			'DateCreated' => NULL,
			'DateLastModified' => NULL,
			'IsActive' => true,
		);

		$user = new User();
		$user->setIsActive(TRUE);
		$user->setEmail('email@host.com');
		$user->setKeyId(1234);
		$user->setName('name');
		$user->setRoles(array('role1', 'role2'));
		$user->setUsername('username');
		
		$jsonableObject = JsonManager::buildJsonableValue($user);
		
		$this->assertEqual($expectedObject, $jsonableObject);
	}
}

class JsonTestUser {
	private $username;
	public function __construct($username = 'default_username'){
		$this->username = $username;
	}
		
	public function toJson(){
		return '{"Username":"' . $this->username . '"}';
	}
}
?>
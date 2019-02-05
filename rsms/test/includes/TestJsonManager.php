<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../src/includes/JsonManager.php');
require_once(dirname(__FILE__) . '/../../src/includes/DtoManager.php');
require_once(dirname(__FILE__) . '/../../src/includes/classes/User.php');

Mock::generate('User');
Mock::generate('TestEntityMaps');

class TestJsonManager extends UnitTestCase {
	
	function test_decode(){
		$json = '{"Class":"User","Key_Id":1234,"Roles":["role1","role2"],"Username":"username","First_name":"name","Email":"email@host.com"}';
		
		$expectedObject = new User();
		$expectedObject->setEmail('email@host.com');
		$expectedObject->setKey_Id(1234);
		$expectedObject->setFirst_name("name");
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
		$expectedObject->setLast_name('Testerson');
		
		$array = array(
			'Class'    => 'User',
			'Username' => 'Test',
			'Last_name'     => 'Testerson',
		);
		
		$actualObject = JsonManager::assembleObjectFromDecodedArray($array);
		
		$this->assertEqual($expectedObject, $actualObject);
	}
	
	function test_callObjectAccessors(){
		$object = new MockUser();
		
		//Expect all getter methods to be called once
		$object->expectOnce('getEmail');
		$object->expectOnce('getKey_Id');
		$object->expectOnce('getFirst_name');
		$object->expectOnce('getRoles');
		$object->expectOnce('getUsername');

		//Expect no other method to be called
		$object->expectNever('getTableName');
		$object->expectNever('getColumnData');
		$object->expectNever('setEmail');
		$object->expectNever('setKey_Id');
		$object->expectNever('setFirst_name');
		$object->expectNever('setRoles');
		$object->expectNever('setUsername');
		
		JsonManager::callObjectAccessors($object);
	}
	
	function test_callObjectAccessors_withEntityMaps(){
		$expected = array(
			'Class' => 'TestEntityMaps',
			'Field1' => '1',
			'Field2' => '2',
			'Field3' => null
		);

		$object = new TestEntityMaps();
		$actual = JsonManager::callObjectAccessors($object);

		$this->assertEqual($expected, $actual);
	}
	
	function test_jsonToObject(){
		$expectedObject = new User();
		$expectedObject->setEmail('email@host.com');
		$expectedObject->setKey_Id(1234);
		$expectedObject->setFirst_name("name");
		$expectedObject->setRoles(array('role1', 'role2'));
		$expectedObject->setUsername("username");
		
		$json = '{"Class":"User","Roles":["role1","role2"],"Username":"username","First_name":"name","Email":"email@host.com","Key_Id":1234}';
		$object = new User();
		$object = JsonManager::jsonToObject($json, $object);
		
		$this->assertEqual($expectedObject, $object);
	}

	function test_buildJsonableValue(){
		$expectedObject = array(
			'Class' => 'TestObject',
			'Field1' => 'value1',
			'Field2' => 'value2'
		);

		$obj = new TestObject();
		$obj->setField1('value1');
		$obj->setField2('value2');

		$jsonableObject = JsonManager::buildJsonableValue($obj);
		
		$this->assertEqual($expectedObject, $jsonableObject);
	}

	function test_mergeEntityMaps(){
		$maps = array(
			EntityMap::eager("getField1"),
			EntityMap::eager("getField2")
		);
		$over = array(
			EntityMap::lazy("getField2"),
			EntityMap::lazy("getField3")
		);

		$expected = array(
			EntityMap::eager("getField1"),
			EntityMap::lazy("getField2"),
			EntityMap::lazy("getField3")
		);

		$this->assertEqual($expected, JsonManager::mergeEntityMaps($maps, $over));
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

class TestObject {
	private $field1;
	private $field2;

	public function getField1(){return  $this->field1;}
	public function setField1($field1){$this->field1 = $field1;}
	public function getField2(){return  $this->getField2;}
	public function setField2($getField2){$this->getField2 = $getField2;}
}

class TestEntityMaps {

	public function getField1(){return 1;}
	public function getField2(){return 2;}
	public function getField3(){return 3;}

	public function getEntityMaps(){
		return array(
			EntityMap::eager("getField1"),
			EntityMap::eager("getField2"),
			EntityMap::lazy("getField3")
		);
	}
}
?>
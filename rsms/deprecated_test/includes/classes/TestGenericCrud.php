<?php
require_once(dirname(__FILE__) . '/../../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../../src/includes/classes/GenericCrud.php');
require_once(dirname(__FILE__) . '/../../../src/includes/classes/User.php');
require_once(dirname(__FILE__) . '/../../../src/includes/classes/Role.php');

class TestGenericCrud extends UnitTestCase {
	
	//Use User as test case
	function testPopulateFromDbRecord() {
		//Using user class to test.
		//TODO: Should this be tested for all GenericCrud classes?
		$user = new User();
	
		//Build a "DB" stdClass to pass
		$dbObject = new stdClass;
		
		//GenericCrud data
		$dbObject->key_id = 1234;
		$dbObject->isactive = true;
		$dbObject->datecreated = time();
		$dbObject->datelastmodified = time();
		
		//User data
		//Create some roles
		$r1 = new Role();
		$r2 = new Role();
		$r1->setName("Role1");
		$r2->setName("Role2");
		
		$dbObject->roles = array($r1, $r2);
		$dbObject->username = 'USER_NAME';
		$dbObject->name = 'REAL_NAME';
		$dbObject->email = 'user@host.com';
	
		$user->populateFromDbRecord( $dbObject );
	
		//Assert that $user's attributes are equal to the db object's
		$columns = array_keys( $user->getColumnData() );
		foreach( $columns as $field ) {
				
			//build the accessor method name
			$fieldName = "get$field";
			
			// NOTE: DB call instantiates stdClass fields as all lower case,
			//  so we must access them as lower case
			$lowerField = strtolower($field);
			
			//Compare the accessor's value to the expected value from the mock array
			$this->assertEqual($user->$fieldName(), $dbObject->$lowerField);
		}
	}
}

?>
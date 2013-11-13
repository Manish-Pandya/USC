<?php
require_once(dirname(__FILE__) . '/../../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../../src/includes/classes/GenericCrud.php');
require_once(dirname(__FILE__) . '/../../../src/includes/classes/User.php');

class TestGenericCrud extends UnitTestCase {
	
	//Use User as test case
	function testPopulateFromDbRecord() {
		$user = new User();
	
		//Build a "DB" array to pass
		$dbObject = array(
				"keyid"		=> 1234,
				"isActive"	=> true,
				"roles"		=> array('TEST_ROLE_1', 'TEST_ROLE_2'),
				"username"	=> 'USER_NAME',
				"name"		=> 'REAL_NAME',
				"email"		=> 'user@host.com',
		);
	
		$user->populateFromDbRecord( $dbObject );
	
		//Assert that $user's attributes are equal to the db object's
		$columns = array_keys( $user->getColumnData() );
		foreach( $columns as $field ) {
				
			//build the accessor method name
			$fieldName = "get$field";
			
			//Compare the accessor's value to the expected value from the mock array
			$this->assertEqual($user->$fieldName(), $dbObject[$field]);
		}
	}
}

?>
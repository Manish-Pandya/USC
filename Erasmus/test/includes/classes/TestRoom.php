<?php
require_once(dirname(__FILE__) . '/../../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../../src/includes/classes/Room.php');

/**
 * Test case for testing the Room class.
 * 
 * @author Mitch Martin
 */
class TestRoom extends UnitTestCase {
	
	function testPopulateFromDbRecord() {
		$room = new Room(); // new MockGenericCrud();
	
		//Build a "DB" array to pass
		$dbObject = array(
			"keyid"		=> 1234,
			"active"	=> true,
			"name"		=> 'REAL_NAME',
		);
	
		$room->populateFromDbRecord( $dbObject );
	
		//Assert that $room's attributes are equal to the db object's
		$columns = array_keys( $room->getColumnData() );
		foreach( $columns as $field ) {
			
			//build the accessor method name
			$fieldName = "get$field";
			
			//Compare the accessor's value to the expected value from the mock array
			$this->assertEqual($room->$fieldName(), $dbObject[$field]);
		}
	}
	
}
?>
<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../src/includes/DtoBuilder.php');
require_once(dirname(__FILE__) . '/../../src/includes/classes/User.php');

/**
 * Test case for testing the DtoBuilder.
 * 
 * @author Mitch Martin
 */
class TestDtoBuilder extends UnitTestCase {
	
	function testAutoSetFieldsFromArray() {
		
		$prefix = 'test';
		
		$array = array(
			$prefix . "_username"=>'USERNAME',
		);
		
		$baseObject = new User();
		$baseObject = DtoBuilder::autoSetFieldsFromArray($array, $baseObject, $prefix);
		
		$this->assertEqual('USERNAME', $baseObject->getUsername());
	}
	
	//TODO: Do we need to test autoSetFieldsFromRequest? It's just a delegate to autoSetFieldsFromArray
	
	function testAutoSetFieldsFromArray_badFunctionName(){
		$prefix = 'test';
		
		$array = array(
			$prefix . "_doesnotexist"=>'Does Not Exist',
		);
		
		$baseObject = new User();
		$baseObject = DtoBuilder::autoSetFieldsFromArray($array, $baseObject, $prefix);
	}
}
?>
<?php
require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../src/includes/ActionDispatcher.php');

class TestActionDispatcher extends UnitTestCase {
	
	function test_dispatchError_default(){
		$dataSource = array();
		$dispatcher = new ActionDispatcher($dataSource);
		
		$this->assertEqual($dispatcher->dispatchError(), 'forbidden.php');
	}
	
	function test_dispatchError_setDefault(){
		$dataSource = array();
		$dispatcher = new ActionDispatcher($dataSource, 'error-page.php');
	
		$this->assertEqual($dispatcher->dispatchError(), 'error-page.php');
	}
	
	function test_dispatchError_action(){
		//TODO: 
	}
	
	function test_dispatchSuccess(){
		//TODO:
	}
	
	function test_checkRoles(){
		//TODO:
	}
	
	function test_doAction(){
		//TODO:
	}
	
	function test_dispatch(){
		//TODO:
	}
	
}
?>
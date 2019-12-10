<?php
require_once(dirname(__FILE__) . '/../../src/Autoloader.php');

require_once(dirname(__FILE__) . '/../simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../../src/includes/ActionDispatcher.php');
require_once(dirname(__FILE__) . '/../../src/includes/ActionMappingFactory.php');
require_once(dirname(__FILE__) . '/../../src/includes/classes/ActionMapping.php');

Mock::generate('ActionMappingFactory');

class TestActionDispatcher extends UnitTestCase {
	
	function test_dispatchError_default(){
		$dataSource = array();
		$dispatcher = new ActionDispatcher($dataSource);
		
		$result = new ActionResult();
		$dispatcher->dispatchError($result);
		
		$this->assertEqual($result->destinationPage, 'forbidden.php');
		$this->assertEqual($result->statusCode, 500);
	}
	
	function test_dispatchError_setDefault(){
		$dataSource = array();
		$dispatcher = new ActionDispatcher($dataSource);
		$dispatcher->setDefaultErrorPage('error-page.php');
		
		$result = new ActionResult();
		$dispatcher->dispatchError($result);
	
		$this->assertEqual($result->destinationPage, 'error-page.php');
		$this->assertEqual($result->statusCode, 500);
	}
	
	function test_dispatchError_action(){
		$dispatcher = new ActionDispatcher(array());
		$mapping = new ActionMapping('action.php', 'success.php', 'error.php', array());
		
		$result = new ActionResult();
		$dispatcher->dispatchError($result, $mapping);
		
		$this->assertEqual($result->destinationPage, 'error.php');
	}
	
	function test_dispatchSuccess(){
		$dispatcher = new ActionDispatcher(array());
		$mapping = new ActionMapping('action.php', 'success.php', 'error.php', array());
		
		$result = new ActionResult();
		$dispatcher->dispatchSuccess($result, $mapping);
		
		$this->assertEqual($result->destinationPage, 'success.php');
	}
	
	function test_getActionMappings_default(){
		$dispatcher = new ActionDispatcher(array());
		
		$expectedMappings = ActionMappingFactory::readActionConfig();
		$actualMappings = $dispatcher->getActionMappings();
		
		$this->assertEqual($expectedMappings, $actualMappings);
	}
	
	function test_getActionMappings_set(){
		$factory = new MockActionMappingFactory();
		$factory->expectOnce('getConfig');
		
		$dispatcher = new ActionDispatcher(array(), array(), $factory);
		
		//Call to test expectations
		$dispatcher->getActionMappings();
	}
	
	function test_checkRoles_empty(){
		$actionMapping = new ActionMapping(
				"accessDeniedTestAction",
				"access-denied-success.php",
				"access-denied-failed.php"
		);
		
		$datasource = array();
		
		$dispatcher = new ActionDispatcher($datasource);
		$access = $dispatcher->checkRoles($actionMapping);
		
		$this->assertTrue($access);
	}
	
	function test_checkRoles_fail(){
		$actionMapping = new ActionMapping(
				"accessDeniedTestAction",
				"access-denied-success.php",
				"access-denied-failed.php",
				array("ROLE3")
		);
		
		$datasource = array(
				"ROLE" => array("ROLE1", "ROLE2"),
		);
		
		$dispatcher = new ActionDispatcher($datasource);
		$access = $dispatcher->checkRoles($actionMapping);
		
		$this->assertFalse($access);
	}
	
	function test_checkRoles_success(){
		$actionMapping = new ActionMapping(
			"accessDeniedTestAction",
			"access-denied-success.php",
			"access-denied-failed.php",
			array("ROLE1")
		);
		
		$datasource = array();
		$sessionSource = array(
			"ROLE" => array(
				"userRoles" => array("ROLE1", "ROLE2")
			)
		);
		
		$dispatcher = new ActionDispatcher($datasource, $sessionSource);
		$access = $dispatcher->checkRoles($actionMapping);
		
		$this->assertTrue($access);
	}
	
	function test_doAction_success(){
		$actionMapping = new ActionMapping(
			"functionTestActionSuccess",
			"function-success.php",
			"function-failed.php",
			array("ROLE1")
		);
		
		//Define success function
		function functionTestActionSuccess(){
			return true;
		}
		
		$dispatcher = new ActionDispatcher(array());
		$functionSuccess = $dispatcher->doAction($actionMapping);
		
		$this->assertTrue($functionSuccess);
	}
	
	function test_doAction_fail(){
		$actionMapping = new ActionMapping(
			"functionTestActionFailure",
			"function-success.php",
			"function-failed.php",
			array("ROLE1")
		);
		
		//Define success function
		function functionTestActionFailure(){
			return false;
		}
		
		$dispatcher = new ActionDispatcher(array());
		$functionSuccess = $dispatcher->doAction($actionMapping);
		
		$this->assertFalse($functionSuccess);
	}
	
	function test_doAction_functionDoesNotExist(){
		$actionMapping = new ActionMapping(
			"nonExistentFunction",
			"function-success.php",
			"function-failed.php",
			array("ROLE1")
		);
		
		$dispatcher = new ActionDispatcher(array());
		$functionSuccess = $dispatcher->doAction($actionMapping);
		
		$this->assertTrue( $functionSuccess instanceof ActionError);
	}
	
	function test_dispatch_nullAction(){
		//data source doesn't matter
		$dispatcher = new ActionDispatcher(array());
		
		$action = NULL;
		$dispatchedResult = $dispatcher->dispatch($action);
		$dispatchedPage = $dispatchedResult->destinationPage;
		
		$this->assertEqual($dispatchedPage, 'forbidden.php');
	}
	
	function test_dispatch_invalidAction(){
		$factory = new MockActionMappingFactory();
		$factory->returns('getConfig', array());
		
		$dispatcher = new ActionDispatcher(array(), $factory);
		$dispatchedResult = $dispatcher->dispatch('noSuchAction');
		$dispatchedPage = $dispatchedResult->destinationPage;
		
		$this->assertEqual($dispatchedPage, 'forbidden.php');
	}
	
	function test_dispatch_accessDenied(){
		$factory = new MockActionMappingFactory();
		$factory->returns('getConfig', array(
			'accessDeniedTest' => new ActionMapping(
				"accessDeniedTestAction",
				"access-denied-success.php",
				"access-denied-failed.php",
				array("Admin"))
		));
		
		$dispatcher = new ActionDispatcher(array(), $factory);
		$dispatchedResult = $dispatcher->dispatch('accessDeniedTest');
		$dispatchedPage = $dispatchedResult->destinationPage;
		
		$this->assertEqual($dispatchedPage, 'access-denied-failed.php');
	}
	
	function test_dispatch_actionFail(){
		$factory = new MockActionMappingFactory();
		$factory->returns('getConfig', array(
			'dispatchActionFailTest' => new ActionMapping(
				"dispatchActionFailTestAction",
				"dispatchActionFailSuccess.php",
				"dispatchActionFailFailed.php",
				array())
		));
		
		$dispatcher = new ActionDispatcher(array(), $factory);
		$dispatchedResult = $dispatcher->dispatch('dispatchActionFailTest');
		$dispatchedPage = $dispatchedResult->destinationPage;
		
		//Method doesn't exist, so dispatch will fail
		
		$this->assertEqual($dispatchedPage, 'dispatchActionFailFailed.php');
	}
	
	function test_dispatch_actionSuccess(){
		$factory = new MockActionMappingFactory();
		$factory->returns('getConfig', array(
			'dispatchActionSuccessTest' => new ActionMapping(
				"dispatchActionSuccessTestAction",
				"dispatchActionSuccessSuccess.php",
				"dispatchActionSuccessFailed.php",
				array())
		));
		
		$dispatcher = new ActionDispatcher(array(), $factory);
		
		//Define function
		function dispatchActionSuccessTestAction(){
			return true;
		}
		
		$dispatchedResult = $dispatcher->dispatch('dispatchActionSuccessTest');
		$dispatchedPage = $dispatchedResult->destinationPage;
		
		$this->assertEqual($dispatchedPage, 'dispatchActionSuccessSuccess.php');
	}
}
?>
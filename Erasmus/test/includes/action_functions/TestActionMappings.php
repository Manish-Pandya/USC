<?php
require_once(dirname(__FILE__) . '/../../simpletest/autorun.php');

//Include action functions to test
require_once(dirname(__FILE__) . '/../../../src/includes/classes/ActionMapping.php');
require_once(dirname(__FILE__) . '/../../../src/includes/ActionMappingFactory.php');
require_once(dirname(__FILE__) . '/../../../src/includes/action_functions.php');

class TestActionMappings extends UnitTestCase {
	
	public function test_mappedFunctionsExist(){
		$config = ActionMappingFactory::readActionConfig();
		
		foreach( $config as $name => $mapping ){
			$this->assertTrue( function_exists( $mapping->actionFunctionName ) );
		}
	}
	
}

?>
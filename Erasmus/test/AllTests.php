<?php

require_once('simpletest/autorun.php');

/**
 * Master Test Suite; Finds and executes all tests.
 */
class AllTests extends TestSuite {
	
	function __construct(){
		parent::__construct();
		
		$testCollector = new SimplePatternCollector('/Test.*.php/');
		
		// TODO: Search tree
		// Collect php files that begin with Test in /includes/classes
		$this->collect(dirname(__FILE__) . '/includes', $testCollector);
		$this->collect(dirname(__FILE__) . '/includes/classes', $testCollector);
		$this->collect(dirname(__FILE__) . '/includes/action_functions', $testCollector);
	}
	
}

?>
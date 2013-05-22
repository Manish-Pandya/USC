<?php

require_once('simpletest/autorun.php');
require_once('../src/Autoloader.php');
Autoloader::init();

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
	}
	
}

?>
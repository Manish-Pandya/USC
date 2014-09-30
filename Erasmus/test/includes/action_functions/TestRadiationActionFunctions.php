<?php
require_once(dirname(__FILE__) . '/../../simpletest/autorun.php');

require_once(dirname(__FILE__) . '/../../../src/Autoloader.php');
Logger::configure( dirname(__FILE__) . "/../../../src/includes/conf/log4php-config.php");

// Include action functions to test
require_once(dirname(__FILE__) . '/../../../src/includes/Rad_action_functions.php');

// Radiation action functions depend on some standard action functions as well
require_once(dirname(__FILE__) . '/../../../src/includes/action_functions.php');


/*
	IMPORTANT: Until I can find a different way, most of these tests are dependent on
	entities having a record in the database with key_id = 1
*/


class TestRadiationActionFunctions extends UnitTestCase {
	
	// Reset $_REQUEST between tests so that tests aren't affected by previous ones.
	function tearDown() {
		foreach( $_REQUEST as $key=>$value ) {
			unset( $_REQUEST[$key] );
		}
	}
	
	// getIsotopeById
	public function test_getIsotopeById_noId() {
		$isotope = getIsotopeById();
		$this->assertTrue( $isotope instanceof ActionError);
	}
	public function test_getIsotopeById_passId() {
		$isotope = getIsotopeById(1);
		$this->assertTrue( $isotope instanceof Isotope);
	}
	public function test_getIsotopeById_requestId() {
		$_REQUEST['id'] = 1;
		$isotope = getIsotopeById();
		$this->assertTrue( $isotope instanceof Isotope);
	}
	
	// getCarboyById
	public function test_getCarboyById_noId() {
		$carboy = getCarboyById();
		$this->assertTrue( $carboy instanceof ActionError);
	}
	public function test_getCarboyById_passId() {
		$carboy = getCarboyById(1);
		$this->assertTrue( $carboy instanceof Carboy);
	}
	public function test_getCarboyById_requestId() {
		$_REQUEST['id'] = 1;
		$carboy = getCarboyById();
		$this->assertTrue( $carboy instanceof Carboy);
	}
	
}
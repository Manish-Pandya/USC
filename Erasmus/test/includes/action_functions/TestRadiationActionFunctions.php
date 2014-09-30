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
	entities having a record in the database with the following key id:
*/
define("KEY_ID", 1);


class TestRadiationActionFunctions extends UnitTestCase {
	
	// Reset $_REQUEST between tests so that tests using $_REQUEST don't affect each other
	function tearDown() {
		foreach( $_REQUEST as $key=>$value ) {
			unset( $_REQUEST[$key] );
		}
	}
	
	// getIsotopeById
	public function test_getIsotopeById_noId() {
		$isotope = getIsotopeById();
		$this->assertTrue( $isotope instanceof ActionError );
	}
	public function test_getIsotopeById_passId() {
		$isotope = getIsotopeById( KEY_ID );
		$this->assertTrue( $isotope instanceof Isotope );
		$this->assertEqual( $isotope->getKey_id(), KEY_ID );
	}
	public function test_getIsotopeById_requestId() {
		$_REQUEST['id'] = KEY_ID;
		$isotope = getIsotopeById();
		$this->assertTrue( $isotope instanceof Isotope );
		$this->assertEqual( $isotope->getKey_id(), KEY_ID );
	}
	
	// getCarboyById
	public function test_getCarboyById_noId() {
		$carboy = getCarboyById();
		$this->assertTrue( $carboy instanceof ActionError );
	}
	public function test_getCarboyById_passId() {
		$carboy = getCarboyById( KEY_ID );
		$this->assertTrue( $carboy instanceof Carboy );
		$this->assertEqual( $carboy->getKey_id(), KEY_ID );
	}
	public function test_getCarboyById_requestId() {
		$_REQUEST['id'] = 1;
		$carboy = getCarboyById();
		$this->assertTrue( $carboy instanceof Carboy );
		$this->assertEqual( $carboy->getKey_id(), KEY_ID );
	}
	
}
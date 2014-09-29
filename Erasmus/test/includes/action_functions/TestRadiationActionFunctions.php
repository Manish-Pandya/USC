<?php
require_once(dirname(__FILE__) . '/../../simpletest/autorun.php');

require_once(dirname(__FILE__) . '/../../../src/Autoloader.php');
Logger::configure( dirname(__FILE__) . "/../../../src/includes/conf/log4php-config.php");

// Include action functions to test
require_once(dirname(__FILE__) . '/../../../src/includes/Rad_action_functions.php');

// Radiation action functions depend on some standard action functions as well
require_once(dirname(__FILE__) . '/../../../src/includes/action_functions.php');


class TestRadiationActionFunctions extends UnitTestCase {
	
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
	
	/* Note to self:
	 * 
	 * perhaps instead of assertTrue( $isotope instanceof Isotope);
	 * use assertEqual(gettype($isotope), Isotope);
	 * 
	 * Reason being, if it fails, output will say what the type
	 * it recieved was, instead of just saying it got false when
	 * expecting true.
	 * 
	 * Try out later
	 */
	
	 // Other note to self: is there a way to be less dependent on Isotope with
	 // Key_id = 1 existing in the database? right now, if that one particular
	 // isotope is removed, it would cause test to fail.
	
	
}
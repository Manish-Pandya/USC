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
		$_REQUEST['id'] = KEY_ID;
		$carboy = getCarboyById();
		$this->assertTrue( $carboy instanceof Carboy );
		$this->assertEqual( $carboy->getKey_id(), KEY_ID );
	}
	

	// getCarboyUseCycleById
	public function test_getCarboyUseCycleById_noId() {
		$cycle = getCarboyUseCycleById();
		$this->assertTrue( $cycle instanceof ActionError );
	}

	public function test_getCarboyUseCycleById_passId() {
		$cycle = getCarboyUseCycleById( KEY_ID );
		$this->assertTrue( $cycle instanceof CarboyUseCycle );
		$this->assertEqual( $cycle->getKey_id(), KEY_ID );
	}

	public function test_getCarboyUseCycleById_requestId() {
		$_REQUEST['id'] = KEY_ID;
		$cycle = getCarboyUseCycleById();
		$this->assertTrue( $cycle instanceof CarboyUseCycle );
		$this->assertEqual( $cycle->getKey_id(), KEY_ID );
	}
	

	// getDisposalLotById
	public function test_getDisposalLotById_noId() {
		$lot = getDisposalLotById();
		$this->assertTrue( $lot instanceof ActionError );
	}

	public function test_getDisposalLotById_passId() {
		$lot = getDisposalLotById( KEY_ID );
		$this->assertTrue( $lot instanceof DisposalLot );
		$this->assertEqual( $lot->getKey_id(), KEY_ID );
	}
	
	public function test_getDisposalLotById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$lot = getDisposalLotById();
		$this->assertTrue( $lot instanceof DisposalLot );
		$this->assertEqual( $lot->getKey_id(), KEY_ID );
	}
	

	// getDrumById
	public function test_getDrumById_noId() {
		$drum = getDrumById();
		$this->assertTrue( $drum instanceof ActionError );
	}
	
	public function test_getDrumById_passId() {
		$drum = getDrumById( KEY_ID );
		$this->assertTrue( $drum instanceof Drum );
		$this->assertEqual( $drum->getKey_id(), KEY_ID );
	}
	
	public function test_getDrumById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$drum = getDrumById();
		$this->assertTrue( $drum instanceof Drum );
		$this->assertEqual( $drum->getKey_id(), KEY_ID );
	}
	

	// getParcelByid
	public function test_getParcelById_noId() {
		$parcel = getParcelById();
		$this->assertTrue( $parcel instanceof ActionError );
	}
	
	public function test_getParcelById_passId() {
		$parcel = getParcelById( KEY_ID );
		$this->assertTrue( $parcel instanceof Parcel );
		$this->assertEqual( $parcel->getKey_id(), KEY_ID );
	}
	
	public function test_getParcelById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$parcel = getParcelById();
		$this->assertTrue( $parcel instanceof Parcel );
		$this->assertEqual( $parcel->getKey_id(), KEY_ID );
	}
	
	
	// getParcelUseById
	public function test_getParcelUseById_noId() {
		$use = getParcelUseById();
		$this->assertTrue( $use instanceof ActionError );
	}

	public function test_getParcelUseById_passId() {
		$use = getParcelUseById( KEY_ID );
		$this->assertTrue( $use instanceof ParcelUse );
		$this->assertEqual( $use->getKey_id(), KEY_ID );
	}

	public function test_getParcelUseById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$use = getParcelUseById();
		$this->assertTrue( $use instanceof ParcelUse );
		$this->assertEqual( $use->getKey_id(), KEY_ID );
	}
	

	// getPickupById
	public function test_getPickupById_noId() {
		$pickup = getPickupById();
		$this->assertTrue( $pickup instanceof ActionError );
	}

	public function test_getPickupById_passId() {
		$pickup = getPickupById( KEY_ID );
		$this->assertTrue( $pickup instanceof Pickup );
		$this->assertEqual( $pickup->getKey_id(), KEY_ID );
	}

	public function test_getPickupById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$pickup = getPickupById();
		$this->assertTrue( $pickup instanceof Pickup );
		$this->assertEqual( $pickup->getKey_id(), KEY_ID );
	}
}
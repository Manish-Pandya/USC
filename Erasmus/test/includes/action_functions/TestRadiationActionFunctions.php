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
	
	
	// tests for basic getters
	
	// getIsotopeById
	public function test_getIsotopeById_noId() {
		$isotope = getIsotopeById();
		$this->assertIsA( $isotope, 'ActionError' );
	}

	public function test_getIsotopeById_passId() {
		$isotope = getIsotopeById( KEY_ID );
		$this->assertIsA( $isotope, 'Isotope' );
		$this->assertEqual( $isotope->getKey_id(), KEY_ID );
	}
	
	public function test_getIsotopeById_requestId() {
		$_REQUEST['id'] = KEY_ID;
		$isotope = getIsotopeById();
		$this->assertIsA( $isotope, 'Isotope' );
		$this->assertEqual( $isotope->getKey_id(), KEY_ID );
	}
	

	// getCarboyById
	public function test_getCarboyById_noId() {
		$carboy = getCarboyById();
		$this->assertIsA( $carboy, 'ActionError' );
	}

	public function test_getCarboyById_passId() {
		$carboy = getCarboyById( KEY_ID );
		$this->assertIsA( $carboy, 'Carboy' );
		$this->assertEqual( $carboy->getKey_id(), KEY_ID );
	}

	public function test_getCarboyById_requestId() {
		$_REQUEST['id'] = KEY_ID;
		$carboy = getCarboyById();
		$this->assertIsA( $carboy, 'Carboy');
		$this->assertEqual( $carboy->getKey_id(), KEY_ID );
	}
	

	// getCarboyUseCycleById
	public function test_getCarboyUseCycleById_noId() {
		$cycle = getCarboyUseCycleById();
		$this->assertIsA( $cycle, 'ActionError' );
	}

	public function test_getCarboyUseCycleById_passId() {
		$cycle = getCarboyUseCycleById( KEY_ID );
		$this->assertIsA( $cycle, 'CarboyUseCycle' );
		$this->assertEqual( $cycle->getKey_id(), KEY_ID );
	}

	public function test_getCarboyUseCycleById_requestId() {
		$_REQUEST['id'] = KEY_ID;
		$cycle = getCarboyUseCycleById();
		$this->assertIsA( $cycle, 'CarboyUseCycle' );
		$this->assertEqual( $cycle->getKey_id(), KEY_ID );
	}
	

	// getDisposalLotById
	public function test_getDisposalLotById_noId() {
		$lot = getDisposalLotById();
		$this->assertIsA( $lot, 'ActionError' );
	}

	public function test_getDisposalLotById_passId() {
		$lot = getDisposalLotById( KEY_ID );
		$this->assertIsA( $lot, 'DisposalLot' );
		$this->assertEqual( $lot->getKey_id(), KEY_ID );
	}
	
	public function test_getDisposalLotById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$lot = getDisposalLotById();
		$this->assertIsA( $lot, 'DisposalLot' );
		$this->assertEqual( $lot->getKey_id(), KEY_ID );
	}
	

	// getDrumById
	public function test_getDrumById_noId() {
		$drum = getDrumById();
		$this->assertIsA( $drum, 'ActionError' );
	}
	
	public function test_getDrumById_passId() {
		$drum = getDrumById( KEY_ID );
		$this->assertIsA( $drum, 'Drum' );
		$this->assertEqual( $drum->getKey_id(), KEY_ID );
	}
	
	public function test_getDrumById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$drum = getDrumById();
		$this->assertIsA( $drum, 'Drum' );
		$this->assertEqual( $drum->getKey_id(), KEY_ID );
	}
	

	// getParcelByid
	public function test_getParcelById_noId() {
		$parcel = getParcelById();
		$this->assertIsA( $parcel, 'ActionError' );
	}
	
	public function test_getParcelById_passId() {
		$parcel = getParcelById( KEY_ID );
		$this->assertIsA( $parcel, 'Parcel' );
		$this->assertEqual( $parcel->getKey_id(), KEY_ID );
	}
	
	public function test_getParcelById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$parcel = getParcelById();
		$this->assertIsA( $parcel, 'Parcel');
		$this->assertEqual( $parcel->getKey_id(), KEY_ID );
	}
	
	
	// getParcelUseById
	public function test_getParcelUseById_noId() {
		$use = getParcelUseById();
		$this->assertIsA( $use, 'ActionError' );
	}

	public function test_getParcelUseById_passId() {
		$use = getParcelUseById( KEY_ID );
		$this->assertIsA( $use, 'ParcelUse' );
		$this->assertEqual( $use->getKey_id(), KEY_ID );
	}

	public function test_getParcelUseById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$use = getParcelUseById();
		$this->assertIsA( $use, 'ParcelUse' );
		$this->assertEqual( $use->getKey_id(), KEY_ID );
	}
	

	// getPickupById
	public function test_getPickupById_noId() {
		$pickup = getPickupById();
		$this->assertIsA( $pickup, 'ActionError' );
	}

	public function test_getPickupById_passId() {
		$pickup = getPickupById( KEY_ID );
		$this->assertIsA( $pickup, 'Pickup' );
		$this->assertEqual( $pickup->getKey_id(), KEY_ID );
	}

	public function test_getPickupById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$pickup = getPickupById();
		$this->assertIsA( $pickup, 'Pickup' );
		$this->assertEqual( $pickup->getKey_id(), KEY_ID );
	}
	
	
	// getPickupLotById
	public function test_getPickupLotById_noId() {
		$lot = getPickupLotById();
		$this->assertIsA( $lot, 'ActionError' );
	}
	
	public function test_getPickupLotById_passId() {
		$lot = getPickupLotById( KEY_ID );
		$this->assertIsA( $lot, 'PickupLot' );
		$this->assertEqual( $lot->getKey_id(), KEY_ID );
	}
	
	public function test_getPickupLotById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$lot = getPickupLotById();
		$this->assertIsA( $lot, 'PickupLot' );
		$this->assertEqual( $lot->getKey_id(), KEY_ID );
	}
	

	// getPurchaseOrderById
	public function test_getPurchaseOrderById_noId() {
		$order = getPurchaseOrderById();
		$this->assertIsA( $order, 'ActionError' );
	}

	public function test_getPurchaseOrderById_passId() {
		$order = getPurchaseOrderById( KEY_ID );
		$this->assertIsA( $order, 'PurchaseOrder' );
		$this->assertEqual( $order->getKey_id(), KEY_ID );
	}

	public function test_getPurchaseOrderById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$order = getPurchaseOrderById();
		$this->assertIsA( $order, 'PurchaseOrder' );
		$this->assertEqual( $order->getKey_id(), KEY_ID );
	}
	
	
	// getWasteTypeById
	public function test_getWasteTypeById_noId() {
		$type = getWasteTypeById();
		$this->assertIsA( $type, 'ActionError' );
	}
	
	public function test_getWasteTypeById_passId() {
		$type = getWasteTypeById( KEY_ID );
		$this->assertIsA( $type, 'WasteType' );
		$this->assertEqual( $type->getKey_id(), KEY_ID );
	}
	
	public function test_getWasteTypeById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$type = getWasteTypeById();
		$this->assertIsA( $type, 'WasteType' );
		$this->assertEqual( $type->getKey_id(), KEY_ID );
	}
	
	
	// tests for "get by relationship" functions
	
	// getAuthorizationsByPIId
	public function test_getAuthorizationsByPIId_noId() {
		$auths = getAuthorizationsByPIId();
		$this->assertIsA( $auths, 'ActionError' );
	} 
	
	public function test_getAuthorizationsByPIId_passId() {
		$auths = getAuthorizationsByPIId( KEY_ID );
		
		$this->checkArrayAndTypes( $auths, 'Authorization' );
	}
	
	public function test_getAuthorizationsByPIId_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$auths = getAuthorizationsByPIId();
		
		$this->checkArrayAndTypes( $auths, 'Authorization' );
	}
	

	// getPickupLotsByPickupId
	public function test_getPickupLotsByPickupId_noId() {
		$lots = getPickupLotsByPickupId();
		$this->assertIsA( $lots, 'ActionError' );
	}
	
	public function test_getPickupLotsByPickupId_passId() {
		$lots = getPickupLotsByPickupId( KEY_ID );
		$this->checkArrayAndTypes( $lots, 'PickupLot' );
	}
	
	public function test_getPickupLotsByPickupId_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$lots = getPickupLotsByPickupId();
		$this->checkArrayAndTypes( $lots, 'PickupLot' );
	}
	
	
	// getDisposalLotsByPickupLotId
	public function test_getDisposalLotsByPickupLotId_noId() {
		$lots = getDisposalLotsByPickupLotId();
		$this->assertIsA( $lots, 'ActionError' );
	}
	
	public function test_getDisposalLotsByPickupLotId_passId() {
		$lots = getDisposalLotsByPickupLotId( KEY_ID );
		$this->checkArrayAndTypes( $lots, 'DisposalLot' );
	}
	
	public function test_getDisposalLotsByPickupLotId_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$lots = getDisposalLotsByPickupLotId();
		$this->checkArrayAndTypes( $lots, 'DisposalLot' );
	}


	// getDisposalLotsByDrumId
	public function test_getDisposalLotsByDrumId_noId() {
		$lots = getDisposalLotsByDrumId();
		$this->assertIsA( $lots, 'ActionError' );
	}
	
	public function test_getDisposalLotsByDrumId_passId() {
		$lots = getDisposalLotsByDrumId( KEY_ID );
		$this->checkArrayAndTypes( $lots, 'DisposalLot' );
	}
	
	public function test_getDisposalLotsByDrumId_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$lots = getDisposalLotsByDrumId();
		$this->checkArrayAndTypes( $lots, 'DisposalLot' );
	}
	
	
	// getParcelUsesByParcelId
	public function test_getParcelUsesByParcelId_noId() {
		$uses = getParcelUsesByParcelId();
		$this->assertIsA( $uses, 'ActionError' );
	}
	
	public function test_getParcelUsesByParcelId_passId() {
		$uses = getParcelUsesByParcelId( KEY_ID );
		$this->checkArrayAndTypes( $uses, 'ParcelUse' );
	}
	
	public function test_getParcelUsesByParcelId_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$uses = getParcelUsesByParcelId();
		$this->checkArrayAndTypes( $uses, 'ParcelUse' );
	}
	
	
	// getActiveParcelsFromPIById
	public function test_getActiveParcelsFromPIById_noId() {
		$parcels = getActiveParcelsFromPIById();
		$this->assertIsA( $parcels, 'ActionError' );
	}
	
	public function test_getActiveParcelsFromPIById_passId() {
		$parcels = getActiveParcelsFromPIById( KEY_ID );
		$this->checkArrayAndTypes( $parcels, 'Parcel' );
	}
	
	public function test_getActiveParcelsFromPIById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$parcels = getActiveParcelsFromPIById();
		$this->checkArrayAndTypes( $parcels, 'Parcel' );
	}
	
	
	// Tests for "getAll" functions
	
	public function test_getAllCarboys() {
		$carboys = getAllCarboys();
		$this->checkArrayAndTypes( $carboys, 'Carboy' );
	}
	
	public function test_getAllDrums() {
		$drums = getAllDrums();
		$this->checkArrayAndTypes( $drums, 'Drum' );
	}
	
	public function test_getAllIsotopes() {
		$isotopes = getAllIsotopes();
		$this->checkArrayAndTypes( $isotopes, 'Isotope' );
	}
	
	public function test_getAllWasteTypes() {
		$types = getAllWasteTypes();
		$this->checkArrayAndTypes( $types, 'WasteType' );
	}

	
	// UTILITY FUNCTIONS
	
	// confirms that given object is an array and that nested objects are of given type
	public function checkArrayAndTypes($object, $targetType) {
		$this->assertTrue( is_array($object) );
		
		// if array is empty, below foreach loop will not run
		$this->assertFalse( empty($object) );

		foreach( $object as $element ) {
			$this->assertIsA( $element, $targetType );
		}
	}
}
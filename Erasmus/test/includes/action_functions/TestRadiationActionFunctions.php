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
	
	
	// getPickupLotById
	public function test_getPickupLotById_noId() {
		$lot = getPickupLotById();
		$this->assertTrue( $lot instanceof ActionError );
	}
	
	public function test_getPickupLotById_passId() {
		$lot = getPickupLotById( KEY_ID );
		$this->assertTrue( $lot instanceof PickupLot );
		$this->assertEqual( $lot->getKey_id(), KEY_ID );
	}
	
	public function test_getPickupLotById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$lot = getPickupLotById();
		$this->assertTrue( $lot instanceof PickupLot );
		$this->assertEqual( $lot->getKey_id(), KEY_ID );
	}
	

	// getPurchaseOrderById
	public function test_getPurchaseOrderById_noId() {
		$order = getPurchaseOrderById();
		$this->assertTrue( $order instanceof ActionError );
	}

	public function test_getPurchaseOrderById_passId() {
		$order = getPurchaseOrderById( KEY_ID );
		$this->assertTrue( $order instanceof PurchaseOrder );
		$this->assertEqual( $order->getKey_id(), KEY_ID );
	}

	public function test_getPurchaseOrderById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$order = getPurchaseOrderById();
		$this->assertTrue( $order instanceof PurchaseOrder );
		$this->assertEqual( $order->getKey_id(), KEY_ID );
	}
	
	
	// getWasteTypeById
	public function test_getWasteTypeById_noId() {
		$type = getWasteTypeById();
		$this->assertTrue( $type instanceof ActionError );
	}
	
	public function test_getWasteTypeById_passId() {
		$type = getWasteTypeById( KEY_ID );
		$this->assertTrue( $type instanceof WasteType );
		$this->assertEqual( $type->getKey_id(), KEY_ID );
	}
	
	public function test_getWasteTypeById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$type = getWasteTypeById();
		$this->assertTrue( $type instanceof WasteType );
		$this->assertEqual( $type->getKey_id(), KEY_ID );
	}
	
	
	// tests for "get by relationship" functions
	
	// getAuthorizationsByPIId
	public function test_getAuthorizationsByPIId_noId() {
		$auths = getAuthorizationsByPIId();
		$this->assertTrue( $auths instanceof ActionError );
	} 
	
	public function test_getAuthorizationsByPIId_passId() {
		$auths = getAuthorizationsByPIId( KEY_ID );
		
		$this->checkArrayAndTypes( $auths, new Authorization() );
	}
	
	public function test_getAuthorizationsByPIId_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$auths = getAuthorizationsByPIId();
		
		$this->checkArrayAndTypes( $auths, new Authorization() );
	}
	

	// getPickupLotsByPickupId
	public function test_getPickupLotsByPickupId_noId() {
		$lots = getPickupLotsByPickupId();
		$this->assertTrue( $lots instanceof ActionError );
	}
	
	public function test_getPickupLotsByPickupId_passId() {
		$lots = getPickupLotsByPickupId( KEY_ID );
		$this->checkArrayAndTypes( $lots, new PickupLot() );
	}
	
	public function test_getPickupLotsByPickupId_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$lots = getPickupLotsByPickupId();
		$this->checkArrayAndTypes( $lots, new PickupLot() );
	}
	
	
	// getDisposalLotsByPickupLotId
	public function test_getDisposalLotsByPickupLotId_noId() {
		$lots = getDisposalLotsByPickupLotId();
		$this->assertTrue( $lots instanceof ActionError );
	}
	
	public function test_getDisposalLotsByPickupLotId_passId() {
		$lots = getDisposalLotsByPickupLotId( KEY_ID );
		$this->checkArrayAndTypes( $lots, new DisposalLot() );
	}
	
	public function test_getDisposalLotsByPickupLotId_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$lots = getDisposalLotsByPickupLotId();
		$this->checkArrayAndTypes( $lots, new DisposalLot() );
	}


	// getDisposalLotsByDrumId
	public function test_getDisposalLotsByDrumId_noId() {
		$lots = getDisposalLotsByDrumId();
		$this->assertTrue( $lots instanceof ActionError );
	}
	
	public function test_getDisposalLotsByDrumId_passId() {
		$lots = getDisposalLotsByDrumId( KEY_ID );
		$this->checkArrayAndTypes( $lots, new DisposalLot() );
	}
	
	public function test_getDisposalLotsByDrumId_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$lots = getDisposalLotsByDrumId();
		$this->checkArrayAndTypes( $lots, new DisposalLot() );
	}
	
	
	// getParcelUsesByParcelId
	public function test_getParcelUsesByParcelId_noId() {
		$uses = getParcelUsesByParcelId();
		$this->assertTrue( $uses instanceof ActionError );
	}
	
	public function test_getParcelUsesByParcelId_passId() {
		$uses = getParcelUsesByParcelId( KEY_ID );
		$this->checkArrayAndTypes( $uses, new ParcelUse() );
	}
	
	public function test_getParcelUsesByParcelId_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$uses = getParcelUsesByParcelId();
		$this->checkArrayAndTypes( $uses, new ParcelUse() );
	}
	
	
	// getActiveParcelsFromPIById
	public function test_getActiveParcelsFromPIById_noId() {
		$parcels = getActiveParcelsFromPIById();
		$this->assertTrue( $parcels instanceof ActionError );
	}
	
	public function test_getActiveParcelsFromPIById_passId() {
		$parcels = getActiveParcelsFromPIById( KEY_ID );
		$this->checkArrayAndTypes( $parcels, new Parcel() );
	}
	
	public function test_getActiveParcelsFromPIById_requestId() {
		$_REQUEST["id"] = KEY_ID;
		$parcels = getActiveParcelsFromPIById();
		$this->checkArrayAndTypes( $parcels, new Parcel() );
	}
	
	
	// Tests for "getAll" functions
	
	public function test_getAllCarboys() {
		$carboys = getAllCarboys();
		$this->checkArrayAndTypes( $carboys, new Carboy() );
	}
	
	public function test_getAllDrums() {
		$drums = getAllDrums();
		$this->checkArrayAndTypes( $drums, new Drum() );
	}
	
	public function test_getAllIsotopes() {
		$isotopes = getAllIsotopes();
		$this->checkArrayAndTypes( $isotopes, new Isotope() );
	}
	
	public function test_getAllWasteTypes() {
		$types = getAllWasteTypes();
		$this->checkArrayAndTypes( $types, new WasteType() );
	}

	
	// UTILITY FUNCTIONS
	
	// confirms that given object is an array and that nested objects are of given type
	public function checkArrayAndTypes($object, $targetType) {
		$this->assertTrue( is_array($object) );
		
		// if array is empty, below foreach loop will not run
		$this->assertFalse( empty($object) );

		foreach( $object as $element ) {
			$this->assertTrue( $element instanceof $targetType );
		}
	}
}
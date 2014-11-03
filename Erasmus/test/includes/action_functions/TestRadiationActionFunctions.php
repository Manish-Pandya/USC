<?php
/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */

require_once(dirname(__FILE__) . '/../../../src/Autoloader.php');
Logger::configure( dirname(__FILE__) . "/../../../src/includes/conf/log4php-config.php");

// Include action functions to test
require_once(dirname(__FILE__) . '/../../../src/includes/Rad_ActionManager.php');

// Radiation action functions depend on some standard action functions as well
require_once(dirname(__FILE__) . '/../../../src/includes/ActionManager.php');

// Unit double for GenericDao so we don't actually modify database
require_once(dirname(__FILE__) . '/../../../src/includes/dao/GenericDAOSpy.php');


// TODO: check that getById was called with correct arguments

class TestRadiationActionFunctions extends PHPUnit_Framework_TestCase {
	
	private $actionManager;
	
	// Reset $_REQUEST between tests so that tests using $_REQUEST don't affect each other
	function tearDown() {
		foreach( $_REQUEST as $key=>$value ) {
			unset( $_REQUEST[$key] );
		}
	}
	
	function setUp() {
		// create test double for GenericDao
		$daoSpy = new GenericDaoSpy();
		// set up a factory that can inject the spy into ActionManager
		$daoSpyInjector = new DaoFactory($daoSpy);
		
		// give our dao injector to ActionManager to substitute daoSpy for GenericDao
		$this->actionManager = new Rad_ActionManager($daoSpyInjector);
	}
	
	
	/*************************************************************************\
	 *                         Basic Get Tests                               *
	\*************************************************************************/

	
	/* getIsotopeById */

	public function test_getIsotopeById_noId() {
		$isotope = $this->actionManager->getIsotopeById();

		$this->assertInstanceOf( 'ActionError', $isotope );
		$this->assertEquals( 201, $isotope->getStatusCode() );
	}

	public function test_getIsotopeById_passId() {

		$isotope = $this->actionManager->getIsotopeById(1);

		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Isotope', $isotope );
		$this->assertEquals( 1, $isotope->getKey_id() );
	}
	
	public function test_getIsotopeById_requestId() {
		$_REQUEST['id'] = 1;
		$isotope = $this->actionManager->getIsotopeById();

		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Isotope', $isotope );
		$this->assertEquals( 1, $isotope->getKey_id() );
	}


	/* getCarboyById */

	public function test_getCarboyById_noId() {
		$carboy = $this->actionManager->getCarboyById();

		$this->assertInstanceOf( 'ActionError', $carboy );
		$this->assertEquals( 201, $carboy->getStatusCode() );
	}

	public function test_getCarboyById_passId() {
		$carboy = $this->actionManager->getCarboyById(1);

		// make sure same object is returned
		$this->assertInstanceOf( 'Carboy', $carboy );
		$this->assertEquals( 1, $carboy->getKey_id() );
	}

	public function test_getCarboyById_requestId() {
		$_REQUEST['id'] = 1;
		$carboy = $this->actionManager->getCarboyById();

		// check same object returned
		$this->assertInstanceOf( 'Carboy', $carboy );
		$this->assertEquals( 1, $carboy->getKey_id() );
	}

	
	/* getCarboyUseCycleById */

	public function test_getCarboyUseCycleById_noId() {
		$cycle = $this->actionManager->getCarboyUseCycleById();

		$this->assertInstanceOf( 'ActionError', $cycle );
		$this->assertEquals( 201, $cycle->getStatusCode() );
	}

	public function test_getCarboyUseCycleById_passId() {
		$cycle = $this->actionManager->getCarboyUseCycleById( 1 );

		// make sure same object is returned
		$this->assertInstanceOf( 'CarboyUseCycle', $cycle );
		$this->assertEquals( 1, $cycle->getKey_id() );
	}

	public function test_getCarboyUseCycleById_requestId() {
		$_REQUEST['id'] = 1;

		$cycle = $this->actionManager->getCarboyUseCycleById();

		// make sure same object is returned
		$this->assertInstanceOf( 'CarboyUseCycle', $cycle );
		$this->assertEquals( 1, $cycle->getKey_id() );
	}
	

	/* getDisposalLotById */

	public function test_getDisposalLotById_noId() {
		$lot = $this->actionManager->getDisposalLotById();

		$this->assertInstanceOf( 'ActionError', $lot );
		$this->assertEquals( 201, $lot->getStatusCode() );
	}

	public function test_getDisposalLotById_passId() {
		$lot = $this->actionManager->getDisposalLotById( 1 );

		// check that specific object returned correctly
		$this->assertInstanceOf( 'DisposalLot', $lot );
		$this->assertEquals( 1, $lot->getKey_id() );
	}
	
	public function test_getDisposalLotById_requestId() {
		$_REQUEST["id"] = 1;
		$lot = $this->actionManager->getDisposalLotById();

		// check that specific object returned correctly
		$this->assertInstanceOf( 'DisposalLot', $lot );
		$this->assertEquals( 1, $lot->getKey_id() );
	}
	

	/* getDrumById */

	public function test_getDrumById_noId() {
		$drum = $this->actionManager->getDrumById();

		$this->assertInstanceOf( 'ActionError', $drum );
		$this->assertEquals( 201, $drum->getStatusCode() );
	}
	
	public function test_getDrumById_passId() {
		$drum = $this->actionManager->getDrumById( 1 );
		 
		// check that specific object returned correctly
		$this->assertInstanceOf( 'Drum', $drum );
		$this->assertEquals( 1, $drum->getKey_id() );
	}
	
	public function test_getDrumById_requestId() {
		$_REQUEST["id"] = 1;
		$drum = $this->actionManager->getDrumById();

		// check that specific object returned correctly
		$this->assertInstanceOf( 'Drum', $drum );
		$this->assertEquals( 1, $drum->getKey_id() );
	}
	

	/* getParcelByid */

	public function test_getParcelById_noId() {
		$parcel = $this->actionManager->getParcelById();

		$this->assertInstanceOf( 'ActionError', $parcel );
		$this->assertEquals(201, $parcel->getStatusCode() );
	}
	
	public function test_getParcelById_passId() {
		$parcel = $this->actionManager->getParcelById( 1 );
		
		// check that specific object returned correctly
		$this->assertInstanceOf( 'Parcel', $parcel );
		$this->assertEquals( 1, $parcel->getKey_Id() );
	}
	
	public function test_getParcelById_requestId() {
		$_REQUEST["id"] = 1;
		$parcel = $this->actionManager->getParcelById();

		// check that specific object returned correctly
		$this->assertInstanceOf( 'Parcel', $parcel );
		$this->assertEquals( 1, $parcel->getKey_id() );
	}
	
	
	/* getParcelUseById */

	public function test_getParcelUseById_noId() {
		$use = $this->actionManager->getParcelUseById();

		// should return actionError when no id provided
		$this->assertInstanceOf( 'ActionError', $use );
		$this->assertEquals( 201, $use->getStatusCode() );
	}

	public function test_getParcelUseById_passId() {
		$use = $this->actionManager->getParcelUseById( 1 );
		
		// check that specific object was returned correctly
		$this->assertInstanceOf( 'ParcelUse', $use );
		$this->assertEquals( 1, $use->getKey_id() );
	}

	public function test_getParcelUseById_requestId() {
		$_REQUEST["id"] = 1;
		$use = $this->actionManager->getParcelUseById();

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'ParcelUse', $use );
		$this->assertEquals( 1, $use->getKey_id() );
	}
	

	/* getPickupById */

	public function test_getPickupById_noId() {
		$pickup = $this->actionManager->getPickupById();
		
		// should return actionError when no id provided
		$this->assertInstanceOf( 'ActionError', $pickup );
		$this->assertEquals( 201, $pickup->getStatusCode() );
	}

	public function test_getPickupById_passId() {
		$pickup = $this->actionManager->getPickupById( 1 );

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'Pickup', $pickup );
		$this->assertEquals( 1, $pickup->getKey_id() );
	}

	public function test_getPickupById_requestId() {
		$_REQUEST["id"] = 1;
		$pickup = $this->actionManager->getPickupById();

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'Pickup', $pickup );
		$this->assertEquals( 1, $pickup->getKey_id() );
	}
	
	
	/* getPickupLotById */

	public function test_getPickupLotById_noId() {
		$lot = $this->actionManager->getPickupLotById();
		
		// should return actionError when no id is provided
		$this->assertInstanceOf( 'ActionError', $lot );
		$this->assertEquals( 201, $lot->getStatusCode() );
	}
	
	public function test_getPickupLotById_passId() {
		$lot = $this->actionManager->getPickupLotById( 1 );

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'PickupLot', $lot );
		$this->assertEquals( 1, $lot->getKey_id() );
	}
	
	public function test_getPickupLotById_requestId() {
		$_REQUEST["id"] = 1;
		$lot = $this->actionManager->getPickupLotById();
		
		// check that specific object was returned correctly
		$this->assertInstanceOf( 'PickupLot', $lot );
		$this->assertEquals( 1, $lot->getKey_id() );
	}
	

	/* getPurchaseOrderById */

	public function test_getPurchaseOrderById_noId() {
		$order = $this->actionManager->getPurchaseOrderById();
		
		// should return actionError when no id is provided
		$this->assertInstanceOf( 'ActionError', $order );
		$this->assertEquals( 201, $order->getStatusCode() );
	}

	public function test_getPurchaseOrderById_passId() {
		$order = $this->actionManager->getPurchaseOrderById( 1 );

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'PurchaseOrder', $order );
		$this->assertEquals( 1, $order->getKey_id() );
	}

	public function test_getPurchaseOrderById_requestId() {
		$_REQUEST["id"] = 1;
		$order = $this->actionManager->getPurchaseOrderById();

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'PurchaseOrder', $order );
		$this->assertEquals( 1, $order->getKey_id() );
	}
	
	
	/* getWasteTypeById */

	public function test_getWasteTypeById_noId() {
		$type = $this->actionManager->getWasteTypeById();

		// should return actionError when no id is provided
		$this->assertInstanceOf( 'ActionError', $type );
		$this->assertEquals( 201, $type->getStatusCode() );
	}
	
	public function test_getWasteTypeById_passId() {
		$type = $this->actionManager->getWasteTypeById( 1 );

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'WasteType', $type );
		$this->assertEquals( 1, $type->getKey_id() );
	}
	
	public function test_getWasteTypeById_requestId() {
		$_REQUEST["id"] = 1;
		$type = $this->actionManager->getWasteTypeById();

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'WasteType', $type );
		$this->assertEquals( 1, $type->getKey_id() );
	}
	

	/*************************************************************************\
	 *                       Get By Relationship Tests                       *
	\*************************************************************************/
	
	/* NOTE:
	 * These pose a problem because whatever the fake dao returns need to then
	 * return a mock as well. TODO meditate on it, then come up with a solution.
	 * In the meantime, commented out so as to not trigger an error when phpunit is run
	 */

	/* getAuthorizationsByPIId */

// 	public function test_getAuthorizationsByPIId_noId() {
		
// 		$auths = $this->actionManager->getAuthorizationsByPIId();
		
// 		// should return actionError when no id is provided
// 		$this->assertInstanceOf( 'ActionError', $auths );
		
// 		/* If error happened inside action functions due to lack of id paramateter
// 		   (which is what should have happened), ActionError will have error code 201 */
// 		$this->assertEquals(201, $auths->getStatusCode() );
// 	} 
	
// 	public function test_getAuthorizationsByPIId_passId() {
		
// 		$auths = $this->actionManager->getAuthorizationsByPIId( 0 );
		
// 		$this->assertContainsOnlyInstancesOf( "Authorization", $auths );
// 		$this->assertCount( 5, $auths );
// 	}
	
// 	public function test_getAuthorizationsByPIId_requestId() {
		
// 		// create mock that will return array of authorizations when asked
// 		$mock = $this->prepareMockToReturnArray( "PrincipalInvestigator", "getAuthorizations", "Authorization", 5 );
		
// 		// tell Dao to use the created mock
// 		$this->setGetByIdToReturn( $mock );
		
// 		$_REQUEST["id"] = 0;
// 		$auths = getAuthorizationsByPIId();
		
// 		$this->assertContainsOnlyInstancesOf( "Authorization", $auths );
// 		$this->assertCount( 5, $auths );
// 	}

	
// 	/* getPickupLotsByPickupId */

// 	public function test_getPickupLotsByPickupId_noId() {
// 		$lots = getPickupLotsByPickupId();

// 		//should return actionError when no id provided
// 		$this->assertInstanceOf( 'ActionError', $lots );
		
// 		// ActionError should have code 201 if created due to lack of id
// 		$this->assertEquals( 201, $lots->getStatusCode() );
// 	}
	
// 	public function test_getPickupLotsByPickupId_passId() {

// 		// create mock to return array of pickuplots, set Dao to use that mock
// 		$mock = $this->prepareMockToReturnArray( "PickUp", "getPickupLots", "PickupLot", 5 );
// 		$this->setGetByIdToReturn( $mock );

// 		$lots = getPickupLotsByPickupId( 0 );

// 		$this->assertContainsOnlyInstancesOf( 'PickupLot', $lots );
// 		$this->assertCount( 5, $lots );
// 	}
	
// 	public function test_getPickupLotsByPickupId_requestId() {

// 		// create mock to return array of pickuplots, set Dao to use that mock
// 		$mock = $this->prepareMockToReturnArray( "PickUp", "getPickupLots", "PickupLot", 5 );
// 		$this->setGetByIdToReturn( $mock );

// 		$_REQUEST["id"] = 0;
// 		$lots = getPickupLotsByPickupId();

// 		$this->assertContainsOnlyInstancesOf( 'PickupLot', $lots );
// 		$this->assertCount( 5, $lots );
// 	}

	
// 	/* getDisposalLotsByPickupLotId */
	
// 	public function test_getDisposalLotsByPickupLotId_noId() {
// 		$lots = getDisposalLotsByPickupLotId();

// 		// should have returned actionError with error code for missing parameter
// 		$this->assertInstanceOf( 'ActionError', $lots );
// 		$this->assertEquals( 201, $lots->getStatusCode() );

// 	}
	
// 	public function test_getDisposalLotsByPickupLotId_passId() {
		
// 		// create mock to return array of pickuplots, set Dao to use that mock
// 		$mock = $this->prepareMockToReturnArray( "PickupLot", "getDisposalLots", "DisposalLot", 5 );
// 		$this->setGetByIdToReturn( $mock );
		
// 		$lots = getDisposalLotsByPickupLotId( 0 );

// 		$this->assertContainsOnlyInstancesOf( 'DisposalLot', $lots );
// 		$this->assertCount( 5, $lots );
// 	}
	
// 	public function test_getDisposalLotsByPickupLotId_requestId() {

// 		// create mock to return array of pickuplots, set Dao to use that mock
// 		$mock = $this->prepareMockToReturnArray( "PickupLot", "getDisposalLots", "DisposalLot", 5 );
// 		$this->setGetByIdToReturn( $mock );

// 		$_REQUEST["id"] = 0;
// 		$lots = getDisposalLotsByPickupLotId();

// 		$this->assertContainsOnlyInstancesOf( 'DisposalLot', $lots );
// 		$this->assertCount( 5, $lots );
// 	}


// 	/* getDisposalLotsByDrumId */
	
// 	public function test_getDisposalLotsByDrumId_noId() {
// 		$lots = getDisposalLotsByDrumId();

// 		// should have returned ActionError with status code for missing parameter
// 		$this->assertInstanceOf('ActionError', $lots);
// 		$this->assertEquals( 201, $lots->getStatusCode() );
// 	}
	
// 	public function test_getDisposalLotsByDrumId_passId() {
		
// 		// create mock to return array of disposalLots, set Dao to use that mock
// 		$mock = $this->prepareMockToReturnArray( "Drum", "getDisposalLots", "DisposalLot", 5 );
// 		$this->setGetByIdToReturn( $mock );
		
// 		$lots = getDisposalLotsByDrumId( 0 );

// 		$this->assertContainsOnlyInstancesOf( 'DisposalLot', $lots );
// 		$this->assertCount( 5, $lots );
// 	}
	
// 	public function test_getDisposalLotsByDrumId_requestId() {
		
// 		// create mock to return array of disposalLots, set Dao to use that mock
// 		$mock = $this->prepareMockToReturnArray( "Drum", "getDisposalLots", "DisposalLot", 5 );
// 		$this->setGetByIdToReturn( $mock );
		
// 		$_REQUEST["id"] = 0;
// 		$lots = getDisposalLotsByDrumId();

// 		$this->assertContainsOnlyInstancesOf( 'DisposalLot', $lots );
// 		$this->assertCount( 5, $lots );
// 	}
	
	
// 	/* getParcelUsesByParcelId */
	
// 	public function test_getParcelUsesByParcelId_noId() {
// 		$uses = getParcelUsesByParcelId();

// 		// should have returned actionError with status code for missing error
// 		$this->assertInstanceOf( 'ActionError', $uses );
// 		$this->assertEquals( 201, $uses->getStatusCode() );
// 	}
	
// 	public function test_getParcelUsesByParcelId_passId() {
// 		// create mock to return array of ParcelUses, set Dao to use that mock
// 		$mock = $this->prepareMockToReturnArray( "Parcel", "getUses", "ParcelUse", 5 );
// 		$this->setGetByIdToReturn( $mock );

// 		$uses = getParcelUsesByParcelId( 0 );
		
// 		$this->assertContainsOnlyInstancesOf( 'ParcelUse', $uses );
// 		$this->assertCount( 5, $uses );
// 	}
	
// 	public function test_getParcelUsesByParcelId_requestId() {
// 		// create mock to return array of ParcelUses, set Dao to use that mock
// 		$mock = $this->prepareMockToReturnArray( "Parcel", "getUses", "ParcelUse", 5 );
// 		$this->setGetByIdToReturn( $mock );

// 		$_REQUEST["id"] = 0;
// 		$uses = getParcelUsesByParcelId();
		
// 		$this->assertContainsOnlyInstancesOf( 'ParcelUse', $uses );
// 		$this->assertCount( 5, $uses );
// 	}
	
	
// 	/* getActiveParcelsFromPIById */
	
// 	public function test_getActiveParcelsFromPIById_noId() {
// 		$parcels = getActiveParcelsFromPIById();

// 		$this->assertInstanceOf( 'ActionError', $parcels );
// 		$this->assertEquals( 201, $parcels->getStatusCode() );
// 	}
	
// 	public function test_getActiveParcelsFromPIById_passId() {
// 		// create mock to return array of Parcels, set Dao to use that mock
// 		$mock = $this->prepareMockToReturnArray( "PrincipalInvestigator", "getActiveParcels", "Parcel", 5 );
// 		$this->setGetByIdToReturn( $mock );
		
// 		$parcels = getActiveParcelsFromPIById( 0 );

// 		$this->assertContainsOnlyInstancesOf( 'Parcel', $parcels );
// 		$this->assertCount( 5, $parcels );
// 	}
	
// 	public function test_getActiveParcelsFromPIById_requestId() {
//         // create mock to return array of Parcels, set Dao to use that mock
// 		$mock = $this->prepareMockToReturnArray( "PrincipalInvestigator", "getActiveParcels", "Parcel", 5 );
// 		$this->setGetByIdToReturn( $mock );
		
// 		$_REQUEST["id"] = 0;
// 		$parcels = getActiveParcelsFromPIById();

// 		$this->assertContainsOnlyInstancesOf( 'Parcel', $parcels );
// 		$this->assertCount( 5, $parcels );
// 	}
	
	
	/**************************************************************************\
	 *                            GetAll Tests                                *
	\**************************************************************************/
	

	/* getAllCarboys */
	public function test_getAllCarboys() {
		$carboys = $this->actionManager->getAllCarboys();

		$this->assertContainsOnlyInstancesOf( 'Carboy', $carboys );
		$this->assertCount( 5, $carboys );
	}
	
	/* getAllDrums */
	public function test_getAllDrums() {
		$drums = $this->actionManager->getAllDrums();

		$this->assertContainsOnlyInstancesOf( 'Drum', $drums );
		$this->assertCount( 5, $drums );
	}
	
	/* getAllIsotopes */
	public function test_getAllIsotopes() {
		$isotopes = $this->actionManager->getAllIsotopes();

		$this->assertContainsOnlyInstancesOf( 'Isotope', $isotopes );
		$this->assertCount( 5, $isotopes );
	}
	
	/* getAllWasteTypes */
	public function test_getAllWasteTypes() {
		$types = $this->actionManager->getAllWasteTypes();

		$this->assertContainsOnlyInstancesOf( 'WasteType', $types );
		$this->assertCount( 5, $types );
	}
}
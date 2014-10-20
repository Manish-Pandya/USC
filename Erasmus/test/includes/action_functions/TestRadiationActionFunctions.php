<?php
/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */

require_once(dirname(__FILE__) . '/../../../src/Autoloader.php');
Logger::configure( dirname(__FILE__) . "/../../../src/includes/conf/log4php-config.php");

// Include action functions to test
require_once(dirname(__FILE__) . '/../../../src/includes/Rad_action_functions.php');

// Radiation action functions depend on some standard action functions as well
require_once(dirname(__FILE__) . '/../../../src/includes/action_functions.php');


// TODO: check that getById was called with correct arguments

// Note: Tests not yet converted to PHPUnit are commented out

class TestRadiationActionFunctions extends PHPUnit_Framework_TestCase {
	
	// Reset $_REQUEST between tests so that tests using $_REQUEST don't affect each other
	function tearDown() {
		foreach( $_REQUEST as $key=>$value ) {
			unset( $_REQUEST[$key] );
		}
	}
	
	// sets mock for GenericDAO to return specific object when getById is called
	function setGetByIdToReturn($returnObject) {

		$mockDao = $this->getMock( 'GenericDAO' );
		$mockDao->method( 'getById' )->willReturn($returnObject);
		$this->setMockDao( $mockDao );

	}
	
	//WARNING: setGetById and setGetAll will overwrite each other if used in the same test method
	
	// sets mock for GenericDAO to return array of objects of specified type when getAll is called
	function setGetAllToReturn( $itemType, $itemCount = 3 ) {
		
		$itemArray = array_fill( 0, $itemCount, new $itemType );
		
		$mockDao = $this->getMock( 'GenericDao' );
		$mockDao->method( 'getAll' )->willReturn( $itemArray );
		$this->setMockDao( $mockDao );

	}
	
	// returns mock of type $mockType that will return an array of $itemType with
	// length $itemCount when $methodName is called
	function prepareMockToReturnArray( $mockType, $methodName, $itemType, $itemCount = 3 ) {
		// create array filled with type $itemType
		$array = array_fill( 0, $itemCount, new $itemType() );
		
		// create a mock that returns array of itemType when methodName is called
		$mock = $this->getMock( $mockType );
		$mock->method( $methodName )->willReturn( $array );
		
		return $mock;
	}
	
	// sets dao factory to return a new given mock dao
	function setMockDao( $mockDao ) {
		$newFactory = new DaoFactory();
		$newFactory->setModelDao( $mockDao );

		setDaoFactory( $newFactory );
	}
	

	// tests for basic getters
	
	// getIsotopeById
	public function test_getIsotopeById_noId() {
		$this->setGetByIdToReturn(new Isotope);
		
		$isotope = getIsotopeById();

		$this->assertInstanceOf( 'ActionError', $isotope );
	}

	public function test_getIsotopeById_passId() {
		// set mock to return object with specific type and key id
		$returnedIsotope = new Isotope();
		$returnedIsotope->setKey_id(1);
		$this->setGetByIdToReturn($returnedIsotope);

		$isotope = getIsotopeById(1);

		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Isotope', $isotope );
		$this->assertEquals( 1, $isotope->getKey_id() );
	}
	
	
	public function test_getIsotopeById_requestId() {
		// set mock to return object with specific type and key_id
		$returnedIsotope = new Isotope();
		$returnedIsotope->setKey_id(1);
		$this->setGetByIdToReturn($returnedIsotope);
		
		$_REQUEST['id'] = 1;
		$isotope = getIsotopeById();

		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Isotope', $isotope );
		$this->assertEquals( 1, $isotope->getKey_id() );
	}

	// getCarboyById
	public function test_getCarboyById_noId() {
		$this->setGetByIdToReturn(new Carboy());

		$carboy = getCarboyById();
		$this->assertInstanceOf( 'ActionError', $carboy );
	}

	public function test_getCarboyById_passId() {
		// set mock to return object with specific type and key id
		$objToReturn = new Carboy();
		$objToReturn->setKey_id(1);
		$this->setGetByIdToReturn( $objToReturn );
		
		$carboy = getCarboyById(1);

		// make sure same object is returned
		$this->assertInstanceOf( 'Carboy', $carboy );
		$this->assertEquals( 1, $carboy->getKey_id() );
	}

	public function test_getCarboyById_requestId() {
		// set mock to return object with specific type and key id
		$objToReturn = new Carboy();
		$objToReturn->setKey_id(1);
		$this->setGetByIdToReturn( $objToReturn );

		$_REQUEST['id'] = 1;
		$carboy = getCarboyById();

		// check same object returned
		$this->assertInstanceOf( 'Carboy', $carboy );
		$this->assertEquals( 1, $carboy->getKey_id() );
	}

	// getCarboyUseCycleById
	public function test_getCarboyUseCycleById_noId() {
		$this->setGetByIdToReturn( new CarboyUseCycle() );

		$cycle = getCarboyUseCycleById();

		$this->assertInstanceOf( 'ActionError', $cycle );
	}

	public function test_getCarboyUseCycleById_passId() {
		// set mock to return object with specific type and key id
		$objToReturn = new CarboyUseCycle();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );
		
		$cycle = getCarboyUseCycleById( 1 );

		// make sure same object is returned
		$this->assertInstanceOf( 'CarboyUseCycle', $cycle );
		$this->assertEquals( 1, $cycle->getKey_id() );
	}

	public function test_getCarboyUseCycleById_requestId() {
		// set mock to return object with specific type and key id
		$objToReturn = new CarboyUseCycle();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );

		$_REQUEST['id'] = 1;

		$cycle = getCarboyUseCycleById();

		// make sure same object is returned
		$this->assertInstanceOf( 'CarboyUseCycle', $cycle );
		$this->assertEquals( 1, $cycle->getKey_id() );
	}
	

	// getDisposalLotById
	public function test_getDisposalLotById_noId() {
		$this->setGetByIdToReturn( new DisposalLot() );

		$lot = getDisposalLotById();

		$this->assertInstanceOf( 'ActionError', $lot );
	}

	public function test_getDisposalLotById_passId() {
		// set mock to return object with specific type and key id
		$objToReturn = new DisposalLot();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );

		$lot = getDisposalLotById( 1 );

		// check that specific object returned correctly
		$this->assertInstanceOf( 'DisposalLot', $lot );
		$this->assertEquals( 1, $lot->getKey_id() );
	}
	
	public function test_getDisposalLotById_requestId() {
		// set mock to return object with specific type and key id
		$objToReturn = new DisposalLot();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );

		$_REQUEST["id"] = 1;
		$lot = getDisposalLotById();

		// check that specific object returned correctly
		$this->assertInstanceOf( 'DisposalLot', $lot );
		$this->assertEquals( 1, $lot->getKey_id() );
	}
	

	// getDrumById
	public function test_getDrumById_noId() {
		$this->setGetByIdToReturn( new Drum() );

		$drum = getDrumById();

		$this->assertInstanceOf( 'ActionError', $drum );
	}
	
	public function test_getDrumById_passId() {
		// set mock to return object with specific type and key id
		$objToReturn = new Drum();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn($objToReturn);

		$drum = getDrumById( 1 );
		 
		// check that specific object returned correctly
		$this->assertInstanceOf( 'Drum', $drum );
		$this->assertEquals( 1, $drum->getKey_id() );
	}
	
	public function test_getDrumById_requestId() {
		// set mock to return object with specific type and key id
		$objToReturn = new Drum();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn($objToReturn);

		$_REQUEST["id"] = 1;
		$drum = getDrumById();

		// check that specific object returned correctly
		$this->assertInstanceOf( 'Drum', $drum );
		$this->assertEquals( 1, $drum->getKey_id() );
	}
	
	// getParcelByid
	public function test_getParcelById_noId() {
		$this->setGetByIdToReturn( new Parcel() );

		$parcel = getParcelById();

		$this->assertInstanceOf( 'ActionError', $parcel );
	}
	
	public function test_getParcelById_passId() {
		// set mock to return object with specific type and key id
		$objToReturn = new Parcel();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn($objToReturn);

		$parcel = getParcelById( 1 );
		
		// check that specific object returned correctly
		$this->assertInstanceOf( 'Parcel', $parcel );
		$this->assertEquals( 1, $parcel->getKey_Id() );
	}
	
	public function test_getParcelById_requestId() {
		// set mock to return object with specific type and key id
		$objToReturn = new Parcel();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn($objToReturn);
		
		$_REQUEST["id"] = 1;
		$parcel = getParcelById();

		// check that specific object returned correctly
		$this->assertInstanceOf( 'Parcel', $parcel );
		$this->assertEquals( 1, $parcel->getKey_id() );
	}
	
	
	// getParcelUseById
	public function test_getParcelUseById_noId() {
		$this->setGetByIdToReturn( new ParcelUse() );

		$use = getParcelUseById();

		// should return actionError when no id provided
		$this->assertInstanceOf( 'ActionError', $use );
	}

	public function test_getParcelUseById_passId() {
		// set mock to return object with specific type and key id
		$objToReturn = new ParcelUse();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );
		
		$use = getParcelUseById( 1 );
		
		// check that specific object was returned correctly
		$this->assertInstanceOf( 'ParcelUse', $use );
		$this->assertEquals( 1, $use->getKey_id() );
	}

	public function test_getParcelUseById_requestId() {
		// set mock to return object with specific type and key id
		$objToReturn = new ParcelUse();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );

		$_REQUEST["id"] = 1;
		$use = getParcelUseById();

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'ParcelUse', $use );
		$this->assertEquals( 1, $use->getKey_id() );
	}
	

	// getPickupById
	public function test_getPickupById_noId() {
		$this->setGetByIdToReturn( new Pickup() );

		$pickup = getPickupById();
		
		// should return actionError when no id provided
		$this->assertInstanceOf( 'ActionError', $pickup );
	}

	public function test_getPickupById_passId() {
		// set mock to return object with specific type and key id
		$objToReturn = new Pickup();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );

		$pickup = getPickupById( 1 );

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'Pickup', $pickup );
		$this->assertEquals( 1, $pickup->getKey_id() );
	}

	public function test_getPickupById_requestId() {
		// set mock to return object with specific type and key id
		$objToReturn = new Pickup();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );

		$_REQUEST["id"] = 1;
		$pickup = getPickupById();

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'Pickup', $pickup );
		$this->assertEquals( 1, $pickup->getKey_id() );
	}
	
	
	// getPickupLotById
	public function test_getPickupLotById_noId() {
		$this->setGetByIdToReturn( new PickupLot() );

		$lot = getPickupLotById();
		
		// should return actionError when no id is provided
		$this->assertInstanceOf( 'ActionError', $lot );
	}
	
	public function test_getPickupLotById_passId() {
		// set mock to return object with specific type and key id
		$objToReturn = new PickupLot();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );
		
		$lot = getPickupLotById( 1 );

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'PickupLot', $lot );
		$this->assertEquals( 1, $lot->getKey_id() );
	}
	
	public function test_getPickupLotById_requestId() {
		// set mock to return object with specific type and key id
		$objToReturn = new PickupLot();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );

		$_REQUEST["id"] = 1;
		$lot = getPickupLotById();
		
		// check that specific object was returned correctly
		$this->assertInstanceOf( 'PickupLot', $lot );
		$this->assertEquals( 1, $lot->getKey_id() );
	}
	

	// getPurchaseOrderById
	public function test_getPurchaseOrderById_noId() {
		$this->setGetByIdToReturn( new PurchaseOrder() );

		$order = getPurchaseOrderById();
		
		// should return actionError when no id is provided
		$this->assertInstanceOf( 'ActionError', $order );
	}

	public function test_getPurchaseOrderById_passId() {
		// set mock to return object with specific type and key id
		$objToReturn = new PurchaseOrder();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );

		$order = getPurchaseOrderById( 1 );

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'PurchaseOrder', $order );
		$this->assertEquals( 1, $order->getKey_id() );
	}

	public function test_getPurchaseOrderById_requestId() {
		// set mock to return object with specific type and key id
		$objToReturn = new PurchaseOrder();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );

		$_REQUEST["id"] = 1;
		$order = getPurchaseOrderById();

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'PurchaseOrder', $order );
		$this->assertEquals( 1, $order->getKey_id() );
	}
	
	
	// getWasteTypeById
	public function test_getWasteTypeById_noId() {
		$this->setGetByIdToReturn( new WasteType() );

		$type = getWasteTypeById();

		// should return actionError when no id is provided
		$this->assertInstanceOf( 'ActionError', $type );
	}
	
	public function test_getWasteTypeById_passId() {
		// set mock to return object with specific type and key id
		$objToReturn = new WasteType();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );

		$type = getWasteTypeById( 1 );

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'WasteType', $type );
		$this->assertEquals( 1, $type->getKey_id() );
	}
	
	public function test_getWasteTypeById_requestId() {
		// set mock to return object with specific type and key id
		$objToReturn = new WasteType();
		$objToReturn->setKey_id( 1 );
		$this->setGetByIdToReturn( $objToReturn );

		$_REQUEST["id"] = 1;
		$type = getWasteTypeById();

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'WasteType', $type );
		$this->assertEquals( 1, $type->getKey_id() );
	}
	
	
	// tests for "get by relationship" functions
	
	// getAuthorizationsByPIId
	public function test_getAuthorizationsByPIId_noId() {
		
		$auths = getAuthorizationsByPIId();
		
		// should return actionError when no id is provided
		$this->assertInstanceOf( 'ActionError', $auths );
		
		/* If error happened inside action functions due to lack of id paramateter
		   (which is what should have happened), ActionError will have error code 201 */
		$this->assertEquals(201, $auths->getStatusCode() );
	} 
	
	public function test_getAuthorizationsByPIId_passId() {

		// create mock that will return array of authorizations when asked
		$mock = $this->prepareMockToReturnArray( "PrincipalInvestigator", "getAuthorizations", "Authorization", 5 );
		
		// tell Dao to use the created mock
		$this->setGetByIdToReturn( $mock );
		
		$auths = getAuthorizationsByPIId( 0 );
		
		$this->assertContainsOnlyInstancesOf( "Authorization", $auths );
		$this->assertCount( 5, $auths );
	}
	
	public function test_getAuthorizationsByPIId_requestId() {
		
		// create mock that will return array of authorizations when asked
		$mock = $this->prepareMockToReturnArray( "PrincipalInvestigator", "getAuthorizations", "Authorization", 5 );
		
		// tell Dao to use the created mock
		$this->setGetByIdToReturn( $mock );
		
		$_REQUEST["id"] = 0;
		$auths = getAuthorizationsByPIId();
		
		$this->assertContainsOnlyInstancesOf( "Authorization", $auths );
		$this->assertCount( 5, $auths );
	}

	
	// getPickupLotsByPickupId
	public function test_getPickupLotsByPickupId_noId() {
		$lots = getPickupLotsByPickupId();

		//should return actionError when no id provided
		$this->assertInstanceOf( 'ActionError', $lots );
		
		// ActionError should have code 201 if created due to lack of id
		$this->assertEquals( 201, $lots->getStatusCode() );
	}
	
	public function test_getPickupLotsByPickupId_passId() {

		// create mock to return array of pickuplots, set Dao to use that mock
		$mock = $this->prepareMockToReturnArray( "PickUp", "getPickupLots", "PickupLot", 5 );
		$this->setGetByIdToReturn( $mock );

		$lots = getPickupLotsByPickupId( 0 );

		$this->assertContainsOnlyInstancesOf( 'PickupLot', $lots );
		$this->assertCount( 5, $lots );
	}
	
	public function test_getPickupLotsByPickupId_requestId() {

		// create mock to return array of pickuplots, set Dao to use that mock
		$mock = $this->prepareMockToReturnArray( "PickUp", "getPickupLots", "PickupLot", 5 );
		$this->setGetByIdToReturn( $mock );

		$_REQUEST["id"] = 0;
		$lots = getPickupLotsByPickupId();

		$this->assertContainsOnlyInstancesOf( 'PickupLot', $lots );
		$this->assertCount( 5, $lots );
	}

	
	// getDisposalLotsByPickupLotId
	public function test_getDisposalLotsByPickupLotId_noId() {
		$lots = getDisposalLotsByPickupLotId();

		// should have returned actionError with error code for missing parameter
		$this->assertInstanceOf( 'ActionError', $lots );
		$this->assertEquals( 201, $lots->getStatusCode() );

	}
	
	public function test_getDisposalLotsByPickupLotId_passId() {
		
		// create mock to return array of pickuplots, set Dao to use that mock
		$mock = $this->prepareMockToReturnArray( "PickupLot", "getDisposalLots", "DisposalLot", 5 );
		$this->setGetByIdToReturn( $mock );
		
		$lots = getDisposalLotsByPickupLotId( 0 );

		$this->assertContainsOnlyInstancesOf( 'DisposalLot', $lots );
		$this->assertCount( 5, $lots );
	}
	
	public function test_getDisposalLotsByPickupLotId_requestId() {

		// create mock to return array of pickuplots, set Dao to use that mock
		$mock = $this->prepareMockToReturnArray( "PickupLot", "getDisposalLots", "DisposalLot", 5 );
		$this->setGetByIdToReturn( $mock );

		$_REQUEST["id"] = 0;
		$lots = getDisposalLotsByPickupLotId();

		$this->assertContainsOnlyInstancesOf( 'DisposalLot', $lots );
		$this->assertCount( 5, $lots );
	}


	// getDisposalLotsByDrumId
	public function test_getDisposalLotsByDrumId_noId() {
		$lots = getDisposalLotsByDrumId();

		// should have returned ActionError with status code for missing parameter
		$this->assertInstanceOf('ActionError', $lots);
		$this->assertEquals( 201, $lots->getStatusCode() );
	}
	
	public function test_getDisposalLotsByDrumId_passId() {
		
		// create mock to return array of disposalLots, set Dao to use that mock
		$mock = $this->prepareMockToReturnArray( "Drum", "getDisposalLots", "DisposalLot", 5 );
		$this->setGetByIdToReturn( $mock );
		
		$lots = getDisposalLotsByDrumId( 0 );

		$this->assertContainsOnlyInstancesOf( 'DisposalLot', $lots );
		$this->assertCount( 5, $lots );
	}
	
	public function test_getDisposalLotsByDrumId_requestId() {
		
		// create mock to return array of disposalLots, set Dao to use that mock
		$mock = $this->prepareMockToReturnArray( "Drum", "getDisposalLots", "DisposalLot", 5 );
		$this->setGetByIdToReturn( $mock );
		
		$_REQUEST["id"] = 0;
		$lots = getDisposalLotsByDrumId();

		$this->assertContainsOnlyInstancesOf( 'DisposalLot', $lots );
		$this->assertCount( 5, $lots );
	}
	
	
	// getParcelUsesByParcelId
	public function test_getParcelUsesByParcelId_noId() {
		$uses = getParcelUsesByParcelId();

		// should have returned actionError with status code for missing error
		$this->assertInstanceOf( 'ActionError', $uses );
		$this->assertEquals( 201, $uses->getStatusCode() );
	}
	
	public function test_getParcelUsesByParcelId_passId() {
		// create mock to return array of ParcelUses, set Dao to use that mock
		$mock = $this->prepareMockToReturnArray( "Parcel", "getUses", "ParcelUse", 5 );
		$this->setGetByIdToReturn( $mock );

		$uses = getParcelUsesByParcelId( 0 );
		
		$this->assertContainsOnlyInstancesOf( 'ParcelUse', $uses );
		$this->assertCount( 5, $uses );
	}
	
	public function test_getParcelUsesByParcelId_requestId() {
		// create mock to return array of ParcelUses, set Dao to use that mock
		$mock = $this->prepareMockToReturnArray( "Parcel", "getUses", "ParcelUse", 5 );
		$this->setGetByIdToReturn( $mock );

		$_REQUEST["id"] = 0;
		$uses = getParcelUsesByParcelId();
		
		$this->assertContainsOnlyInstancesOf( 'ParcelUse', $uses );
		$this->assertCount( 5, $uses );
	}
	
	
	// getActiveParcelsFromPIById
	public function test_getActiveParcelsFromPIById_noId() {
		$parcels = getActiveParcelsFromPIById();

		$this->assertInstanceOf( 'ActionError', $parcels );
		$this->assertEquals( 201, $parcels->getStatusCode() );
	}
	
	public function test_getActiveParcelsFromPIById_passId() {
		// create mock to return array of Parcels, set Dao to use that mock
		$mock = $this->prepareMockToReturnArray( "PrincipalInvestigator", "getActiveParcels", "Parcel", 5 );
		$this->setGetByIdToReturn( $mock );
		
		$parcels = getActiveParcelsFromPIById( 0 );

		$this->assertContainsOnlyInstancesOf( 'Parcel', $parcels );
		$this->assertCount( 5, $parcels );
	}
	
	public function test_getActiveParcelsFromPIById_requestId() {
        // create mock to return array of Parcels, set Dao to use that mock
		$mock = $this->prepareMockToReturnArray( "PrincipalInvestigator", "getActiveParcels", "Parcel", 5 );
		$this->setGetByIdToReturn( $mock );
		
		$_REQUEST["id"] = 0;
		$parcels = getActiveParcelsFromPIById();

		$this->assertContainsOnlyInstancesOf( 'Parcel', $parcels );
		$this->assertCount( 5, $parcels );
	}
	
	
	// Tests for "getAll" functions
	
	public function test_getAllCarboys() {
		$this->setGetAllToReturn( 'Carboy', 5 );

		$carboys = getAllCarboys();

		$this->assertContainsOnlyInstancesOf( 'Carboy', $carboys );
		$this->assertCount( 5, $carboys );
	}
	
	public function test_getAllDrums() {
		$this->setGetAllToReturn( 'Drum', 5 );
		
		$drums = getAllDrums();

		$this->assertContainsOnlyInstancesOf( 'Drum', $drums );
		$this->assertCount( 5, $drums );
	}
	
	public function test_getAllIsotopes() {
		$this->setGetAllToReturn( 'Isotope', 5 );

		$isotopes = getAllIsotopes();

		$this->assertContainsOnlyInstancesOf( 'Isotope', $isotopes );
		$this->assertCount( 5, $isotopes );
	}
	
	/*
	public function test_getAllWasteTypes() {
		$types = getAllWasteTypes();
		$this->checkArrayAndTypes( $types, 'WasteType' );
	}
	*/
}
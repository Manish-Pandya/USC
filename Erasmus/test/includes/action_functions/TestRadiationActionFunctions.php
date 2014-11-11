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
// TODO: put internal methods and fields in ActionManager test class - the only reason
// they're here is that this file was created first.

class TestRadiationActionFunctions extends PHPUnit_Framework_TestCase {
	
	public $actionManager;
	private $daoSpy;
	
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
		
		$this->daoSpy = $daoSpy;
		
		// give our dao injector to ActionManager to substitute daoSpy for GenericDao
		$this->actionManager = new Rad_ActionManager($daoSpyInjector);
		
		// set actionManager to read from $_REQUEST['testInput'] instead of JsonManager
		$this->actionManager->setTestMode(true);
	}
	
	function setGetByIdToReturn($objToReturn) {
		// create test double for GenericDao
		$daoSpy = new GenericDAOSpy();
		
		// override $daoSpy's getById method to return specific obj
		$daoSpy->overrideMethod('getById', $objToReturn);
		
		// new daoFactory will provide actionManager the modified GenericDaoSpy
		$this->actionManager->setDaoFactory(new DaoFactory($daoSpy));
	}
	
	// returns mock of type $mockType that will return an array of $itemType with
	// length $itemCount when $methodName is called
	function prepareMockToReturnArray( $mockType, $methodName, $itemType, $itemCount = 3 ) {
		// create array filled with type $itemType
		$array = array_fill( 0, $itemCount, new $itemType() );
	
		$mock = $this->prepareMockToReturn( $mockType, $methodName, $array );

		return $mock;
	}

	function prepareMockToReturn( $mockType, $methodName, $objToReturn ) {
		$mock = $this->getMock( $mockType );
		$mock->method( $methodName )->willReturn( $objToReturn );

		return $mock;
	}

	function getDaoSpy() {
		return $this->daoSpy;
	}

	
	/*************************************************************************\
	 *                         Basic Get Tests                               *
	\*************************************************************************/

	
	/* getIsotopeById */

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getIsotopeById_noId() {
		$isotope = $this->actionManager->getIsotopeById();

		$this->assertInstanceOf( 'ActionError', $isotope );
		$this->assertEquals( 201, $isotope->getStatusCode() );
	}

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getIsotopeById_passId() {

		$isotope = $this->actionManager->getIsotopeById(1);

		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Isotope', $isotope );
		$this->assertEquals( 1, $isotope->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getIsotopeById_requestId() {
		$_REQUEST['id'] = 1;
		$isotope = $this->actionManager->getIsotopeById();

		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Isotope', $isotope );
		$this->assertEquals( 1, $isotope->getKey_id() );
	}


	/* getCarboyById */

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getCarboyById_noId() {
		$carboy = $this->actionManager->getCarboyById();

		$this->assertInstanceOf( 'ActionError', $carboy );
		$this->assertEquals( 201, $carboy->getStatusCode() );
	}

	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getCarboyById_passId() {
		$carboy = $this->actionManager->getCarboyById(1);

		// make sure same object is returned
		$this->assertInstanceOf( 'Carboy', $carboy );
		$this->assertEquals( 1, $carboy->getKey_id() );
	}

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getCarboyById_requestId() {
		$_REQUEST['id'] = 1;
		$carboy = $this->actionManager->getCarboyById();

		// check same object returned
		$this->assertInstanceOf( 'Carboy', $carboy );
		$this->assertEquals( 1, $carboy->getKey_id() );
	}

	
	/* getCarboyUseCycleById */

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getCarboyUseCycleById_noId() {
		$cycle = $this->actionManager->getCarboyUseCycleById();

		$this->assertInstanceOf( 'ActionError', $cycle );
		$this->assertEquals( 201, $cycle->getStatusCode() );
	}

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getCarboyUseCycleById_passId() {
		$cycle = $this->actionManager->getCarboyUseCycleById( 1 );

		// make sure same object is returned
		$this->assertInstanceOf( 'CarboyUseCycle', $cycle );
		$this->assertEquals( 1, $cycle->getKey_id() );
	}

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getCarboyUseCycleById_requestId() {
		$_REQUEST['id'] = 1;

		$cycle = $this->actionManager->getCarboyUseCycleById();

		// make sure same object is returned
		$this->assertInstanceOf( 'CarboyUseCycle', $cycle );
		$this->assertEquals( 1, $cycle->getKey_id() );
	}
	

	/* getDisposalLotById */

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDisposalLotById_noId() {
		$lot = $this->actionManager->getDisposalLotById();

		$this->assertInstanceOf( 'ActionError', $lot );
		$this->assertEquals( 201, $lot->getStatusCode() );
	}

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDisposalLotById_passId() {
		$lot = $this->actionManager->getDisposalLotById( 1 );

		// check that specific object returned correctly
		$this->assertInstanceOf( 'DisposalLot', $lot );
		$this->assertEquals( 1, $lot->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDisposalLotById_requestId() {
		$_REQUEST["id"] = 1;
		$lot = $this->actionManager->getDisposalLotById();

		// check that specific object returned correctly
		$this->assertInstanceOf( 'DisposalLot', $lot );
		$this->assertEquals( 1, $lot->getKey_id() );
	}
	

	/* getDrumById */

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDrumById_noId() {
		$drum = $this->actionManager->getDrumById();

		$this->assertInstanceOf( 'ActionError', $drum );
		$this->assertEquals( 201, $drum->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDrumById_passId() {
		$drum = $this->actionManager->getDrumById( 1 );
		 
		// check that specific object returned correctly
		$this->assertInstanceOf( 'Drum', $drum );
		$this->assertEquals( 1, $drum->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDrumById_requestId() {
		$_REQUEST["id"] = 1;
		$drum = $this->actionManager->getDrumById();

		// check that specific object returned correctly
		$this->assertInstanceOf( 'Drum', $drum );
		$this->assertEquals( 1, $drum->getKey_id() );
	}
	

	/* getParcelByid */

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getParcelById_noId() {
		$parcel = $this->actionManager->getParcelById();

		$this->assertInstanceOf( 'ActionError', $parcel );
		$this->assertEquals(201, $parcel->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getParcelById_passId() {
		$parcel = $this->actionManager->getParcelById( 1 );
		
		// check that specific object returned correctly
		$this->assertInstanceOf( 'Parcel', $parcel );
		$this->assertEquals( 1, $parcel->getKey_Id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getParcelById_requestId() {
		$_REQUEST["id"] = 1;
		$parcel = $this->actionManager->getParcelById();

		// check that specific object returned correctly
		$this->assertInstanceOf( 'Parcel', $parcel );
		$this->assertEquals( 1, $parcel->getKey_id() );
	}
	
	
	/* getParcelUseById */

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getParcelUseById_noId() {
		$use = $this->actionManager->getParcelUseById();

		// should return actionError when no id provided
		$this->assertInstanceOf( 'ActionError', $use );
		$this->assertEquals( 201, $use->getStatusCode() );
	}

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getParcelUseById_passId() {
		$use = $this->actionManager->getParcelUseById( 1 );
		
		// check that specific object was returned correctly
		$this->assertInstanceOf( 'ParcelUse', $use );
		$this->assertEquals( 1, $use->getKey_id() );
	}

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getParcelUseById_requestId() {
		$_REQUEST["id"] = 1;
		$use = $this->actionManager->getParcelUseById();

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'ParcelUse', $use );
		$this->assertEquals( 1, $use->getKey_id() );
	}
	

	/* getPickupById */

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getPickupById_noId() {
		$pickup = $this->actionManager->getPickupById();
		
		// should return actionError when no id provided
		$this->assertInstanceOf( 'ActionError', $pickup );
		$this->assertEquals( 201, $pickup->getStatusCode() );
	}

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getPickupById_passId() {
		$pickup = $this->actionManager->getPickupById( 1 );

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'Pickup', $pickup );
		$this->assertEquals( 1, $pickup->getKey_id() );
	}

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getPickupById_requestId() {
		$_REQUEST["id"] = 1;
		$pickup = $this->actionManager->getPickupById();

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'Pickup', $pickup );
		$this->assertEquals( 1, $pickup->getKey_id() );
	}
	
	
	/* getPickupLotById */

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getPickupLotById_noId() {
		$lot = $this->actionManager->getPickupLotById();
		
		// should return actionError when no id is provided
		$this->assertInstanceOf( 'ActionError', $lot );
		$this->assertEquals( 201, $lot->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getPickupLotById_passId() {
		$lot = $this->actionManager->getPickupLotById( 1 );

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'PickupLot', $lot );
		$this->assertEquals( 1, $lot->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getPickupLotById_requestId() {
		$_REQUEST["id"] = 1;
		$lot = $this->actionManager->getPickupLotById();
		
		// check that specific object was returned correctly
		$this->assertInstanceOf( 'PickupLot', $lot );
		$this->assertEquals( 1, $lot->getKey_id() );
	}
	

	/* getPurchaseOrderById */

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getPurchaseOrderById_noId() {
		$order = $this->actionManager->getPurchaseOrderById();
		
		// should return actionError when no id is provided
		$this->assertInstanceOf( 'ActionError', $order );
		$this->assertEquals( 201, $order->getStatusCode() );
	}

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getPurchaseOrderById_passId() {
		$order = $this->actionManager->getPurchaseOrderById( 1 );

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'PurchaseOrder', $order );
		$this->assertEquals( 1, $order->getKey_id() );
	}

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getPurchaseOrderById_requestId() {
		$_REQUEST["id"] = 1;
		$order = $this->actionManager->getPurchaseOrderById();

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'PurchaseOrder', $order );
		$this->assertEquals( 1, $order->getKey_id() );
	}
	
	
	/* getWasteTypeById */

	/**
	 * @group get
	 * @group byId
	 */
	public function test_getWasteTypeById_noId() {
		$type = $this->actionManager->getWasteTypeById();

		// should return actionError when no id is provided
		$this->assertInstanceOf( 'ActionError', $type );
		$this->assertEquals( 201, $type->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getWasteTypeById_passId() {
		$type = $this->actionManager->getWasteTypeById( 1 );

		// check that specific object was returned correctly
		$this->assertInstanceOf( 'WasteType', $type );
		$this->assertEquals( 1, $type->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
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
	

	/* getAuthorizationsByPIId */

	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getAuthorizationsByPIId_noId() {
		
		$auths = $this->actionManager->getAuthorizationsByPIId();
		
		// should return actionError when no id is provided
		$this->assertInstanceOf( 'ActionError', $auths );
		
		/* If error happened inside action functions due to lack of id paramateter
		   (which is what should have happened), ActionError will have error code 201 */
		$this->assertEquals( 201, $auths->getStatusCode() );
	} 
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getAuthorizationsByPIId_passId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of authorizations
		$mock = $this->prepareMockToReturnArray( "PrincipalInvestigator", "getAuthorizations", "Authorization", 5 );
		$this->setGetByIdToReturn( $mock );
		
		$auths = $this->actionManager->getAuthorizationsByPIId( 0 );
		
		$this->assertContainsOnlyInstancesOf( "Authorization", $auths );
		$this->assertCount( 5, $auths );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getAuthorizationsByPIId_requestId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of Authorizations
		$mock = $this->prepareMockToReturnArray( "PrincipalInvestigator", "getAuthorizations", "Authorization", 5 );
		$this->setGetByIdToReturn( $mock );
		
		$_REQUEST["id"] = 0;
		$auths = $this->actionManager->getAuthorizationsByPIId();
		
		$this->assertContainsOnlyInstancesOf( "Authorization", $auths );
		$this->assertCount( 5, $auths );
	}

	
	/* getPickupLotsByPickupId */

	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getPickupLotsByPickupId_noId() {
		$lots = $this->actionManager->getPickupLotsByPickupId();

		//should return actionError when no id provided
		$this->assertInstanceOf( 'ActionError', $lots );
		
		// ActionError should have code 201 if created due to lack of id
		$this->assertEquals( 201, $lots->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getPickupLotsByPickupId_passId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of PickupLots
		$mock = $this->prepareMockToReturnArray( "Pickup", "getPickupLots", "PickupLot", 5 );
		$this->setGetByIdToReturn( $mock );

		$lots = $this->actionManager->getPickupLotsByPickupId( 0 );

		$this->assertContainsOnlyInstancesOf( 'PickupLot', $lots );
		$this->assertCount( 5, $lots );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getPickupLotsByPickupId_requestId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of PickupLots
		$mock = $this->prepareMockToReturnArray( "Pickup", "getPickupLots", "PickupLot", 5 );
		$this->setGetByIdToReturn( $mock );

		$_REQUEST["id"] = 0;
		$lots = $this->actionManager->getPickupLotsByPickupId();

		$this->assertContainsOnlyInstancesOf( 'PickupLot', $lots );
		$this->assertCount( 5, $lots );
	}

	
	/* getDisposalLotsByPickupLotId */
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getDisposalLotsByPickupLotId_noId() {
		$lots = $this->actionManager->getDisposalLotsByPickupLotId();

		// should have returned actionError with error code for missing parameter
		$this->assertInstanceOf( 'ActionError', $lots );
		$this->assertEquals( 201, $lots->getStatusCode() );

	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getDisposalLotsByPickupLotId_passId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of DisposalLots
		$mock = $this->prepareMockToReturnArray( "PickupLot", "getDisposalLots", "DisposalLot", 5 );
		$this->setGetByIdToReturn( $mock );
		
		$lots = $this->actionManager->getDisposalLotsByPickupLotId( 0 );

		$this->assertContainsOnlyInstancesOf( 'DisposalLot', $lots );
		$this->assertCount( 5, $lots );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getDisposalLotsByPickupLotId_requestId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of 
		$mock = $this->prepareMockToReturnArray( "PickupLot", "getDisposalLots", "DisposalLot", 5 );
		$this->setGetByIdToReturn( $mock );

		$_REQUEST["id"] = 0;
		$lots = $this->actionManager->getDisposalLotsByPickupLotId();

		$this->assertContainsOnlyInstancesOf( 'DisposalLot', $lots );
		$this->assertCount( 5, $lots );
	}


	/* getDisposalLotsByDrumId */

	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getDisposalLotsByDrumId_noId() {
		$lots = $this->actionManager->getDisposalLotsByDrumId();

		// should have returned ActionError with status code for missing parameter
		$this->assertInstanceOf('ActionError', $lots);
		$this->assertEquals( 201, $lots->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getDisposalLotsByDrumId_passId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of 
		$mock = $this->prepareMockToReturnArray( "Drum", "getDisposalLots", "DisposalLot", 5 );
		$this->setGetByIdToReturn( $mock );
		
		$lots = $this->actionManager->getDisposalLotsByDrumId( 0 );

		$this->assertContainsOnlyInstancesOf( 'DisposalLot', $lots );
		$this->assertCount( 5, $lots );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getDisposalLotsByDrumId_requestId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of 
		$mock = $this->prepareMockToReturnArray( "Drum", "getDisposalLots", "DisposalLot", 5 );
		$this->setGetByIdToReturn( $mock );
		
		$_REQUEST["id"] = 0;
		$lots = $this->actionManager->getDisposalLotsByDrumId();

		$this->assertContainsOnlyInstancesOf( 'DisposalLot', $lots );
		$this->assertCount( 5, $lots );
	}
	
	
	/* getParcelUsesByParcelId */
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getParcelUsesByParcelId_noId() {
		$uses = $this->actionManager->getParcelUsesByParcelId();

		// should have returned actionError with status code for missing error
		$this->assertInstanceOf( 'ActionError', $uses );
		$this->assertEquals( 201, $uses->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getParcelUsesByParcelId_passId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of 
		$mock = $this->prepareMockToReturnArray( "Parcel", "getUses", "ParcelUse", 5 );
		$this->setGetByIdToReturn( $mock );

		$uses = $this->actionManager->getParcelUsesByParcelId( 0 );
		
		$this->assertContainsOnlyInstancesOf( 'ParcelUse', $uses );
		$this->assertCount( 5, $uses );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getParcelUsesByParcelId_requestId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of 
		$mock = $this->prepareMockToReturnArray( "Parcel", "getUses", "ParcelUse", 5 );
		$this->setGetByIdToReturn( $mock );

		$_REQUEST["id"] = 0;
		$uses = $this->actionManager->getParcelUsesByParcelId();
		
		$this->assertContainsOnlyInstancesOf( 'ParcelUse', $uses );
		$this->assertCount( 5, $uses );
	}
	
	
	/* getActiveParcelsFromPIById */
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getActiveParcelsFromPIById_noId() {
		$parcels = $this->actionManager->getActiveParcelsFromPIById();

		$this->assertInstanceOf( 'ActionError', $parcels );
		$this->assertEquals( 201, $parcels->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getActiveParcelsFromPIById_passId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of 
		$mock = $this->prepareMockToReturnArray( "PrincipalInvestigator", "getActiveParcels", "Parcel", 5 );
		$this->setGetByIdToReturn( $mock );
		
		$parcels = $this->actionManager->getActiveParcelsFromPIById( 0 );

		$this->assertContainsOnlyInstancesOf( 'Parcel', $parcels );
		$this->assertCount( 5, $parcels );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getActiveParcelsFromPIById_requestId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of 
		$mock = $this->prepareMockToReturnArray( "PrincipalInvestigator", "getActiveParcels", "Parcel", 5 );
		$this->setGetByIdToReturn( $mock );
		
		$_REQUEST["id"] = 0;
		$parcels = $this->actionManager->getActiveParcelsFromPIById();

		$this->assertContainsOnlyInstancesOf( 'Parcel', $parcels );
		$this->assertCount( 5, $parcels );
	}
	
	
	/**************************************************************************\
	 *                            GetAll Tests                                *
	\**************************************************************************/
	

	/* getAllCarboys */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllCarboys() {
		$carboys = $this->actionManager->getAllCarboys();

		$this->assertContainsOnlyInstancesOf( 'Carboy', $carboys );
		$this->assertCount( 5, $carboys );
	}
	
	/* getAllDrums */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllDrums() {
		$drums = $this->actionManager->getAllDrums();

		$this->assertContainsOnlyInstancesOf( 'Drum', $drums );
		$this->assertCount( 5, $drums );
	}
	
	/* getAllIsotopes */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllIsotopes() {
		$isotopes = $this->actionManager->getAllIsotopes();

		$this->assertContainsOnlyInstancesOf( 'Isotope', $isotopes );
		$this->assertCount( 5, $isotopes );
	}
	
	/* getAllWasteTypes */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllWasteTypes() {
		$types = $this->actionManager->getAllWasteTypes();

		$this->assertContainsOnlyInstancesOf( 'WasteType', $types );
		$this->assertCount( 5, $types );
	}


	/*************************************************************************\
	 *                            Save Tests                                 *
	\*************************************************************************/
	
	
	/* saveAuthorization */
	
	/**
	 * @group save
	 */
	public function test_saveAuthorization_noObject() {
		$result = $this->actionManager->saveAuthorization();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveAuthorization() {

		$testData = new Authorization();
		$_REQUEST["testInput"] = $testData;

		$result = $this->actionManager->saveAuthorization(); 
		
		// should have returned Authorization with a newly-assigned key id
		$this->assertInstanceOf('Authorization', $result);
		$this->assertEquals( 1, $result->getKey_id() );
		
		// genericDao->save should have been called
		$this->assertEquals( true, $this->daoSpy->wasItCalled('save') );
	}
	
	
	/* saveIsotope */
	
	/**
	 * @group save
	 */
	public function test_saveIsotope_noObject() {
		$result = $this->actionManager->saveIsotope();
		
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}

	/**
	 * @group save
	 */
	public function test_saveIsotope() {
		$testData = new Isotope();
		$_REQUEST['testInput'] = $testData;
		
		$result = $this->actionManager->saveAuthorization();
		
		// should have returned Isotope with newly-assigned key id
		$this->assertInstanceOf( 'Isotope', $result );
		$this->assertEquals( 1, $result->getKey_id() );
		
		// genericDao->save should have been called
		$this->assertEquals( true, $this->daoSpy->wasItCalled('save') );
	}
	

    /* saveCarboy */
	
	/**
	 * @group save
	 */
	public function test_saveCarboy_noObject() {
		$result = $this->actionManager->saveCarboy();
		
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}

    /**
     * @group save
     */
    public function test_saveCarboy() {
        $testData = new Carboy();
        $_REQUEST['testInput'] = $testData;

        $result = $this->actionManager->saveCarboy();

        // should have returned Carboy with newly-assigned key id
        $this->assertInstanceOf( 'Carboy', $result );
        $this->assertEquals( 1, $result->getKey_id() );

        // genericDao->save should have been called
        $this->assertEquals( true, $this->daoSpy->wasItCalled('save') );
    }


    /* saveCarboyUseCycle */

    /**
     * @group save
     */
    public function test_saveCarboyUseCycle_noObject() {
        $result = $this->actionManager->saveCarboyUseCycle();

        // should have returned ActionError, no input given
        $this->assertInstanceOf( 'ActionError', $result );
        $this->assertEquals( 202, $result->getStatusCode() );
    }

    /**
     * @group save
     */
    public function test_saveCarboyUseCycle() {
        $testData = new CarboyUseCycle();
        $_REQUEST['testInput'] = $testData;

        $result = $this->actionManager->saveCarboyUseCycle();

        // should have returned CarboyUseCycle with newly-assigned key id
        $this->assertInstanceOf( 'CarboyUseCycle', $result );
        $this->assertEquals( 1, $result->getKey_id() );

        // genericDao->save should have been called
        $this->assertEquals( true, $this->daoSpy->wasItCalled('save') );
    }


    /* saveDisposalLot */

    /**
     * @group save
     */
    public function test_saveDisposalLot_noObject() {
        $result = $this->actionManager->saveDisposalLot();

        // should have returned ActionError, no input given
        $this->assertInstanceOf( 'ActionError', $result );
        $this->assertEquals( 202, $result->getStatusCode() );
    }

    /**
     * @group save
     */
    public function test_saveDisposalLot() {
        $testData = new DisposalLot();
        $_REQUEST['testInput'] = $testData;
        
        $result = $this->actionManager->saveDisposalLot();

        // should have returned Disposallot with newly assigned key id
        $this->assertInstanceOf( 'DisposalLot', $result );
        $this->assertEquals( 1, $result->getKey_id() );

        // genericDao->save should have been called
        $this->assertEquals( true, $this->daoSpy->wasItCalled('save') );
    }


    /* saveDrum */

    /**
     * @group save
     */
    public function test_saveDrum_noObject() {
        $result = $this->actionManager->saveDrum();

        // should have returned ActionError, no input given
        $this->assertInstanceOf( 'ActionError', $result );
        $this->assertEquals( 202, $result->getStatusCode() );
    }

    /**
     * @group save
     */
    public function test_saveDrum() {
        $testData = new Drum();
        $_REQUEST['testInput'] = $testData;

        $result = $this->actionManager->saveDrum();

        // should have returned Drum with newly assigned key id
        $this->assertInstanceOf( 'Drum', $result );
        $this->assertEquals( 1, $result->getKey_id() );

        // genericDao->save should have been called
        $this->assertEquals( true, $this->daoSpy->wasItCalled('save') );
    }


    /* saveParcel */

    /**
     * @group save
     */
    public function test_saveParcel_noObject() {
        $result = $this->actionManager->saveParcel();
        

        // should have returned ActionError, no input given
        $this->assertInstanceOf( 'ActionError', $result );
        $this->assertEquals( 202, $result->getStatusCode() );
    }

    /**
     * @group save
     */
    public function test_saveParcel() {
        $testData = new Parcel();
        $_REQUEST['testInput'] = $testData;
        
        $result = $this->actionManager->saveParcel();

        // should ahve returned Parcel with newly assigned key id
        $this->assertInstanceOf( 'Parcel', $result );
        $this->assertEquals( 1, $result->getKey_id() );

        // genericDao->save should have been called
        $this->assertEquals( true, $this->daoSpy->wasItCalled('save') );
    }


    /* saveParcelUse */

    /**
     * @group save
     */
    public function test_saveParcelUse_noObject() {
        $result = $this->actionManager->saveParcelUse();

        // should have returned ActionError, no input given
        $this->assertInstanceOf( 'ActionError', $result );
        $this->assertEquals( 202, $result->getStatusCode() );
    }

    /**
     * @group save
     */
    public function test_saveParcelUse() {

        $testData = new ParcelUse();
        $_REQUEST['testInput'] = $testData;

        $result = $this->actionManager->saveParcelUse();

        // should have returned ParcelUse with newly assigned key id
        $this->assertInstanceOf( 'ParcelUse', $result );
        $this->assertEquals( 1, $result->getKey_id() );

        // genericDao->save should have been called
        $this->assertEquals( true, $this->daoSpy->wasItCalled('save') );
    }


    /* savePickup */

    /**
     * @group save
     */
    public function test_savePickup_noObject() {
        $result = $this->actionManager->savePickup();

        // should have returned ActionError, no input given
        $this->assertInstanceOf( 'ActionError', $result );
        $this->assertEquals( 202, $result->getStatusCode() );
    }

    /**
     * @group save
     */
    public function test_savePickup() {
        $testData = new Pickup();
        $_REQUEST['testInput'] = $testData;

        $result = $this->actionManager->savePickup();

        // should have returned Pickup with newly assigned key id
        $this->assertInstanceOf( 'Pickup', $result );
        $this->assertEquals( 1, $result->getKey_id() );

        // genericDao->save should have been called
        $this->assertEquals( true, $this->daoSpy->wasItCalled('save') );
    }


    /* savePickupLot */

    /**
     * @group save
     */
    public function test_savePickupLot_noObject() {
        $result = $this->actionManager->savePickupLot();

        // should have returned ActionError, no input given
        $this->assertInstanceOf( 'ActionError', $result );
        $this->assertEquals( 202, $result->getStatusCode() );
    }

    /**
     * @group save
     */
    public function test_savePickupLot() {
        $testData = new PickupLot();
        $_REQUEST['testInput'] = $testData;

        $result = $this->actionManager->savePickupLot();

        // should have returned PickupLot with newly assigned key id
        $this->assertInstanceOf( 'PickupLot', $result );
        $this->assertEquals( 1, $result->getKey_id() );

        // genericDao->save should have been called
        $this->assertEquals( true, $this->daoSpy->wasItCalled('save') );
    }


    /* savePurchaseOrder */

    /**
     * @group save
     */
    public function test_savePurchaseOrder_noObject() {
        $result = $this->actionManager->savePurchaseOrder();

        // should have returned ActionError, no input given
        $this->assertInstanceOf( 'ActionError', $result );
        $this->assertEquals( 202, $result->getStatusCode() );
    }

    /**
     * @group save
     */
    public function test_savePurchaseOrder() {
        $testData = new PurchaseOrder();
        $_REQUEST['testInput'] = $testData;
        
        $result = $this->actionManager->savePurchaseOrder();

        // should have returned PurchaseOrder with newly assigned key id
        $this->assertInstanceOf( 'PurchaseOrder', $result );
        $this->assertEquals( 1, $result->getKey_id() );

        // genericDao->save should have been called
        $this->assertEquals( true, $this->daoSpy->wasItCalled('save') );
    }


    /* saveWasteType */

    /**
     * @group save
     */
    public function test_saveWasteType_noObject() {
        $result = $this->actionManager->saveWasteType();

        // should have returned ActionError, no input given
        $this->assertInstanceOf( 'ActionError', $result );
        $this->assertEquals( 202, $result->getStatusCode() );
    }

    /**
     * @group save
     */
    public function test_saveWasteType() {
        $testData = new WasteType();
        $_REQUEST['testInput'] = $testData;
        
        $result = $this->actionManager->saveWasteType();

        // should have returned WasteType with newly assigned key id
        $this->assertInstanceOf( 'WasteType', $result );
        $this->assertEquals( 1, $result->getKey_id() );

        // genericDao->save should have been called
        $this->assertEquals( true, $this->daoSpy->wasItCalled('save') );
    }
}

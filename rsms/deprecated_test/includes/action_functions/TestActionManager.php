<?php
/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */

// here because other classes need to be included, like GenericCrud subclasses
// TODO remove dependency on autoloader - autoloader requires database to be running
require_once(dirname(__FILE__) . '/../../../src/Autoloader.php');
Logger::configure( dirname(__FILE__) . "/../../../src/includes/conf/log4php-config.php");

// Include action functions to test
require_once(dirname(__FILE__) . '/../../../src/includes/ActionManager.php');

// Unit double for GenericDao so we don't actually modify database
require_once(dirname(__FILE__) . '/../../../src/includes/dao/GenericDAOSpy.php');


// TODO: check that getById was called with correct arguments
/* 
 * TODO: Add more descriptive custom error messages
 * 
 * I didn't realize at first that this was a thing, but look!
 * 
 * $this->assertSomething(answer, somethingToCheck, "Explanation of what broke if this assertion fails");
 * logs the last parameter if the assertion fails to give more detailed errors than
 * "expected somethingToCheck to equal answer" etc.
 * 
 * TODO do this for all new tests, add to old tests as well at some point
 * 
 */

class TestActionManager extends PHPUnit_Framework_TestCase {
		
	// certain methods added just for testing do not need to be tested
	protected static $IGNORED_METHODS = array("__construct", "setDaoFactory", "setTestMode", "isTestModeEnabled");

	public $actionManager;
	private $daoSpy;
	private $spyFactory;

	// here because tests extending this class will need to specify what (if any)
	// subclasses of ActionManager they need to instantiate.
	private $actionManagerClassName;
	
	function __construct($classToTest = "ActionManager") {
		parent::__construct();
		$this->setTestedClassName($classToTest);
	}	
	
	public function getTestedClassName() { return $this->actionManagerClassName; }
	public function setTestedClassName($newName) {
		$this->actionManagerClassName = $newName;
	}
	
	public function getSpyFactory() { return $this->spyFactory; }
	public function setSpyFactory($factory) { $this->spyFactory = $factory; }

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
		$this->spyFactory = new DaoFactory($daoSpy);
		
		$this->daoSpy = $daoSpy;
		
		// give our dao injector to ActionManager to substitute daoSpy for GenericDao
		$actionManagerClass = $this->getTestedClassName();
		$this->actionManager = new $actionManagerClass($this->spyFactory);
		
		// set actionManager to read from $_REQUEST['testInput'] instead of JsonManager
		$this->actionManager->setTestMode(true);
	}
	
	function setGetByIdToReturn($objToReturn, $class = "Any") {
		// create test double for GenericDao
		$daoSpy = new GenericDAOSpy();
		
		// Override $daoSpy's getById method to return specific obj when
		// GenericDao has model object instance of class. If class == Any,
		// it overrides for all model objects.
		$daoSpy->overrideMethod($class, 'getById', $objToReturn);
		
		// new daoFactory will provide actionManager the modified GenericDaoSpy
		$this->getSpyFactory()->setModelDao($daoSpy);
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

	function getDaoSpy($modelObject) {
		return $this->getSpyFactory()->getDao($modelObject);
	}
	
	/**
	 * Converts array of entityMaps to associative key_value pairs,
	 * [methodName] => loadingType
	 * 
	 * helpful when tests need to check a specific entity map's property
	 * 
	 * @param array [of EntityMaps] $map
	 */
	protected function convertEntityMapsToAssociativeArray($maps) {
		$mapArray = array();
		foreach($maps as $entityMap) {
			$mapArray[$entityMap->getEntityAccessor()] = $entityMap->getLoadingType();
		}
		return $mapArray;
	}


	/**
	 * Used by test_FullCoverage to filter tests from other methods in this class
	 */
	function isATest($name) {
		// all tests start with "test_", so if it contains this, it is a test method
		return strpos($name, "test_") !== false;
	}
	
	
	/**
	 * Verify that every method in ActionManager is being tested
	 */
	public function test_fullCoverage() {
	
		// get just the public methods present in the class we're testing
		$manager = new ReflectionClass( $this->getTestedClassName() );
		$reflectedMethods = $manager->getMethods(ReflectionMethod::IS_PUBLIC);
		$actionMethods = array();
		foreach($reflectedMethods as $method) {
			$actionMethods[] = $method->name;
		}
		
		// get list of methods in this tester class
		$classMethods = get_class_methods($this);
	
		// since the isATest filter function is declared inside a class, must
		// wrap in array for array_filter to use it. PHP is weird.
		$isATest = array($this, "isATest");
	
		// remove irrelevant methods that aren't directly testing things
		$testedMethods = array_filter($classMethods, $isATest);
	
		// strip irrelevant 'test_' and _details from each method name
		$methodNames = array();
		foreach($testedMethods as $method) {
			// split method name into array of strings sepparated by '_'s
			$method = explode('_', $method);
			// since all testedMethods start with test_, method name is second item
			$methodNames[] = $method[1];
		}
	
		// remove duplicate method names
		$testedMethods = array_unique($methodNames);
	
		// check for actionMethods without corresponding tests.
		$missingTestCount = 0;
		$untestedMethods = array();
		foreach($actionMethods as $method) {
			if( !in_array($method, $testedMethods) && !in_array($method, self::$IGNORED_METHODS) ) {
				$missingTestCount++;
				$untestedMethods[] = $method;
            }
		}

		
		$missingTests = print_r($untestedMethods, true);

		// TODO: Better way to format list of untested methods?
		$this->assertEquals(0, $missingTestCount, "The following methods are not tested: $missingTests");

	}
	

	/*************************************************************************\
	 *                            GetAll Tests                               *
	\*************************************************************************/

	
	/* getAllUsers */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllUsers() {
		$result = $this->actionManager->getAllUsers();

		$this->assertContainsOnlyInstancesOf( 'User', $result );
		$this->assertCount( 5, $result );
	}

	/* getAllRoles */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllRoles() {
		$result = $this->actionManager->getAllRoles();

		$this->assertContainsOnlyInstancesOf( 'Role', $result );
		$this->assertCount( 5, $result );
	}

	/* getAllQuestions */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllQuestions() {
		$result = $this->actionManager->getAllQuestions();

		$this->assertContainsOnlyInstancesOf( 'Question', $result );
		$this->assertCount( 5, $result );
	}

	
	/* NOTE: This function is different from the rest as it returns a tree. */
	public function test_getAllHazardsAsTree() {
		$result = $this->actionManager->getAllHazardsAsTree();
		
		// should return "root" hazard of tree, with key id 10,000
		$this->assertInstanceOf( 'Hazard', $result );
		$this->assertEquals( 10000, $result->getKey_id() );
		
		// should have specific entity maps set so JsonManager properly loads the rest of the tree later.
		$entityMaps = $result->getEntityMaps();
		$this->assertContainsOnlyInstancesOf( 'EntityMap', $entityMaps );
		
		// rearrange entity maps into associative array for easier testability.
		$maps = $this->convertEntityMapsToAssociativeArray($entityMaps);
		
		// check specific entity maps for correct setting
		$this->assertEquals( "lazy", $maps["getSubhazards"] );
		$this->assertEquals( "eager", $maps["getActiveSubhazards"] );
		$this->assertEquals( "lazy", $maps["getChecklist"] );
		$this->assertEquals( "lazy", $maps["getRooms"] );
		$this->assertEquals( "lazy", $maps["getInspectionRooms"] );

	}

	/* getAllHazards */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllHazards() {
		$result = $this->actionManager->getAllHazards();

		$this->assertContainsOnlyInstancesOf( 'Hazard', $result );
		$this->assertCount( 5, $result );
	}

	/* getAllInspectors */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllInspectors() {
		$result = $this->actionManager->getAllInspectors();

		$this->assertContainsOnlyInstancesOf( 'Inspector', $result );
		$this->assertCount( 5, $result );
	}

	/* getAllPIs */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllPIs() {
		$result = $this->actionManager->getAllPIs();

		$this->assertContainsOnlyInstancesOf( 'PrincipalInvestigator', $result );
		$this->assertCount( 5, $result );
	}

	/* getAllRooms */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllRooms() {
		$result = $this->actionManager->getAllRooms();

		$this->assertContainsOnlyInstancesOf( 'Room', $result );
		$this->assertCount( 5, $result );
	}

	/* getAllDepartments */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllDepartments() {
		$result = $this->actionManager->getAllDepartments();

		$this->assertContainsOnlyInstancesOf( 'Department', $result );
		$this->assertCount( 5, $result );
	}

	/* getAllActiveDepartments */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllActiveDepartments() {
		$result = $this->actionManager->getAllActiveDepartments();

		$this->assertContainsOnlyInstancesOf( 'Department', $result );
		$this->assertCount( 5, $result );
	}

	/* getAllBuildings */
	/**
	 * @group get
	 * @group getAll
	 */
	public function test_getAllBuildings() {
		$result = $this->actionManager->getAllBuildings();

		$this->assertContainsOnlyInstancesOf( 'Building', $result );
		$this->assertCount( 5, $result );
	}
	
	/*************************************************************************\
	 *                         Basic Get Tests                               *
	\*************************************************************************/


	/* getUserById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getUserById_noId() {
		$result = $this->actionManager->getUserById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getUserById_passId() {
	
		$result = $this->actionManager->getUserById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'User', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getUserById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getUserById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'User', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getRoleById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getRoleById_noId() {
		$result = $this->actionManager->getRoleById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getRoleById_passId() {
	
		$result = $this->actionManager->getRoleById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Role', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getRoleById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getRoleById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Role', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getChecklistById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getChecklistById_noId() {
		$result = $this->actionManager->getChecklistById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getChecklistById_passId() {
	
		$result = $this->actionManager->getChecklistById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Checklist', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getChecklistById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getChecklistById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Checklist', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getHazardById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getHazardById_noId() {
		$result = $this->actionManager->getHazardById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getHazardById_passId() {
	
		$result = $this->actionManager->getHazardById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Hazard', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getHazardById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getHazardById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Hazard', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getQuestionById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getQuestionById_noId() {
		$result = $this->actionManager->getQuestionById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getQuestionById_passId() {
	
		$result = $this->actionManager->getQuestionById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Question', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getQuestionById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getQuestionById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Question', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getPIById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getPIById_noId() {
		$result = $this->actionManager->getPIById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getPIById_passId() {
	
		$result = $this->actionManager->getPIById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'PrincipalInvestigator', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getPIById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getPIById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'PrincipalInvestigator', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getRoomById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getRoomById_noId() {
		$result = $this->actionManager->getRoomById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getRoomById_passId() {
	
		$result = $this->actionManager->getRoomById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Room', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getRoomById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getRoomById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Room', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getDepartmentById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDepartmentById_noId() {
		$result = $this->actionManager->getDepartmentById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDepartmentById_passId() {
	
		$result = $this->actionManager->getDepartmentById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Department', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDepartmentById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getDepartmentById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Department', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getBuildingById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getBuildingById_noId() {
		$result = $this->actionManager->getBuildingById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getBuildingById_passId() {
	
		$result = $this->actionManager->getBuildingById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Building', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getBuildingById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getBuildingById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Building', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getDeficiencyById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDeficiencyById_noId() {
		$result = $this->actionManager->getDeficiencyById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDeficiencyById_passId() {
	
		$result = $this->actionManager->getDeficiencyById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Deficiency', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDeficiencyById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getDeficiencyById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Deficiency', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getInspectionById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getInspectionById_noId() {
		$result = $this->actionManager->getInspectionById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getInspectionById_passId() {
	
		$result = $this->actionManager->getInspectionById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Inspection', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getInspectionById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getInspectionById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Inspection', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getRecommendationById */
	
	/**
	 
	 * @group get
	 * @group byId
	 */
	public function test_getRecommendationById_noId() {
		$result = $this->actionManager->getRecommendationById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getRecommendationById_passId() {
	
		$result = $this->actionManager->getRecommendationById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Recommendation', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getRecommendationById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getRecommendationById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Recommendation', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getObservationById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getObservationById_noId() {
		$result = $this->actionManager->getObservationById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getObservationById_passId() {
	
		$result = $this->actionManager->getObservationById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Observation', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getObservationById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getObservationById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Observation', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getResponseById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getResponseById_noId() {
		$result = $this->actionManager->getResponseById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getResponseById_passId() {
	
		$result = $this->actionManager->getResponseById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Response', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 
	 * @group get
	 * @group byId
	 */
	public function test_getResponseById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getResponseById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'Response', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/*************************************************************************\
	 *                         Get By Relationship                           *
	\*************************************************************************/
	
	/* getSupervisorByUserId */

	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getSupervisorByUserId_noId() {

		$results = $this->actionManager->getSupervisorByUserId();
		
		// should return actionError, no id provided
		$this->assertInstanceOf( 'ActionError', $results );
		$this->assertEquals( 201, $results->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getSupervisorByUserId_passId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of authorizations
		$mock = $this->prepareMockToReturn( "User", "getSupervisor", new PrincipalInvestigator() );
		$this->setGetByIdToReturn( $mock );
		
		$results = $this->actionManager->getSupervisorByUserId(1);
		
		$this->assertInstanceOf( "PrincipalInvestigator", $results );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getSupervisorByUserId_requestId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of Authorizations
		$mock = $this->prepareMockToReturn( "User", "getSupervisor", new PrincipalInvestigator() );
		$this->setGetByIdToReturn( $mock );
		
		$_REQUEST["id"] = 0;
		$results = $this->actionManager->getSupervisorByUserId();
		
		$this->assertInstanceOf( "PrincipalInvestigator", $results );
	}
	
	
	/* getCheckListByHazardId */
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getChecklistByHazardId() {
	
		$results = $this->actionManager->getChecklistByHazardId();
	
		// should return actionError, no id provided
		$this->assertInstanceOf( 'ActionError', $results );
		$this->assertEquals( 201, $results->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getChecklistByHazardId_passId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of authorizations
		$mock = $this->prepareMockToReturn( "Hazard", "getChecklist", new Checklist() );
		$this->setGetByIdToReturn( $mock );
	
		$results = $this->actionManager->getChecklistByHazardId(1);
	
		$this->assertInstanceOf( "Checklist", $results );
	}
	
	/**
	 * @group get
	 * @group byRelation
	 */
	public function test_getChecklistByHazardId_requestId() {
		// GenericDao->getById should return a mock object which, in turn, returns a list of Authorizations
		$mock = $this->prepareMockToReturn( "Hazard", "getChecklist", new Checklist() );
		$this->setGetByIdToReturn( $mock );
	
		$_REQUEST["id"] = 0;
		$results = $this->actionManager->getChecklistByHazardId();
	
		$this->assertInstanceOf( "Checklist", $results );
	}
	
	/*************************************************************************\
	 *                              Save Tests                               *
	\*************************************************************************/

	/* saveUser */
	
	/**
	 * @group save
	 */
	public function test_saveUser_noObject() {
		$result = $this->actionManager->saveUser();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveUser() {
	
		$testData = new User();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveUser();
	
		// should have returned User with a newly-assigned key id
		$this->assertInstanceOf('User', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new User())->wasItCalled('save') );
	}
	
	
	/* saveChecklist */
	
	/**
	 * @group save
	 */
	public function test_saveChecklist_noObject() {
		$result = $this->actionManager->saveChecklist();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveChecklist() {
	
		$testData = new Checklist();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveChecklist();
	
		// should have returned Checklist with a newly-assigned key id
		$this->assertInstanceOf('Checklist', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new Checklist())->wasItCalled('save') );
	}
	
	
	/* saveQuestion */
	
	/**
	 * @group save
	 */
	public function test_saveQuestion_noObject() {
		$result = $this->actionManager->saveQuestion();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveQuestion() {
	
		$testData = new Question();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveQuestion();
	
		// should have returned Question with a newly-assigned key id
		$this->assertInstanceOf('Question', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new Question())->wasItCalled('save') );
	}
	
	
	/* saveDeficiency */
	
	/**
	 * @group save
	 */
	public function test_saveDeficiency_noObject() {
		$result = $this->actionManager->saveDeficiency();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveDeficiency() {
	
		$testData = new Deficiency();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveDeficiency();
	
		// should have returned Deficiency with a newly-assigned key id
		$this->assertInstanceOf('Deficiency', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new Deficiency())->wasItCalled('save') );
	}
	
	
	/* saveObservation */
	
	/**
	 * @group save
	 */
	public function test_saveObservation_noObject() {
		$result = $this->actionManager->saveObservation();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveObservation() {
	
		$testData = new Observation();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveObservation();
	
		// should have returned Observation with a newly-assigned key id
		$this->assertInstanceOf('Observation', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new Observation())->wasItCalled('save') );
	}
	
	
	/* saveRecommendation */
	
	/**
	 * @group save
	 */
	public function test_saveRecommendation_noObject() {
		$result = $this->actionManager->saveRecommendation();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveRecommendation() {
	
		$testData = new Recommendation();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveRecommendation();
	
		// should have returned Recommendation with a newly-assigned key id
		$this->assertInstanceOf('Recommendation', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new Recommendation())->wasItCalled('save') );
	}
	
	
	/* saveSupplementalObservation */
	
	/**
	 * @group save
	 */
	public function test_saveSupplementalObservation_noObject() {
		$result = $this->actionManager->saveSupplementalObservation();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveSupplementalObservation() {
	
		$testData = new SupplementalObservation();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveSupplementalObservation();
	
		// should have returned SupplementalObservation with a newly-assigned key id
		$this->assertInstanceOf('SupplementalObservation', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new SupplementalObservation())->wasItCalled('save') );
	}
	
	
	/* saveSupplementalRecommendation */
	
	/**
	 * @group save
	 */
	public function test_saveSupplementalRecommendation_noObject() {
		$result = $this->actionManager->saveSupplementalRecommendation();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveSupplementalRecommendation() {
	
		$testData = new SupplementalRecommendation();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveSupplementalRecommendation();
	
		// should have returned SupplementalRecommendation with a newly-assigned key id
		$this->assertInstanceOf('SupplementalRecommendation', $result);
		$this->assertEquals( 1, $result->getKey_id() );
		
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new SupplementalRecommendation())->wasItCalled('save') );
	}
	
	
	/* saveHazard */
	
	/**
	 * @group save
	 */
	public function test_saveHazard_noObject() {
		$result = $this->actionManager->saveHazard();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveHazard_noSiblings() {
	
		$testData = new Hazard();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveHazard();
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new Hazard())->wasItCalled('save') );

		// last method of genericDao called should've been called with $testData
		$methodCalls = $this->getDaoSpy(new Hazard())->getCalls();
		$methodArgs = $methodCalls[count($methodCalls.length) -1]->getArg(0);

		// should have returned Hazard with a newly-assigned key id
		$this->assertInstanceOf('Hazard', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	}

	/**
	 * @group save
	 */
	public function test_saveHazard_unorderedSiblings() {

		// set up tree structure of subhazards belonging to parent
		$parentId = 10;
		$parentHazard = new Hazard();
		$parentHazard->setKey_id($parentId);
		
		// hazards in no particular order
		$child1 = new Hazard();
		$child1->setName("Bio Hazards");
		$child1->setOrder_index(1.0);

		$child2 = new Hazard();
		$child2->setName("Meaningless hazard");
		$child2->setOrder_index(1.4);
		
		$child3 = new Hazard();
		$child3->setName("A non-alphabetical name");
		$child3->setOrder_index(2.1);

		$testData = new Hazard();
		$testData->setParent_hazard_id($parentId);
		$testData->setName("John Doe Hazard");
		
		$subHazards = array( $child1, $child2, $child3 );
		$parentHazard->setSubHazards($subHazards);
		
		$_REQUEST["testInput"] = $testData;
		
		// saveHazard makes a call to GenericDao->getById to get the parent hazard
		// make sure it returns the proper parent instead of a generic hazard
		$this->setGetByIdToReturn($parentHazard);
		
		$result = $this->actionManager->saveHazard();
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new Hazard())->wasItCalled('save') );

		// last method of genericDao called should've been called with $testData
		$methodCalls = $this->getDaoSpy(new Hazard())->getCalls();
		$methodArgs = $methodCalls[count($methodCalls.length) -1]->getArg(0);

		// should have returned Hazard with a newly-assigned key id
		$this->assertInstanceOf('Hazard', $result);
		$this->assertEquals( 1, $result->getKey_id() );
		
		// returned hazard should have new orderIndex at end of list
		$this->assertGreaterThan($child3->getOrder_index(), $result->getOrder_index());
	}
	
	/*
	 * @group save
	 */
	function test_saveHazard_alphabeticalSiblings() {
	
		// set up tree structure of subhazards belonging to parent
		$parentId = 10;
		$parentHazard = new Hazard();
		$parentHazard->setKey_id($parentId);
		
		// hazards organized alphabetically
		$child1 = new Hazard();
		$child1->setName("A first listed hazard");
		$child1->setOrder_index(1.0);

		$child2 = new Hazard();
		$child2->setName("Second in alphabetical");
		$child2->setOrder_index(1.4);
		
		$child3 = new Hazard();
		$child3->setName("Ze last item");
		$child3->setOrder_index(2.1);

		$testData = new Hazard();
		$testData->setParent_hazard_id($parentId);
		$testData->setName("Should be second to last");
		
		$subHazards = array( $child1, $child2, $child3 );
		$parentHazard->setSubHazards($subHazards);
		
		$_REQUEST["testInput"] = $testData;
		
		// saveHazard makes a call to GenericDao->getById to get the parent hazard
		// make sure it returns the proper parent instead of a generic hazard
		$this->setGetByIdToReturn($parentHazard);
		
		$result = $this->actionManager->saveHazard();
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new hazard())->wasItCalled('save') );

		// last method of genericDao called should've been called with $testData
		$methodCalls = $this->getDaoSpy(new Hazard())->getCalls();
		$methodArgs = $methodCalls[count($methodCalls.length) -1]->getArg(0);

		// should have returned Hazard with a newly-assigned key id
		$this->assertInstanceOf('Hazard', $result);
		$this->assertEquals( 1, $result->getKey_id() );
		
		// result order index should be second to last
		$this->assertLessThan($child3->getOrder_index(), $result->getOrder_index());	
		$this->assertGreaterThan($child2->getOrder_index(), $result->getOrder_index());
	}

	
	/* saveRoom */
	
	/**
	 * @group save
	 */
	public function test_saveRoom_noObject() {
		$result = $this->actionManager->saveRoom();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveRoom() {
	
		$testData = new Room();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveRoom();
	
		// should have returned Room with a newly-assigned key id
		$this->assertInstanceOf('Room', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new Room())->wasItCalled('save') );
	}
	
	
	/* saveBuilding */
	
	/**
	 * @group save
	 */
	public function test_saveBuilding_noObject() {
		$result = $this->actionManager->saveBuilding();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveBuilding() {
	
		$testData = new Building();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveBuilding();
	
		// should have returned Building with a newly-assigned key id
		$this->assertInstanceOf('Building', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new Building())->wasItCalled('save') );
	}
	
	
	/* savePI */
	
	/**
	 * @group save
	 */
	public function test_savePI_noObject() {
		$result = $this->actionManager->savePI();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_savePI() {
	
		$testData = new PrincipalInvestigator();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->savePI();
	
		// should have returned PI with a newly-assigned key id
		$this->assertInstanceOf('PrincipalInvestigator', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new PrincipalInvestigator())->wasItCalled('save') );
	}
	
	
	/* saveInspector */
	
	/**
	 * @group save
	 */
	public function test_saveInspector_noObject() {
		$result = $this->actionManager->saveInspector();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveInspector() {
	
		$testData = new Inspector();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveInspector();
	
		// should have returned Inspector with a newly-assigned key id
		$this->assertInstanceOf('Inspector', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new Inspector)->wasItCalled('save') );
	}
	
	
	/* saveDepartment */
	
	/**
	 * @group save
	 */
	public function test_saveDepartment_noObject() {
		$result = $this->actionManager->saveDepartment();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveDepartment() {
	
		$testData = new Department();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveDepartment();
	
		// should have returned Department with a newly-assigned key id
		$this->assertInstanceOf('Department', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new Department())->wasItCalled('save') );
	}
	
	
	/* saveInspection */
	
	/**
	 * @group save
	 */
	public function test_saveInspection_noObject() {
		$result = $this->actionManager->saveInspection();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveInspection() {
	
		$testData = new Inspection();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveInspection();
	
		// should have returned Inspection with a newly-assigned key id
		$this->assertInstanceOf('Inspection', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new Inspection())->wasItCalled('save') );
	}
	
	

	/* saveNoteForInspection */
	
	/**
	 * @group save
	 */
	public function test_saveNoteForInspection_noObject() {
		$result = $this->actionManager->saveNoteForInspection();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	/* Needs special work - NoteForInspection not correct class name
	
	public function test_saveNoteForInspection() {
	
		$testData = new NoteForInspection();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveNoteForInspection();
	
		// should have returned NoteForInspection with a newly-assigned key id
		$this->assertInstanceOf('NoteForInspection', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy()->wasItCalled('save') );
	}
	
	
	/* saveResponse */
	
	/**
	 * @group save
	 */
	public function test_saveResponse_noObject() {
		$result = $this->actionManager->saveResponse();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveResponse() {
	
		$testData = new Response();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveResponse();
	
		// should have returned Response with a newly-assigned key id
		$this->assertInstanceOf('Response', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new Response())->wasItCalled('save') );
	}
	
	
	/* saveDeficiencySelection */
	
	/**
	 * @group save
	 */
	public function test_saveDeficiencySelection_noInput() {
		$result = $this->actionManager->saveDeficiencySelection();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveDeficiencySelection_noRooms() {
	
		$testData = new DeficiencySelection();
		$_REQUEST["testInput"] = $testData;

		$result = $this->actionManager->saveDeficiencySelection();
		$calls = $this->getDaoSpy(new DeficiencySelection())->getCalls();
	
		// if deficiencySelection has no rooms, saveDeficiencySelction should delete it
		$this->assertTrue( $this->getDaoSpy(new DeficiencySelection())->wasItCalled('deleteById') );
		
		// should have passed DeficiencySelection to deleteById 
		$lastCall = $calls[count(calls) - 1];
		$this->assertEquals( $testData, $lastCall->getArg(0) );
	}
	
	/**
	 * @group save
	 */
	public function test_saveDeficiencySelection_withRooms() {

		// set up test deficiencySelection with room children to save
		$testRoom1 = new Room();
		$testRoom1->setKey_id(42);
		
		$testRoom2 = new Room();
		$testRoom2->setKey_id(3);

		$roomIds = array($testRoom1->getKey_id(), $testRoom2->getKey_id());
		$roomsArray = array($testRoom1, $testRoom2);
		
		$testData = new DeficiencySelection();
		$testData->setRooms($roomsArray);
		$testData->setRoomIds($roomIds);
		
		$_REQUEST["testInput"] = $testData;
		$dao = $this->getDaoSpy(new DeficiencySelection());
	
		$result = $this->actionManager->saveDeficiencySelection();
		$calls = $dao->getCalls();
	
		// since deficiencySelection has rooms, saveDeficiencySelction should not delete it
		$this->assertFalse( $dao->wasItCalled('deleteById') );
		
		// addRelatedItems should have been called
		$this->assertTrue( $dao->wasItCalled('addRelatedItems') );
		
		// testRoom's key id should have been passed to addRelatedItems
		$lastAddedId = $dao->getLastCall('addRelatedItems')->getArg(0);
		$this->assertEquals( $testRoom2->getKey_id(), $lastAddedId );
		
	}
	
	/* saveCorrectiveAction */
	
	/**
	 * @group save
	 */
	public function test_saveCorrectiveAction_noObject() {
		$result = $this->actionManager->saveCorrectiveAction();
	
		// should have returned actionError, no input given
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 202, $result->getStatusCode() );
	}
	
	/**
	 * @group save
	 */
	public function test_saveCorrectiveAction() {
	
		$testData = new CorrectiveAction();
		$_REQUEST["testInput"] = $testData;
	
		$result = $this->actionManager->saveCorrectiveAction();
	
		// should have returned CorrectiveAction with a newly-assigned key id
		$this->assertInstanceOf('CorrectiveAction', $result);
		$this->assertEquals( 1, $result->getKey_id() );
	
		// genericDao->save should have been called
		$this->assertTrue( $this->getDaoSpy(new CorrectiveAction())->wasItCalled('save') );
	}
	

	/*************************************************************************\
	 *                        Other Assorted Tests                           *
	\*************************************************************************/
	
	/* getHazardTreeNode */
	
	/**
	 * @group other
	 */
	public function test_getHazardTreeNode() {
		// construct fake hazard with subHazards to use
		$parentHazard = new Hazard();
		$parentHazard->setKey_id(1);
		
		// children subHazards that the method will act on
		$subHazard1 = new Hazard();
		$subHazard1->setKey_id(2);
		
		$subHazard2 = new Hazard();
		$subHazard2->setKey_id(3);
		$subHazard2->setChecklist(new Checklist());
		
		// set subHazards as children of parent, set getById to return parent when getHazardById is called.
		$parentHazard->setSubHazards( array($subHazard1, $subHazard2) );
		$this->setGetByIdToReturn($parentHazard);
		

		$result = $this->actionManager->getHazardTreeNode(1);
		
		// should return array of 2 items, subHazard1 and subHazard2
		$this->assertCount( 2, $result, "Expected array of 2 items" );
		$this->assertContainsOnlyInstancesOf( "Hazard", $result, "Array should contain only hazards" );

		// extract relevant entityMaps to check
		$firstItem = $result[0];
		$firstMaps = $firstItem->getEntityMaps();
		$firstMaps = $this->convertEntityMapsToAssociativeArray($firstMaps);
		$secondItem = $result[1];
		$secondMaps = $secondItem->getEntityMaps();
		$secondMaps = $this->convertEntityMapsToAssociativeArray($secondMaps);
		
		// getChecklist and getHasChildren should be eager for each returned hazard...
		$this->assertEquals( "eager", $firstMaps["getChecklist"], "getChecklist should be eagerly loaded" );
		$this->assertEquals( "eager", $firstMaps["getHasChildren"], "getHasChildren should be eagerly loaded");

		$this->assertEquals( "eager", $secondMaps["getChecklist"], "getChecklist should be eagerly loaded" );
		$this->assertEquals( "eager", $secondMaps["getHasChildren"], "getHasChildren should be eagerly loaded" );
		
		//... but not getSubHazards
		$this->assertEquals( "lazy", $firstMaps["getSubHazards"], "getSubHazards should be lazy loaded" );
		$this->assertEquals( "lazy", $secondMaps["getSubHazards"], "getSubHazards should be lazy loaded" );

		//NOTE: Not testing other entityMaps since those could change later,
		// getSubhazards =lazy and getChecklist=eager is the only thing that MUST be set
		// to avoid breakage
		
		// checklists in returned hazards should have everything lazy loaded
		$checklistMaps = $secondItem->getChecklist()->getEntityMaps();
		foreach($checklistMaps as $map) {
			$this->assertEquals( "lazy", $map->getLoadingType(), "entityMaps in checklist's maps should be lazy" );
		}

	}
	
	/* moveHazardToParent */
	
	/**
	 * @group other
	 */
	public function test_moveHazardToParent_noId() {
		$result = $this->actionManager->moveHazardToParent();
		
		$this->assertInstanceOf( "ActionError", $result, "No input - should have returned error.");
		$this->assertEquals( 201, $result->getStatusCode(), "ActionError should have error code 201" );
	}
	
	/**
	 * @group other
	 */
	public function test_moveHazardToParent_passId() {
		// fake hazard to be used
		$fakeHazard = new Hazard();
		$fakeHazard->setKey_id(1);
		$fakeHazard->setParent_hazard_id(2);
		
		// set genericDao to return fake hazard instead of changing a real one
		$this->getDaoSpy()->overrideMethod("getById", $fakeHazard);
		
		$result = $this->actionManager->moveHazardToParent(1, 3);
		// NOTE: as yet, moveHazard does not return anything after success,
		// only an empty string. Change as necessary later if method changes.

		// get methods of GenericDaoSpy that were called
		$calledMethods = $this->getDaoSpy(new Hazard())->getCalls();
		$length = count($calledMethods);
		
		// last method called should've been GenericDao->save
		$result = $calledMethods[$length - 1]->getArg(0);
		
		$this->assertInstanceOf( "Hazard", $result, "Method should have saved a hazard." );
		$this->assertEquals( 1, $result->getKey_id(), "Saved hazard should have the same key id as passed in." );
		$this->assertEquals( 3, $result->getParent_hazard_id(), "Saved hazard's parent id should have changed to passed in argument" );
	}
	
	/**
	 * @group other
	 */
	public function test_moveHazardToParent_requestId() {
		$_REQUEST["hazardId"] = 1;
		$_REQUEST["parentHazardId"] = 3;
		
		// fake hazard to be used
		$fakeHazard = new Hazard();
		$fakeHazard->setKey_id(1);
		$fakeHazard->setParent_hazard_id(2);
		
		// set genericDao to return fake hazard instead of changing a real one
		$this->getDaoSpy(new Hazard())->overrideMethod("getById", $fakeHazard);
		
		$result = $this->actionManager->moveHazardToParent();
		// NOTE: as yet, moveHazard does not return anything after success,
		// only an empty string. Change as necessary later if method changes.
		
		// get methods of GenericDaoSpy that were called
		$calledMethods = $this->getDaoSpy(new Hazard())->getCalls();
		$length = count($calledMethods);
		
		// last method called should've been GenericDao->save
		$result = $calledMethods[$length - 1]->getArg(0);
		
		$this->assertInstanceOf( "Hazard", $result, "Method should have saved a hazard." );
		$this->assertEquals( 1, $result->getKey_id(), "Saved hazard should have the same key id as passed in." );
		$this->assertEquals( 3, $result->getParent_hazard_id(), "Saved hazard's parent id should have changed to passed in argument" );
	}
	

	/* getIsAlphabetized */
	
	/**
	 * @group other
	 */
	public function test_getIsAlphabetized_false() {
		$hazard1 = new Hazard();
		$hazard1->setName("goose");
		
		$hazard2 = new Hazard();
		$hazard2->setName("anchovies");
		
		$hazard3 = new Hazard();
		$hazard3->setName("meh");
		
		$result = $this->actionManager->getIsAlphabetized( array($hazard1, $hazard2, $hazard3) );
		$this->assertFalse( $result, "getIsAlphabetized returned false positive" );
	}

	/**
	 * @group other
	 */
	public function test_getIsAlphabetized_true() {
		$hazard1 = new Hazard();
		$hazard1->setName("Alpha");
		
		$hazard2 = new Hazard();
		$hazard2->setName("beta");
		
		$hazard3 = new Hazard();
		$hazard3->setName("qqqq");
		
		$result = $this->actionManager->getIsAlphabetized( array($hazard1, $hazard2, $hazard3) );
		$this->assertTrue( $result, "getIsAlphabetized returned false instead of true" );
	}
}

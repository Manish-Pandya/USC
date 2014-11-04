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

require_once(dirname(__FILE__) . '/TestRadiationActionFunctions.php');

// TODO reverse relationship - TestRadiationActionFunctions should extend this, not the other way around
// (it's just like that because TestRadiationActionFunctions was written first)

class TestActionManager extends TestRadiationActionFunctions {
	
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

	
	
	//TODO this one has custom logic, write sepparately
	/*
	public function test_getAllHazardsAsTree() {
		$result = $this->actionManager->getAllHazardsAsTree();

		$this->assertContainsOnlyInstancesOf( 'HazardsAsTre', $result );
		$this->assertCount( 5, $result );
	}
	*/

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
		$this->assertInstanceOf( 'UserBy', $result );
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
		$this->assertInstanceOf( 'UserBy', $result );
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
		$this->assertInstanceOf( 'RoleBy', $result );
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
		$this->assertInstanceOf( 'RoleBy', $result );
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
		$this->assertInstanceOf( 'ChecklistBy', $result );
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
		$this->assertInstanceOf( 'ChecklistBy', $result );
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
		$this->assertInstanceOf( 'HazardBy', $result );
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
		$this->assertInstanceOf( 'HazardBy', $result );
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
		$this->assertInstanceOf( 'QuestionBy', $result );
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
		$this->assertInstanceOf( 'QuestionBy', $result );
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
		$this->assertInstanceOf( 'PIBy', $result );
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
		$this->assertInstanceOf( 'PIBy', $result );
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
		$this->assertInstanceOf( 'RoomBy', $result );
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
		$this->assertInstanceOf( 'RoomBy', $result );
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
		$this->assertInstanceOf( 'DepartmentBy', $result );
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
		$this->assertInstanceOf( 'DepartmentBy', $result );
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
		$this->assertInstanceOf( 'BuildingBy', $result );
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
		$this->assertInstanceOf( 'BuildingBy', $result );
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
		$this->assertInstanceOf( 'DeficiencyBy', $result );
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
		$this->assertInstanceOf( 'DeficiencyBy', $result );
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
		$this->assertInstanceOf( 'InspectionBy', $result );
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
		$this->assertInstanceOf( 'InspectionBy', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	
	/* getDeficiencySelectionById */
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDeficiencySelectionById_noId() {
		$result = $this->actionManager->getDeficiencySelectionById();
	
		$this->assertInstanceOf( 'ActionError', $result );
		$this->assertEquals( 201, $result->getStatusCode() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDeficiencySelectionById_passId() {
	
		$result = $this->actionManager->getDeficiencySelectionById(1);
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'DeficiencySelectionBy', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	/**
	 * @group get
	 * @group byId
	 */
	public function test_getDeficiencySelectionById_requestId() {
		$_REQUEST['id'] = 1;
		$result = $this->actionManager->getDeficiencySelectionById();
	
		// make sure returned object has same type and key id
		$this->assertInstanceOf( 'DeficiencySelectionBy', $result );
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
		$this->assertInstanceOf( 'RecommendationBy', $result );
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
		$this->assertInstanceOf( 'RecommendationBy', $result );
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
		$this->assertInstanceOf( 'ObservationBy', $result );
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
		$this->assertInstanceOf( 'ObservationBy', $result );
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
		$this->assertInstanceOf( 'ResponseBy', $result );
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
		$this->assertInstanceOf( 'ResponseBy', $result );
		$this->assertEquals( 1, $result->getKey_id() );
	}
	
	

}
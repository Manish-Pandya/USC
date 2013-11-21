<?php
require_once(dirname(__FILE__) . '/../../simpletest/autorun.php');

require_once(dirname(__FILE__) . '/../../../src/Autoloader.php');
Logger::configure( dirname(__FILE__) . "/../../../etc/log4php-config.php");

//Include action functions to test
require_once(dirname(__FILE__) . '/../../../src/includes/action_functions.php');

class TestActionFunctions extends UnitTestCase {
	
	function tearDown(){
		foreach( $_REQUEST as $key=>$value ){
			unset( $_REQUEST[$key] );
		}
	}
	
	// getValueFromRequest
	
	public function test_getValueFromRequest_noValue_noParamValue(){
		$value = getValueFromRequest('DNE');
		$this->assertNull($value);
	}
	
	public function test_getValueFromRequest_noParamValue(){
		$_REQUEST['TestValue'] = "success";
		$value = getValueFromRequest('TestValue');
		$this->assertEqual("success", $value);
	}
	
	//activate
	
	public function test_activate(){
		//TODO: set request body?
	}
	
	//deactivate
	public function test_deactivate(){
		//TODO: set request body?
	}

	//getAllUsers
	public function test_getAllUsers(){
		$allusers = getAllUsers();
		
		foreach( $allusers as $user ){
			$this->assertTrue( $user instanceof User );
		}
	}
	
	//getUserById
	public function test_getUserById_noId(){
		$user = getUserById();
		$this->assertTrue( $user instanceof ActionError );
	}
	
	public function test_getUserById_passId(){
		$user = getUserById(5);
		$this->assertTrue( $user instanceof User );
		$this->assertEqual( $user->getKeyId(), 5);
	}
	
	public function test_getUserById_requestId(){
		$_REQUEST['id'] = 4;
		$user = getUserById();
		$this->assertTrue( $user instanceof User );
		$this->assertEqual( $user->getKeyId(), 4);
	}
	
	// saveUser
	public function test_saveUser(){
		
	}
	
	//getAllRoles
	public function test_getAllRoles(){
		$allroles = getAllRoles();
		$this->assertTrue( is_array( $allroles ) );
	}
	
	// getChecklistById
	public function test_getChecklistById_noId(){
		$checklist = getChecklistById();
		$this->assertTrue( $checklist instanceof ActionError );
	}
	
	public function test_getChecklistById_passId(){
		$checklist = getChecklistById(5);
		$this->assertTrue( $checklist instanceof Checklist );
		$this->assertEqual( $checklist->getKeyId(), 5);
	}
	
	public function test_getChecklistById_requestId(){
		$_REQUEST['id'] = 4;
		$checklist = getChecklistById();
		$this->assertTrue( $checklist instanceof Checklist );
		$this->assertEqual( $checklist->getKeyId(), 4);
	}
	
	//getAllQuestions
	public function test_getAllQuestions(){
		$questions = getAllQuestions();
		
		foreach( $questions as $question ){
			$this->assertTrue( $question instanceof Question );
		}
	}
	
	//TODO: saveChecklist
	//TODO: saveQuestion
	
	//getAllHazards
	public function test_getAllHazards(){
		$hazards = getAllHazards();
		
		foreach( $hazards as $hazard ){
			$this->assertTrue( $hazard instanceof Hazard );
		}
	}
	
	//getHazardById
	public function test_getHazardById_noId(){
		$hazard = getHazardById();
		$this->assertTrue( $hazard instanceof ActionError );
	}
	
	public function test_getHazardById_passId(){
		$hazard = getHazardById(5);
		$this->assertTrue( $hazard instanceof Hazard );
		$this->assertEqual( $hazard->getKeyId(), 5);
	}
	
	public function test_getHazardById_requestId(){
		$_REQUEST['id'] = 4;
		$hazard = getHazardById();
		$this->assertTrue( $hazard instanceof Hazard );
		$this->assertEqual( $hazard->getKeyId(), 4);
	}
	
	//TODO: moveHazardToParent
	public function test_moveHazardToParent_invalidIds(){
		$error = moveHazardToParent();
		$this->assertTrue( $error instanceof ActionError );
		
		$error = moveHazardToParent(5);
		$this->assertTrue( $error instanceof ActionError );
	}
	
	//TODO: saveHazard
	
	//getQuestionById
	public function test_getQuestionById_noId(){
		$question = getQuestionById();
		$this->assertTrue( $question instanceof ActionError );
	}
	
	public function test_getQuestionById_passId(){
		$question = getQuestionById(5);
		$this->assertTrue( $question instanceof Question );
		$this->assertEqual( $question->getKeyId(), 5);
	}
	
	public function test_getQuestionById_requestId(){
		$_REQUEST['id'] = 4;
		$question = getQuestionById();
		$this->assertTrue( $question instanceof Question );
		$this->assertEqual( $question->getKeyId(), 4);
	}
	
	//TODO: saveQuestionRelation
	//TODO: saveDeficiencyRelation
	//TODO: saveRecommendationRelation
	
	//getPI
	public function test_getPI_noId(){
		$pi = getPI();
		$this->assertTrue( $pi instanceof ActionError );
	}
	
	public function test_getPI_passId(){
		$pi = getPI(5);
		$this->assertTrue( $pi instanceof PrincipalInvestigator );
		$this->assertEqual( $pi->getKeyId(), 5);
	}
	
	public function test_getPI_requestId(){
		$_REQUEST['id'] = 4;
		$pi = getPI();
		$this->assertTrue( $pi instanceof PrincipalInvestigator );
		$this->assertEqual( $pi->getKeyId(), 4);
	}
	
	//getAllRooms
	public function test_getAllRooms(){
		$rooms = getAllRooms();
	
		foreach( $rooms as $room ){
			$this->assertTrue( $room instanceof Room );
		}
	}
	
	//getRoomById
	public function test_getRoomById_noId(){
		$room = getRoomById();
		$this->assertTrue( $room instanceof ActionError );
	}
	
	public function test_getRoomById_passId(){
		$room = getRoomById(5);
		$this->assertTrue( $room instanceof Room );
		$this->assertEqual( $room->getKeyId(), 5);
	}
	
	public function test_getRoomById_requestId(){
		$_REQUEST['id'] = 4;
		$room = getRoomById();
		$this->assertTrue( $room instanceof Room );
		$this->assertEqual( $room->getKeyId(), 4);
	}
	
	//getAllDepartments
	public function test_getAllDepartments(){
		$depts = getAllDepartments();
	
		foreach( $depts as $dept ){
			$this->assertTrue( $dept instanceof Department );
		}
	}
	
	//getDepartmentById
	public function test_getDepartmentById_noId(){
		$dept = getDepartmentById();
		$this->assertTrue( $dept instanceof ActionError );
	}
	
	public function test_getDepartmentById_passId(){
		$dept = getDepartmentById(5);
		$this->assertTrue( $dept instanceof Department );
		$this->assertEqual( $dept->getKeyId(), 5);
	}
	
	public function test_getDepartmentById_requestId(){
		$_REQUEST['id'] = 4;
		$dept = getDepartmentById();
		$this->assertTrue( $dept instanceof Department );
		$this->assertEqual( $dept->getKeyId(), 4);
	}
	
	//getAllBuildings
	public function test_getAllBuildings(){
		$buildings = getAllBuildings();
	
		foreach( $buildings as $building ){
			$this->assertTrue( $building instanceof Building );
		}
	}
	
	//getBuildingById
	public function test_getBuildingById_noId(){
		$building = getBuildingById();
		$this->assertTrue( $building instanceof ActionError );
	}
	
	public function test_getBuildingById_passId(){
		$building = getBuildingById(5);
		$this->assertTrue( $building instanceof Building );
		$this->assertEqual( $building->getKeyId(), 5);
	}
	
	public function test_getBuildingById_requestId(){
		$_REQUEST['id'] = 4;
		$building = getBuildingById();
		$this->assertTrue( $building instanceof Building );
		$this->assertEqual( $building->getKeyId(), 4);
	}
	
	//TODO: saveInspection
	
	//getHazardsInRoom
	public function test_getHazardsInRoom_noId(){
		$hazards = getHazardsInRoom();
		$this->assertTrue( $hazards instanceof ActionError );
	}

	public function test_getHazardsInRoom_passId(){
		$hazards = getHazardsInRoom(4);
	}

	public function test_getHazardsInRoom_requestId(){
		$_REQUEST['roomId'] = 4;
		$hazards = getHazardsInRoom();
		
		//Expect an array...
		$this->assertTrue( is_array($hazards) );
		
		//...of Hazards
		foreach($hazards as $hazard){
			$this->assertTrue( $hazard instanceof Hazard );
			
			//With the specified Room
			$rooms = $hazard->getRooms();
			$isInRoom4 = FALSE;
			foreach($rooms as $room){
				$isInRoom4 = $room->getKeyId() === 4;
				if( $isInRoom4 ){
					break;
				}
			}
			
			$this->assertTrue( $isInRoom4 );
		}
	}
	
	//TODO: saveHazardRelation
	//TODO: saveRoomRelation
	
	//getDeficiencyById
	public function test_getDeficiencyById_noId(){
		$deficiency = getDeficiencyById();
		$this->assertTrue( $deficiency instanceof ActionError );
	}
	
	public function test_getDeficiencyById_passId(){
		$deficiency = getDeficiencyById(5);
		$this->assertTrue( $deficiency instanceof Deficiency );
		$this->assertEqual( $deficiency->getKeyId(), 5);
	}
	
	public function test_getDeficiencyById_requestId(){
		$_REQUEST['id'] = 4;
		$deficiency = getDeficiencyById();
		$this->assertTrue( $deficiency instanceof Deficiency );
		$this->assertEqual( $deficiency->getKeyId(), 4);
	}
	
	//TODO: saveResponse
	//TODO: saveDeficiencySelection
	//TODO: saveRootCause
	//TODO: saveCorrectiveAction
	
	//getInspectionById
	public function test_getInspectionById_noId(){
		$inspection = getInspectionById();
		$this->assertTrue( $inspection instanceof ActionError );
	}
	
	public function test_getInspectionById_passId(){
		$inspection = getInspectionById(5);
		$this->assertTrue( $inspection instanceof Inspection );
		$this->assertEqual( $inspection->getKeyId(), 5);
	}
	
	public function test_getInspectionById_requestId(){
		$_REQUEST['id'] = 4;
		$inspection = getInspectionById();
		$this->assertTrue( $inspection instanceof Inspection );
		$this->assertEqual( $inspection->getKeyId(), 4);
	}
	
	//getDeficiencySelectionById
	public function test_getDeficiencySelectionById_noId(){
		$deficiencySelection = getDeficiencySelectionById();
		$this->assertTrue( $deficiencySelection instanceof ActionError );
	}
	
	public function test_getDeficiencySelectionById_passId(){
		$deficiencySelection = getDeficiencySelectionById(5);
		$this->assertTrue( $deficiencySelection instanceof DeficiencySelection );
		$this->assertEqual( $deficiencySelection->getKeyId(), 5);
	}
	
	public function test_getDeficiencySelectionById_requestId(){
		$_REQUEST['id'] = 4;
		$deficiencySelection = getDeficiencySelectionById();
		$this->assertTrue( $deficiencySelection instanceof DeficiencySelection );
		$this->assertEqual( $deficiencySelection->getKeyId(), 4);
	}
	
	// getDeficiencySelectionsForResponse
	public function test_getDeficiencySelectionsForResponse_noId(){
		$selections = getDeficiencySelectionsForResponse();
		$this->assertTrue( $selections instanceof ActionError );
	}
	
	public function test_getDeficiencySelectionsForResponse_passId(){
		$selections = getDeficiencySelectionsForResponse(4);
		
		//Expect array...
		$this->assertTrue( is_array( $selections) );
		
		//...Of DeficiencySelections
		foreach( $selections as $selection ){
			$this->assertTrue( $selection instanceof DeficiencySelection );
			//TODO: Check that response has ID 4
		}
	}

	public function test_getDeficiencySelectionsForResponse_requestId(){
		$_REQUEST['responseId'] = 4;
		$selections = getDeficiencySelectionsForResponse();
		
		//Expect array...
		$this->assertTrue( is_array( $selections) );
		
		//...Of DeficiencySelections
		foreach( $selections as $selection ){
			$this->assertTrue( $selection instanceof DeficiencySelection );
			//TODO: Check that response has ID 4
		}	
	}
	
	// getRecommendationById
	public function test_getRecommendationById_noId(){
		$recommendation = getRecommendationById();
		$this->assertTrue( $recommendation instanceof ActionError );
	}
	
	public function test_getRecommendationById_passId(){
		$recommendation = getRecommendationById(5);
		$this->assertTrue( $recommendation instanceof Recommendation );
		$this->assertEqual( $recommendation->getKeyId(), 5);
	}
	
	public function test_getRecommendationById_requestId(){
		$_REQUEST['id'] = 4;
		$recommendation = getRecommendationById();
		$this->assertTrue( $recommendation instanceof Recommendation );
		$this->assertEqual( $recommendation->getKeyId(), 4);
	}
	
	// getRecommendationsForResponse
	public function test_getRecommendationsForResponse_noId(){
		$recommendations = getRecommendationsForResponse();
		$this->assertTrue( $recommendations instanceof ActionError );
	}
	
	public function test_getRecommendationsForResponse_passId(){
		$recommendations = getRecommendationsForResponse(4);
		
		//Expect array...
		$this->assertTrue( is_array( $recommendations) );
		
		//...Of Recommendations
		foreach( $recommendations as $recommendation ){
			$this->assertTrue( $recommendation instanceof Recommendation );
			//TODO: Check that response has ID 4
		}
	}

	public function test_getRecommendationsForResponse_requestId(){
		$_REQUEST['responseId'] = 4;
		$recommendations = getRecommendationsForResponse();
		
		//Expect array...
		$this->assertTrue( is_array( $recommendations) );
		
		//...Of Recommendations
		foreach( $recommendations as $recommendation ){
			$this->assertTrue( $recommendation instanceof Recommendation );
			//TODO: Check that response has ID 4
		}	
	}
	
	// getResponseById
	public function test_getResponseById_noId(){
		$response = getResponseById();
		$this->assertTrue( $response instanceof ActionError );
	}
	
	public function test_getResponseById_passId(){
		$response = getResponseById(5);
		$this->assertTrue( $response instanceof Response );
		$this->assertEqual( $response->getKeyId(), 5);
	}
	
	public function test_getResponseById_requestId(){
		$_REQUEST['id'] = 4;
		$response = getResponseById();
		$this->assertTrue( $response instanceof Response );
		$this->assertEqual( $response->getKeyId(), 4);
	}
	
	
	// getResponsesForInspection
	public function test_getResponsesForInspection_noId(){
		$responses = getResponsesForInspection();
		$this->assertTrue( $responses instanceof ActionError );
	}
	
	public function test_getResponsesForInspection_passId(){
		$responses = getResponsesForInspection(4);
		
		//Expect array...
		$this->assertTrue( is_array( $responses) );
		
		//...Of Responses
		foreach( $responses as $response ){
			$this->assertTrue( $response instanceof Response );
			//TODO: Check that inspection has ID 4
		}
	}

	public function test_getResponsesForInspection_requestId(){
		$_REQUEST['inspectionId'] = 4;
		$responses = getResponsesForInspection();
		
		//Expect array...
		$this->assertTrue( is_array( $responses) );
		
		//...Of Responses
		foreach( $responses as $response ){
			$this->assertTrue( $response instanceof Response );
			//TODO: Check that inspection has ID 4
		}	
	}
}

?>
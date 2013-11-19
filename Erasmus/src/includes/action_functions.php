<?php 
/*
 * This file is responsible for providing functions for Action calls,
 * and should not execute any code upon inclusion.
 * 
 * Because this file merely provides the functions, they are easily testable
 * 
 * If an error should occur, Action functions should return either NULL or
 * an instance of ActionError. Returning an ActionError allows the function
 * to provide additional information about the error.
 */
?><?php
//TODO: Split these functions up into further includes?

/**
 * Chooses a return value based on the parameters. If $paramValue
 * is specified, it is returned. Otherwise, $valueName is taken from $_REQUEST.
 * 
 * If $valueName is not present in $_REQUEST, NULL is returned.
 * 
 * @param unknown $valueName
 * @param string $paramValue
 * @return string|unknown|NULL
 */
function getValueFromRequest( $valueName, $paramValue = NULL ){
	if( $paramValue !== NULL ){
		return $paramValue;
	}
	else if( array_key_exists($valueName, $_REQUEST)){
		return $_REQUEST[ $valueName ];
	}
	else{
		return NULL;
	}
}

function loginAction(){ };
function logoutAction(){ };

function activate(){
	//Get the user
	$LOG = Logger::getLogger('Action:activate');
	$decodedObject = convertInputJson();
	if( $decodedObject == NULL ){
		return new ActionError('Error converting input stream to GenericCrud');
	}
	else{
		$decodedObject->setIsActive(TRUE);
		return $decodedObject;
	}
};

function deactivate(){
	//Get the user
	$LOG = Logger::getLogger('Action:deactivate');
	$decodedObject = convertInputJson();
	if( $decodedObject == NULL ){
		return new ActionError('Error converting input stream to GenericCrud');
	}
	else{
		$decodedObject->setIsActive(FALSE);
		return $decodedObject;
	}
};

// Users Hub
function getAllUsers(){
	$LOG = Logger::getLogger( 'Action:getAllUsers' );
	$allUsers = array();
	
	//TODO: Query for Users
	for( $i = 0; $i < 10; $i++ ){
		$allUsers[] = getUserById($i);
	}
	
	return $allUsers;
};

function getUserById( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:getUserById' );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$keyid = $id;
	
		//TODO: query for User with this ID
		$user = new User();
		$user->setIsActive(TRUE);
		$user->setEmail("user$keyid@host.com");
		$user->setName("User #$keyid");
		$user->setUsername("user$keyid");
		$user->setKeyId($keyid);

		$LOG->info("Defined User: $user");
	
		return $user;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
}

//TODO: Remove this utility function
function convertInputJson($addKeyId=FALSE){
	try{
		$decodedObject = JsonManager::decodeInputStream();
		
		if( $decodedObject != NULL ){
			//set key id?
			if( $addKeyId ){
				$decodedObject->setKeyId(54321);
			}
		}
		else{
			return new ActionError('No data read from input stream');
		}
		
		return $decodedObject;
	}
	catch(Exception $e){
		return new ActionError("Unable to decode JSON. Cause: $e");
	}
}

function saveUser(){
	$LOG = Logger::getLogger('Action:saveUser');
	$decodedObject = convertInputJson(true);
	if( $decodedObject == NULL ){
		return new ActionError('Error converting input stream to User');
	}
	else{
		return $decodedObject;
	}
};

function getAllRoles(){
	return array(
		'Administrator',
		'AppUser',
	);
};

// Checklist Hub
function getChecklist(){
	if( array_key_exists( 'id', $_REQUEST)){
		$keyid = $_REQUEST['id'];
	
		//TODO: query for Checklist with this ID
		$checklist = new Checklist();
		$checklist->setKeyId($keyid);
	
		return $checklist;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function getQuestions(){
	$LOG = Logger::getLogger( 'Action:getQuestions' );
	$questions = array();
	
	//TODO: Query for Rooms
	for( $i = 0; $i < 10; $i++ ){
		$question = new Question();
		$question->setIsActive(TRUE);
		$question->setKeyId($i);
		$question->setText("Question $i");
		$question->setStandardsAndGuidelines('Standards & Guidelines');
	
		$LOG->info("Defined Question: $question");
	
		$questions[] = $question;
	}
	
	return $questions;
};

function saveChecklist(){
	$LOG = Logger::getLogger('Action:saveChecklist');
	$decodedObject = convertInputJson(true);
	if( $decodedObject == NULL ){
		return new ActionError('Error converting input stream to Checklist');
	}
	else{
		return $decodedObject;
	}
};

function saveQuestion(){
	$LOG = Logger::getLogger('Action:saveQuestion');
	$decodedObject = convertInputJson(true);
	if( $decodedObject == NULL ){
		return new ActionError('Error converting input stream to Question');
	}
	else{
		return $decodedObject;
	}
};

// Hazards Hub
function getAllHazards(){
	$LOG = Logger::getLogger( 'Action:getAllHazards' );
	$hazards = array();
	
	//TODO: Query for Hazards
	for( $i = 0; $i < 10; $i++ ){
		$hazards[] = getHazardById($i);
	}
	
	return $hazards;
};

function getHazardById( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:getHazardById' );
	
	if( $id === NULL ){
		$LOG->info('NULL parameter $id: ' . $id);
		$id = $_REQUEST['id'];
	}
	
	if( $id !== NULL ){
		$keyid = $id;
		
		//TODO: query for hazard with this ID
		$hazard = new Hazard();
		$hazard->setKeyId($keyid);
		$hazard->setName("Dangerous thing #$keyid");
		
		//build subhazard
		$subhazard = new Hazard();
		$subhazard->setKeyId("$keyid$keyid");
		$subhazard->setName("Dangerous thing #" . $subhazard->getKeyId());
		
		$subhazard->setParentHazardId($hazard->getKeyId());
		
		$hazard->setSubHazards( array( $subhazard) );
		
		$LOG->info("Defined Hazard: $hazard");
		
		return $hazard;
	}
	else{
		return new ActionError("No request parameter 'id' was provided");
	}
}

/**
 * Moves specified hazard to the specified parent
 */
function moveHazardToParent($hazardId = NULL, $parentHazardId = NULL){
	$LOG = Logger::getLogger( 'Action:moveHazardToParent' );
	
	//Get ids
	$hazardId = getValueFromRequest('hazardId', $hazardId);
	$parentHazardId = getValueFromRequest('parentHazardId', $parentHazardId);
	
	//TODO: validate values
	
	$LOG->info("Moving Hazard #$hazardId to new parent Hazard #$parentHazardId");
	
	// get Hazard by ID
	$hazard = getHazardById( $hazardId );
	
	// get Parent Hazard by ID
	$parent = getHazardById( $parentHazardId );
	
	//TODO: Remove $hazard from $hazard->getParentHazard() children
	
	//Get children of parent
	$children = $parent->getSubHazards();
	
	//Add hazard to children
	$children[] = $hazard;
	
	$parent->setSubHazards($children);
	
	//TODO: What do we return?
}

function saveHazard(){
	$LOG = Logger::getLogger('Action:saveHazard');
	$decodedObject = convertInputJson(true);
	if( $decodedObject == NULL ){
		return new ActionError('Error converting input stream to Hazard');
	}
	else{
		return $decodedObject;
	}
};
//function saveChecklist(){ };	//DUPLICATE FUNCTION

// Question Hub
function getQuestionById(){
	if( array_key_exists( 'id', $_REQUEST)){
		$keyid = $_REQUEST['id'];
	
		//TODO: query for Question with this ID
		$question = new Question();
		$question->setKeyId($keyid);
		$question->setText('What?');
		$question->setStandardsAndGuidelines('Because.');
	
		return $question;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function saveQuestionRelation(){ };
function saveDeficiencyRelation(){ };
function saveRecommendationRelation(){ };

// Inspection, step 1 (PI / Room assessment)
function getPI(){
	if( array_key_exists( 'id', $_REQUEST)){
		$keyid = $_REQUEST['id'];
		
		//TODO: query for PI with this ID
		$pi = new PrincipalInvestigator();
		$pi->setKeyId($keyid);
		$pi->setUser( getUserById( $keyid ) );
		
		return $pi;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function getRooms(){
	$LOG = Logger::getLogger( 'Action:getRooms' );
	$allRooms = array();
	
	//TODO: Query for Rooms
	for( $i = 100; $i < 110; $i++ ){
		$room = new Room();
		$room->setIsActive(TRUE);
		$room->setKeyId($i);
		$room->setName("Room $i");
		$room->setSafetyContactInformation('Call 911');
	
		$LOG->info("Defined Room: $room");
	
		$allRooms[] = $room;
	}
	
	return $allRooms;
};

function saveInspection(){
	$LOG = Logger::getLogger('Action:saveInspection');
	$decodedObject = convertInputJson(true);
	if( $decodedObject == NULL ){
		return new ActionError('Error converting input stream to Inspection');
	}
	else{
		return $decodedObject;
	}
};

// Inspection, step 2 (Hazard Assessment)
function getHazardsInRoom(){
	if( array_key_exists( 'id', $_REQUEST)){
		$roomId = $_REQUEST['id'];
		
		//TODO: get Room
		$room = new Room();
		$room->setKeyId($roomId);
		$room->setName("Room $roomId");
		$hazards = getHazards();
		
		foreach( $hazards as &$hazard){
			$hazard->setRooms( array($room) );
		}
	
		return $hazards;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function saveHazardRelation(){ };
function saveRoomRelation(){ };

// Inspection, step 3 (Checklist)
//function getQuestions(){ };	//DUPLICATE FUNCTION
function getDeficiencyById( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:getDeficiencyById' );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$keyid = $id;
	
		//TODO: query for Inspection with the specified ID
		$deficiency = new Deficiency();
		$deficiency->setIsActive(True);
		$deficiency->setKeyId($keyid);
		$deficiency->setText("Deficiency #$keyid");
	
		$LOG->info("Defined Deficiency: $deficiency");
	
		return $deficiency;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function saveResponse(){
	$LOG = Logger::getLogger('Action:saveResponse');
	$decodedObject = convertInputJson(true);
	if( $decodedObject == NULL ){
		return new ActionError('Error converting input stream to Response');
	}
	else{
		return $decodedObject;
	}
};

function saveDeficiencySelection(){
	$LOG = Logger::getLogger('Action:saveDeficiencySelection');
	$decodedObject = convertInputJson(true);
	if( $decodedObject == NULL ){
		return new ActionError('Error converting input stream to DeficiencySelection');
	}
	else{
		return $decodedObject;
	}
};
function saveRootCause(){
	$LOG = Logger::getLogger('Action:saveRootCause');
	$decodedObject = convertInputJson(true);
	if( $decodedObject == NULL ){
		return new ActionError('Error converting input stream to RootCause');
	}
	else{
		return $decodedObject;
	}
};
function saveCorrectiveAction(){
	$LOG = Logger::getLogger('Action:saveRootCause');
	$decodedObject = convertInputJson(true);
	if( $decodedObject == NULL ){
		return new ActionError('Error converting input stream to RootCause');
	}
	else{
		return $decodedObject;
	}
};

function getInspectionById( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:getInspectionById' );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$keyid = $id;
	
		//TODO: query for Inspection with the specified ID
		$inspection = new Inspection();
		$inspection->setIsActive(True);
		$inspection->setKeyId($keyid);
		
		$inspection->setResponses( getResponsesForInspection($keyid) );
	
		$LOG->info("Defined Inspection: $inspection");
	
		return $inspection;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getDeficiencySelectionById( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:getDeficiencySelectionById' );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$keyid = $id;
	
		//TODO: query for DeficiencySelection with the specified ID
		$selection = new DeficiencySelection();
		$selection->setIsActive(True);
		$selection->setKeyId($keyid);
	
		$LOG->info("Defined $recommendation: $selection");
	
		return $selection;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
}

// Inspection, step 4 (Review, deficiency report)
function getDeficiencySelectionsForResponse( $responseId = NULL){
	$responseId = getValueFromRequest('responseId', $responseId);
	
	if( $responseId !== NULL ){
		$selections = array();
		
		for( $i = 0; $i < 2; $i++ ){
			$selections[] = getDeficiencySelectionById($i);
		}
		
		return $selections;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function getRecommendationById( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:getRecommendationById' );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$keyid = $id;
	
		//TODO: query for DeficiencySelection with the specified ID
		$recommendation = new Recommendation();
		$recommendation->setIsActive(True);
		$recommendation->setKeyId($keyid);
	
		$LOG->info("Defined Recommendation: $recommendation");
	
		return $recommendation;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getRecommendationsForResponse( $responseId = NULL ){
	//get Recommendations for Response
	
	$responseId = getValueFromRequest('responseId', $responseId);
	
	if( $responseId !== NULL ){
		$recommendations = array();
		
		for( $i = 0; $i < 2; $i++ ){
			$recommendations[] = getRecommendationById($i);
		}
		
		return $recommendations;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

//TODO: remove HACK specifying inspection ID 
function getResponseById( $id = NULL, $inspectionId = NULL ){
	$LOG = Logger::getLogger( 'Action:getResponseById' );
	
	$id = getValueFromRequest('id', $id);
	
	//TODO: Externalize these to constants?
	$POSSIBLE_ANSWERS = array('Yes', 'No', 'NotApplicable', 'NoResponse' );
	
	if( $id !== NULL ){
		$keyid = $id;
	
		//TODO: query for Response with the specified ID
		$response = new Response();
		$response->setIsActive(True);
		$response->setKeyId($keyid);
		$response->setAnswer( $POSSIBLE_ANSWERS[array_rand($POSSIBLE_ANSWERS)] );
		
		$response->setInspectionId( $inspectionId );
		$response->setDeficiencySelections( getDeficiencySelectionsForResponse($keyid) );
		$response->setQuestion( getQuestionById( "$keyid$keyid") );
		$response->setRecommendations( getRecommendationsForResponse($keyid) );
		
		$LOG->info("Defined Response: $response");
	
		return $response;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
}

// Inspection, step 5 (Details, Full Report)
function getResponsesForInspection( $inspectionId = NULL){
	//Get responses for Inspection
	$LOG = Logger::getLogger( 'Action:getResponsesForInspection' );

	$inspectionId = getValueFromRequest('inspectionId', $inspectionId);
	
	if( $inspectionId !== NULL ){
		$keyid = $inspectionId;
	
		//TODO: query for Responses with the specified Inspection ID
		$responses = array();
		for( $i = 0; $i < 5; $i++ ){
			$responses[] = getResponseById($i, $keyid);
		}
	
		return $responses;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};
?>
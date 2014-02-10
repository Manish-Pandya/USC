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

function getDao( $modelObject = NULL ){
	//FIXME: Remove MockDAO
	if( $modelObject === NULL ){
		return new MockDAO();
	}
	else{
		return new GenericDAO( $modelObject );		
	}
}

function loginAction(){ };
function logoutAction(){ };

function activate(){
	//Get the user
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to GenericCrud');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$decodedObject->setIsActive(TRUE);
		$dao = getDao();
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function deactivate(){
	//Get the user
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to GenericCrud');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$decodedObject->setIsActive(FALSE);
		$dao = getDao();
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

// Users Hub
function getAllUsers(){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	$userDao = getDao( new User() );
	$allUsers = $userDao->getAll();
	
	return $allUsers;
};

function getUserById( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao(new User());
		return $dao->getById($id);
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
}

//TODO: Remove this utility function
function convertInputJson(){
	try{
		$decodedObject = JsonManager::decodeInputStream();
		
		if( $decodedObject === NULL ){
			return new ActionError('No data read from input stream');
		}
		
		return $decodedObject;
	}
	catch(Exception $e){
		return new ActionError("Unable to decode JSON. Cause: $e");
	}
}

function saveUser(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to User');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao();
		$dao->save( $decodedObject );
		return $decodedObject;
	}
};

function getAllRoles(){
	$dao = getDao();
	return $dao->getAllRoles();
};

// Checklist Hub
function getChecklistById( $id = NULL ){
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao();
		return $dao->getChecklistById($id);
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function getAllQuestions(){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$questions = array();
	
	$dao = getDao();
	
	for( $i = 0; $i < 10; $i++ ){
		$question = $dao->getQuestionById($i);
		$questions[] = $question;
	}
	
	return $questions;
};

function saveChecklist(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Checklist');
	}
	else{
		$dao = getDao();
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function saveQuestion(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Question');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao();
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

// Hazards Hub
function getAllHazardsAsTree() {
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$dao = getDao();
	$hazards = $dao->getAllHazards();
	
	return $hazards;
}

function getAllHazards(){
	//FIXME: This function should return a FLAT COLLECTION of ALL HAZARDS; not a Tree
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$dao = getDao();
	$hazards = $dao->getAllHazards();
	
	return $hazards;
};

//FIXME: Remove $name
function getHazardById( $id = NULL, $name = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao();
		$hazard = $dao->getHazardById($id, $name);
		
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
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	//Get ids
	$hazardId = getValueFromRequest('hazardId', $hazardId);
	$parentHazardId = getValueFromRequest('parentHazardId', $parentHazardId);
	
	//validate values
	if( $hazardId === NULL || $parentHazardId === NULL ){		
		return new ActionError("Invalid Hazard IDs specified: hazardId=$hazardId parentHazardId=$parentHazardId");
	}
	else{
		$LOG->debug("Moving Hazard #$hazardId to new parent Hazard #$parentHazardId");
		
		$dao = getDao();
		
		// get Hazard by ID
		$hazard = getHazardById( $hazardId );
		$LOG->trace("Loaded Hazard to move: $hazard");
		
		// get Parent Hazard by ID
		$newParent = getHazardById( $parentHazardId );
		$LOG->trace("Loaded New Parent Hazard: $newParent");
		
		// get old Parent Hazard by ID
		$oldParent = getHazardById( $hazard->getParentHazardId() );
		$LOG->trace("Loaded Old Parent Hazard: $oldParent");
		
		//Get children of parent
		$children = $newParent->getSubHazards();
		
		//Add hazard to children
		$children[] = $hazard;
		
		$newParent->setSubHazards($children);
		
		// Save
		$dao->save($hazard);
		$dao->save($newParent);
		
		// Remove $hazard from old parent's children
		if( $oldParent !== NULL && !($oldParent instanceof ActionError) ){
			$LOG->debug("Removing Hazard #$hazardId from old parent Hazard #$parentHazardId");
			$siblings = $oldParent->getSubHazards();
			foreach( $siblings as $key => $sibling ){
				if( $sibling === $hazard ){
					unset( $siblings[ $key ] );
				}
			}
			
			$oldParent->setSubHazards($siblings);
			$dao->save($oldParent);
		}
		
		//TODO: What do we return?
		$LOG->info("Moved Hazard #$hazardId to new parent Hazard #$parentHazardId");
		return '';
	}
}

function saveHazard(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Hazard');
	}
	else if( $decodedObject instanceof ActionError ){
		return $decodedObject;
	}
	else{
		$dao = getDao();
		$dao->save($decodedObject);
		return $decodedObject;
	}
};
//function saveChecklist(){ };	//DUPLICATE FUNCTION

// Question Hub
function getQuestionById( $id = NULL ){
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao();
		return $dao->getQuestionById($id);
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function saveQuestionRelation(){ };
function saveDeficiencyRelation(){ };
function saveRecommendationRelation(){ };

function getInspector( $id = NULL ){
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao();
		return $dao->getInspectorById($id);
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getAllInspectors(){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$inspectors = array();

	$dao = getDao();
	for( $i = 0; $i < 10; $i++ ){
		$inspectors[] = $dao->getInspectorById($i);
	}
	
	return $inspectors;
};

// Inspection, step 1 (PI / Room assessment)
function getPI( $id = NULL ){
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao();
		return $dao->getPiById($id);
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function getAllPIs(){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$pis = array();

	$dao = getDao();
	for( $i = 0; $i < 10; $i++ ){
		$pis[] = $dao->getPiById($i);
	}
	
	return $pis;
};

function getAllRooms(){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$allRooms = array();
	
	$dao = getDao();
	for( $i = 100; $i < 110; $i++ ){
		$room = $dao->getRoomById($i);
		$allRooms[] = $room;
	}
	
	return $allRooms;
};

function getRoomById( $id = NULL ){
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao();
		return $dao->getRoomById($id);
	}
	else{
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getAllDepartments(){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$allDepartments = array();

	$dao = getDao();
	for( $i = 1; $i < 11; $i++ ){
		$dept = $dao->getDepartmentById($i);
		$allDepartments[] = $dept;
	}

	return $allDepartments;
};

function getDepartmentById( $id = NULL ){
	$id = getValueFromRequest('id', $id);

	if( $id !== NULL ){
		$dao = getDao();
		return $dao->getDepartmentById($id);
	}
	else{
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getAllBuildings( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$allBuildings = array();
	
	$dao = getDao();
	for( $i = 1; $i < 11; $i++ ){
		$building = $dao->getBuildingById($i);
		$allBuildings[] = $building;
	}
	
	return $allBuildings;
}

function getBuildingById( $id = NULL ){
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao();
		return $dao->getBuildingById($id);
	}
	else{
		return new ActionError("No request parameter 'id' was provided");
	}
}

function saveInspection(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Inspection');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao();
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

// Inspection, step 2 (Hazard Assessment)

/**
 * Builds an associative array mapping Hazard IDs to the rooms
 * that contain them. The listed rooms are limited by the Room IDs
 * given as a CSV parameter
 *  
 * @param string $roomIds
 * @return Associative array: [Hazard KeyId] => array( stdClass(key_id, hazard_name, roomIds)
 */
function getHazardRoomMappingsAsTree( $roomIds = NULL ){
	//TODO: Logging
	$roomIdsCsv = getValueFromRequest('roomIds', $roomIds);
	
	if( $roomIdsCsv !== NULL ){
		//Split CSV
		$roomIds = explode(',', $roomIdsCsv);
		
		//Prepare array-map for hazard rooms
		$hazardToRoomsMap = array();
		
		//Get all hazards
		$allHazards = getAllHazardsAsTree();
		
		foreach( $allHazards as $hazard ){
			$hazardNodeDto = getHazardRoomMappings($hazard, $roomIds);
			
			$hazardToRoomsMap[$hazardNodeDto->getKey_Id()] = $hazardNodeDto;
		}
		
		return $hazardToRoomsMap;
	}
	else{
		//error
		return new ActionError("No request parameter 'roomIds' was provided");
	}
}

//UTILITY FUNCTION FOR getHazardRoomMappingsAsTree
function getHazardRoomMappings($hazard, $searchRoomids){
	$relevantRooms = array();
		
	//Get the hazard's rooms
	$hazardRooms = $hazard->getRooms();
		
	//Check if this hazard is in a room we want
	foreach ( $hazardRooms as $room ){
		if( array_key_exists($room->getKey_Id(), $searchRoomids) ){
			//Add key to relevant array
			$relevantRooms[] = $room->getKey_Id();
		}
	}

	//Build nodes for sub-hazards
	$subHazardNodeDtos = array();
	foreach( $hazard->getSubHazards() as $subHazard ){
		$node = getHazardRoomMappings($subHazard, $searchRoomids);
		$subHazardNodeDtos[$node->getKey_Id()] = $node;
	}
	
	//Build the node for this hazard
	$hazardDto = new HazardTreeNodeDto(
		$hazard->getKey_Id(),
		$hazard->getName(),
		$relevantRooms,
		$subHazardNodeDtos
	);
		
	//Return this node
	return $hazardDto;
}

function getHazardsInRoom( $roomId = NULL ){
	
	$roomId = getValueFromRequest('roomId', $roomId);
	
	if( $roomId !== NULL ){
		$roomId = $roomId;
		
		$dao = getDao();
		
		//get Room
		$room = $dao->getRoomById($roomId);
		
		//get hazards
		$hazards = getAllHazards();
		
		// Set room in each hazard
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
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao();
		$keyid = $id;
	
		// query for Inspection with the specified ID
		return $dao->getDeficiencyById($id);
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function saveResponse(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Response');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao();
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function saveDeficiencySelection(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to DeficiencySelection');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao();
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function saveRootCause(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to RootCause');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao();
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function saveCorrectiveAction(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to RootCause');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao();
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function getChecklistsForInspection( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		//TODO: get inspection
		$inspection = getInspectionById($id);
		
		//TODO: get Responses
		$responses = $inspection->getResponses();
		
	}
	else{
		//error
		return new ActionError('No request parameter "id" was provided');
	}
}

function getInspectionById( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao();
		
		//get inspection
		$inspection = $dao->getInspectionById($id);
		
		// get responses
		$inspection->setResponses( getResponsesForInspection($id) );
	
		return $inspection;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getDeficiencySelectionById( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao();
		return $dao->getDeficiencySelectionById($id);
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
			$selection = getDeficiencySelectionById($i);
			//TODO: set response ID?
			$selections[] = $selection;
		}
		
		return $selections;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

//TODO: Observations?

function getRecommendationById( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao();
		return $dao->getRecommendationById($id);
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
			$recommendation = getRecommendationById($i);
			//TODO: set response?
			$recommendations[] = $recommendation;
		}
		
		return $recommendations;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function getObservationById( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$id = getValueFromRequest('id', $id);

	if( $id !== NULL ){
		$dao = getDao();
		return $dao->getObservationById($id);
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getObservationsForResponse( $responseId = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	//get Observations for Response

	$responseId = getValueFromRequest('responseId', $responseId);

	if( $responseId !== NULL ){
		$LOG->debug("Generating Observations for response #$responseId");
		$observations = array();

		for( $i = 0; $i < 2; $i++ ){
			$observation = getObservationById($i);
			$observations[] = $observation;
		}

		return $observations;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

//TODO: remove HACK specifying inspection ID 
function getResponseById( $id = NULL, $inspectionId = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao();
		$response = $dao->getResponseById($id);
		
		$response->setInspectionId( $inspectionId );
		$response->setDeficiencySelections( getDeficiencySelectionsForResponse($id) );
		$response->setQuestion( getQuestionById( "$id$id") );
		$response->setRecommendations( getRecommendationsForResponse($id) );
		$response->setObservations( getObservationsForResponse($id) );
		
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
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$inspectionId = getValueFromRequest('inspectionId', $inspectionId);
	
	if( $inspectionId !== NULL ){
		$keyid = $inspectionId;
	
		//TODO: query for Responses with the specified Inspection ID
		$responses = array();
		for( $i = 0; $i < 5; $i++ ){
			$response = getResponseById($i, $keyid);
			//TODO: set Inspection?
			$responses[] = $response;
		}
	
		return $responses;
	}
	else{
		//error
		return new ActionError("No request parameter 'inspectionId' was provided");
	}
};
?>
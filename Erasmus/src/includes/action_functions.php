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
		$dao = getDao(new Checklist());
		return $dao->getById($id);
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function getChecklistByHazardId( $id = NULL ){
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao(new Hazard());
		$hazard = $dao->getById($id);
		return $hazard->getChecklist();
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function getAllQuestions(){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$questions = array();
	
	$dao = getDao(new Question());
	
		$questions = $dao->getAll();
	
	return $questions;
};

function saveChecklist(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Checklist');
	}
	else{
		$dao = getDao(new Checklist());
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
		$dao = getDao(new Question());
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function saveDeficiency(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Deficiency');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao(new Deficiency());
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function saveObservation(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Observation');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao(new Observation());
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function saveRecommendation(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Recommendation');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao(new Recommendation());
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function saveSupplementalObservation(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to SupplementalObservation');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao(new SupplementalObservation());
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function saveSupplementalRecommendation(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to SupplementalRecommendation');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao(new SupplementalRecommendation());
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

// Hazards Hub
function getAllHazardsAsTree() {
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$dao = getDao(new Hazard());
	// get the Root of the hazard tree
	$root = $dao->getById(10000);

	// Define which subentities to load
	$entityMaps = array();
	$entityMaps[] = new EntityMap("eager","getSubhazards");
	$entityMaps[] = new EntityMap("lazy","getChecklist");
	$entityMaps[] = new EntityMap("lazy","getRooms");
	$entityMaps[] = new EntityMap("lazy","getInspectionRooms");
	$root->setEntityMaps($entityMaps);
	
	// Return the object
	return $root;
}

function getAllHazards(){
	//FIXME: This function should return a FLAT COLLECTION of ALL HAZARDS; not a Tree
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$dao = getDao(new Hazard());
	$hazards = $dao->getAll();
	
	return $hazards;
};

//FIXME: Remove $name
function getHazardById( $id = NULL, $name = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao(new Hazard());
		$hazard = $dao->getById($id);
		
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
		
		$dao = getDao(new Hazard());
		
		// get Hazard by ID
		$hazard = getHazardById( $hazardId );
		$LOG->trace("Loaded Hazard to move: $hazard");
		
		$hazard->setParent_hazard_id=$parentHazardId;		
		// Save

		$dao->save($hazard);
		
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
		$dao = getDao(new Hazard());
		$dao->save($decodedObject);
		return $decodedObject;
	}
};
//function saveChecklist(){ };	//DUPLICATE FUNCTION

// Question Hub
function getQuestionById( $id = NULL ){
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao(new Question());
		return $dao->getById($id);
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
		$dao = getDao(new Inspector());
		return $dao->getById($id);
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getAllInspectors(){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$dao = getDao(new Inspector());

	return $dao->getAll();
};

// Inspection, step 1 (PI / Room assessment)
function getPI( $id = NULL ){
	
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao(new PrincipalInvestigator());
		return $dao->getById($id);
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function getAllPIs(){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$dao = getDao(new PrincipalInvestigator());
	
	return $dao->getAll();
};

function getAllRooms(){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	$dao = getDao(new Room());
	
	return $dao->getAll();
};

function getRoomById( $id = NULL ){
	$id = getValueFromRequest('id', $id);
	
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$LOG->trace('getting room');
	
	if( $id !== NULL ){
		$dao = getDao(new Room());
		return $dao->getById($id);
	}
	else{
		return new ActionError("No request parameter 'id' was provided");
	}
}

//Get a room dto duple
function getRoomDtoByRoomId( $id = NULL, $roomName = null, $containsHazard = null, $isAllowed = null ) {
	$id = getValueFromRequest('id', $id);

	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$LOG->trace('getting room');

	if( $id !== NULL ){
		$dao = getDao();
		$room = $dao->getRoomById($id);

		$roomDto = new RoomDto($room->getKey_Id(), $room->getName(), $containsHazard, $isAllowed);

		return $roomDto;
	}
	else{
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getAllDepartments(){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$dao = getDao(new Department());

	return $dao->getAll();
};

function getDepartmentById( $id = NULL ){
	$id = getValueFromRequest('id', $id);

	if( $id !== NULL ){
		$dao = getDao(new Department());
		return $dao->getById($id);
	}
	else{
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getAllBuildings( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	
	$dao = getDao(new Building());
	
	return $dao->getAll();
}

function getBuildingById( $id = NULL ){
	$id = getValueFromRequest('id', $id);
	
	if( $id !== NULL ){
		$dao = getDao(new Building());
		return $dao->getById($id);
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
		$dao = getDao(new Inspection());
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
 * @return Associative array: [Hazard KeyId] => array( HazardTreeNodeDto )
 */
function getHazardRoomMappingsAsTree( $roomIds = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$roomIdsCsv = getValueFromRequest('roomIds', $roomIds);
	
	if( $roomIdsCsv !== NULL ){
		$LOG->debug("Retrieving Hazard-Room mappings for Rooms: $roomIdsCsv");
		
		
		$LOG->debug('Identified ' . count($roomIdsCsv) . ' Rooms');
	
		//Get all hazards
		$allHazards = getAllHazardsAsTree();
		
		$rooms = array();
		$roomDao = getDao(new Room());
		
		// Create an array of Room Objects
		foreach($roomIdsCsv as $roomId) {
			array_push($rooms,$roomDao->getById($roomId));
		}
		
		// filter by room
		filterHazards($allHazards,$rooms);
		
		return $allHazards;
	}
	else{
		//error
		return new ActionError("No request parameter 'roomIds' was provided");
	}
}

function filterHazards (&$hazard, $rooms){
	foreach ($hazard->getSubhazards() as $subhazard){
		filterHazards($subhazard, $rooms);
		$subhazard->setInspectionRooms($rooms);
		$subhazard->filterRooms();
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getSubhazards");
		$entityMaps[] = new EntityMap("lazy","getChecklist");
		$entityMaps[] = new EntityMap("lazy","getRooms");
		$entityMaps[] = new EntityMap("eager","getInspectionRooms");
		$subhazard->setEntityMaps($entityMaps);
	}
}

//UTILITY FUNCTION FOR getHazardRoomMappingsAsTree
function getHazardRoomMappings($hazard, $rooms, $searchRoomIds, $parentIds = null){
	$searchRoomIds = $searchRoomIds;
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$LOG->trace("Getting room mappings for $hazard");
	$relevantRooms = array();
		
	$hazardRooms = $hazard->getRooms();
	//Check if this hazard is in a room we want
	foreach ( $rooms as $key=>$room ){
		if( in_array($room, $hazardRooms) ){
			$LOG->debug("$hazard is in $room");
			$room->setContainsHazard(true);			
		}else{
			$LOG->debug("$hazard is NOT in $room");
			$room->setContainsHazard(false);
		}	
		//Add room to relevant array
		$relevantRooms[] = $room;
	}
	
	if(empty($parentIds)){
		$parentIds = array();
	}
	
	if(!in_array($hazard->getKey_Id(), $parentIds)){
		array_push($parentIds, $hazard->getKey_Id());
	}
	
	$parentIdsForChild = $parentIds;
	array_pop($parentIdsForChild);
	
	//Build nodes for sub-hazards
	$subHazardNodeDtos = array();
	$LOG->trace("Getting mappings for sub-hazards");
	foreach( $hazard->getSubHazards() as $subHazard ){
				
		$node = getHazardRoomMappings($subHazard, $rooms, $searchRoomIds, $parentIds);
		$subHazardNodeDtos[$node->getKey_Id()] = $node;
	}
	
	//Build the node for this hazard
	$hazardDto = new HazardTreeNodeDto(
		$hazard->getKey_Id(),
		$hazard->getName(),
		$relevantRooms,
		$subHazardNodeDtos,
		$parentIdsForChild
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
function saveRoomRelation($hazardId, $roomId){
	//temporarily return true so server returns 200 code
	return true;
};

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
		$dao = getDao(new Recommendation());
		return $dao->getById($id);
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
		$dao = getDao(new Observation());
		return $dao->getById($id);
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

		$response = getResponseById($id);
		if (!empty($response)) {
			return $response->getObservations;
		} else {
			
		//error
		return new ActionError("No response with id $id was found");
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
		$dao = getDao(new Response());
		return 
		$response = $dao->getById($id);
		
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
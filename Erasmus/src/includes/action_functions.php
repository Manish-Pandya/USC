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

	if(stristr($paramValue, "null"))return null;

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
	$allUsers = $userDao->getAll('last_name');

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

function getRoleById( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$id = getValueFromRequest('id', $id);

	if( $id !== NULL ){
		$dao = getDao(new Role());
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
		$dao = getDao( new User() );
		$dao->save( $decodedObject );
		if($decodedObject->getKey_id()>0)return $decodedObject;
	}
	return new ActionError('Could not save');
};

function getAllRoles(){
	$rolesDao = getDao( new Role() );
	$allRoles = $rolesDao->getAll();
	return $allRoles;
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
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$id = getValueFromRequest('id', $id);

	if( $id !== NULL ){
		$dao = getDao(new Hazard());
		$hazard = $dao->getById($id);
		$checklist = $hazard->getChecklist();
		if (!empty($checklist)) {
				return $checklist;
		} else {
			return true;
		}
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

function saveChecklist($checklist = null){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);

	//if we have passed a checklist, use it, if not, use the input stream
	if($checklist == null){
		$decodedObject = convertInputJson();
	}else{
		$decodedObject = $checklist	;
	}

	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Checklist');
	}
	else{
		$dao = getDao(new Checklist());

		// Find the name of the master hazard
		if ($decodedObject->getHazard_id() != null) {
			// Get the hazard for this checklist
			$hazard = $decodedObject->getHazard();
			// Get the array of parent hazards
			$hazard->setParentIds(null);
			$parentIds = $hazard->getParentIds();

			$master_hazard = null;

			// If there are at least 2 hazards, get the second to last one (this is the master category)
			if (!empty($parentIds)){
				$count = count($parentIds);
				if ($count >= 2){
					$masterHazardId = $parentIds[$count - 2];
					$hazardDao = getDao ($hazard);
					$masterHazard = $hazardDao->getById($masterHazardId);
					$master_hazard = $masterHazard->getName();
				}else{
					//if we don't have a parent hazard, other than Root, we set the master hazard to be the hazard
					//i.e. Biological Hazards' checklist should have Biological Hazards as its master hazard
					$master_hazard = $hazard->getName();
				}
			}

			$decodedObject->setMaster_hazard($master_hazard);
		}

		$dao->save($decodedObject);

		return $decodedObject;
	}
};

function setMasterHazardsForAllChecklists(){
	//get all of the checklists
	$dao = getDao(new Checklist());
	$checklists = $dao->getAll();

	foreach($checklists as $checklist){
		//if this checklist has a hazard_id, call the saveChecklist function, which will find and set the master hazard and save the checklist
		if ($checklist->getHazard_id() != null) {
			saveChecklist($checklist);
		}

		$dao->save($checklist);
	}
	return $checklists;
}

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
	$entityMaps[] = new EntityMap("lazy","getSubhazards");
	$entityMaps[] = new EntityMap("eager","getActiveSubhazards");
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

	$entityMaps = array();
	$entityMaps[] = new EntityMap("lazy","getSubHazards");
	$entityMaps[] = new EntityMap("lazy","getActiveSubHazards");
	$entityMaps[] = new EntityMap("lazy","getChecklist");
	$entityMaps[] = new EntityMap("lazy","getRooms");
	$entityMaps[] = new EntityMap("lazy","getInspectionRooms");
	$entityMaps[] = new EntityMap("lazy","getHasChildren");
	$entityMaps[] = new EntityMap("lazy","getParentIds");

	foreach ($hazards as &$hazard){
		$hazard->setEntityMaps($entityMaps);
	}

	return $hazards;
};

function getHazardTreeNode( $id = NULL){

	// get the node hazard
	$hazard = getHazardById($id);
	$hazards = array();

	// prepare a load map for the subHazards to load Subhazards lazy but Checklist eagerly.
	$hazMaps = array();
	$hazMaps[] = new EntityMap("lazy","getSubHazards");
	$hazMaps[] = new EntityMap("lazy","getActiveSubHazards");
	$hazMaps[] = new EntityMap("eager","getChecklist");
	$hazMaps[] = new EntityMap("lazy","getRooms");
	$hazMaps[] = new EntityMap("lazy","getInspectionRooms");
	$hazMaps[] = new EntityMap("eager","getHasChildren");
	$hazMaps[] = new EntityMap("lazy","getParentIds");

	// prepare a load map for Checklist to load all lazy.
	$chklstMaps = array();
	$chklstMaps[] = new EntityMap("lazy","getHazard");
	$chklstMaps[] = new EntityMap("lazy","getQuestions");

	// For each child hazard, init a lazy-loading checklist, if there is one
	foreach ($hazard->getSubHazards() as $child){
		$checklist = $child->getChecklist();
		// If there's a checklist, set its load map and push it back onto the hazard
		if ($checklist != null) {
			$checklist->setEntityMaps($chklstMaps);
			$child->setChecklist($checklist);
		}

		// set load map for this hazard
		$child->setEntityMaps($hazMaps);
		// push this hazard onto the hazards array
		$hazards[] = $child;

	}

	// Return the child hazards
	return $hazards;
};


//FIXME: Remove $name
function getHazardById( $id = NULL){
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

function saveRoom(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();

	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Hazard');
	}
	else if( $decodedObject instanceof ActionError ){
		return $decodedObject;
	}
	else{
		$dao = getDao(new Room());
		$decodedObject = $dao->save($decodedObject);
		return $decodedObject;
	}
};

function removeResponse( $id = NULL ){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);

	$id = getValueFromRequest('id', $id);

	if( $id !== NULL ){
		$dao = getDao(new Response());

		// Get the response object
		$response = $dao->getById($id);

		$LOG->debug(" Response is: $response");
		if ($response == null) {
			$LOG->debug(" Response was null");
			return new ActionError("Bad Response id: $id");
		}

		// Remove all its response data before deleting the response itself
		foreach ($response->getDeficiencySelections() as $child){
			$dao->removeRelatedItems($child->getKey_id(),$response->getKey_id(),DataRelationship::fromArray(Response::$DEFICIENCIES_RELATIONSHIP));
		}

		foreach ($response->getRecommendations() as $child){
			$dao->removeRelatedItems($child->getKey_id(),$response->getKey_id(),DataRelationship::fromArray(Response::$RECOMMENDATIONS_RELATIONSHIP));
		}

		foreach ($response->getObservations() as $child){
			$dao->removeRelatedItems($child->getKey_id(),$response->getKey_id(),DataRelationship::fromArray(Response::$OBSERVATIONS_RELATIONSHIP));
		}

		foreach ($response->getSupplementalRecommendations() as $child){
			$dao->removeRelatedItems($child->getKey_id(),$response->getKey_id(),DataRelationship::fromArray(Response::$SUPPLEMENTAL_RECOMMENDATIONS_RELATIONSHIP));
		}

		foreach ($response->getSupplementalObservations() as $child){
			$dao->removeRelatedItems($child->getKey_id(),$response->getKey_id(),DataRelationship::fromArray(Response::$SUPPLEMENTAL_OBSERVATIONS_RELATIONSHIP));
		}

		$dao->deleteById($id);

		return true;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function removeDeficiencySelection( $deficiencyId = NULL, $inspectionId = NULL ){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);

	$inspectionId = getValueFromRequest('inspectionId', $inspectionId);
	$deficiencyId = getValueFromRequest('deficiencyId', $deficiencyId);

	if( $inspectionId !== NULL  && $deficiencyId!== NULL){

		// Find the deficiencySelection
		$ds = getDeficiencySelectionByInspectionIdAndDeficiencyId($inspectionId,$deficiencyId);

		if ($ds == null){
			return new ActionError("Couldn't find DeficiencySelection for that Inspection and Deficiency");
		}

		$LOG->debug("DeficiencySelection is: $ds->getKey_id()".$ds);

		$dao = getDao($ds);

		// Remove all its child data before deleting the deficiencyselection itself
		foreach ($ds->getCorrectiveActions() as $child){
			$dao->removeRelatedItems($child->getKey_id(),$ds->getKey_id(),DataRelationship::fromArray(DeficiencySelection::$CORRECTIVE_ACTIONS_RELATIONSHIP));
		}

		foreach ($ds->getRooms() as $child){
			$dao->removeRelatedItems($child->getKey_id(),$ds->getKey_id(),DataRelationship::fromArray(DeficiencySelection::$ROOMS_RELATIONSHIP));
		}

		$dao->deleteById($ds->getKey_id());

		return true;
	}
	else{
		//error
		return new ActionError("Must provide parameters deficiencyId and inspectionId");
	}
};

function addCorrectedInInspection( $deficiencyId = NULL, $inspectionId = NULL ){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);

	$inspectionId = getValueFromRequest('inspectionId', $inspectionId);
	$deficiencyId = getValueFromRequest('deficiencyId', $deficiencyId);

	if( $inspectionId !== NULL  && $deficiencyId!== NULL){

		// Find the deficiencySelection
		$ds = getDeficiencySelectionByInspectionIdAndDeficiencyId($inspectionId,$deficiencyId);

		if ($ds == null){
			return new ActionError("Couldn't find DeficiencySelection for that Inspection and Deficiency");
		}

		$LOG->debug("Prepare to add Corrected flag to DeficiencySelection: $ds->getKey_id()");

		$dao = getDao($ds);
		$ds->setCorrected_in_inspection(true);

		$dao->save($ds);

		return true;
		}
		else{
			//error
			return new ActionError("Must provide parameters deficiencyId and inspectionId");
		}
}

function removeCorrectedInInspection( $deficiencyId = NULL, $inspectionId = NULL ){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);

	$inspectionId = getValueFromRequest('inspectionId', $inspectionId);
	$deficiencyId = getValueFromRequest('deficiencyId', $deficiencyId);

	if( $inspectionId !== NULL  && $deficiencyId!== NULL){

		// Find the deficiencySelection
		$ds = getDeficiencySelectionByInspectionIdAndDeficiencyId($inspectionId,$deficiencyId);

		if ($ds == null){
			return new ActionError("Couldn't find DeficiencySelection for that Inspection and Deficiency");
		}

		$LOG->debug("Prepare to remove Corrected flag from DeficiencySelection: $ds->getKey_id()");

		$dao = getDao($ds);
		$ds->setCorrected_in_inspection(false);

		$dao->save($ds);

		return true;
		}
		else{
			//error
			return new ActionError("Must provide parameters deficiencyId and inspectionId");
		}
}

function getDeficiencySelectionByInspectionIdAndDeficiencyId($inspectionId = null,$deficiencyId = null){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);

	$dao = getDao(new Inspection());
	$inspection = $dao->getById($inspectionId);

	foreach ($inspection->getResponses() as $response){
		foreach ($response->getDeficiencySelections() as $ds){
			$def = $ds->getDeficiency();
			if ($def->getKey_id() == $deficiencyId){
				$LOG->debug("Found a matching DeficiencySelection: ". $ds);
				return $ds;
			}
		}
	}
	$LOG->debug("Found no matching DeficiencySelection for inspection [$inspectionId] and deficiency [$deficiencyId]");

	return null;
}

function saveBuilding(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();

	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Building');
	}
	else if( $decodedObject instanceof ActionError ){
		return $decodedObject;
	}
	else{
		$dao = getDao(new Building());
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

function saveRecommendationRelation(){

	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$decodedObject = convertInputJson();

	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to RelationshipDto');
	}
	else if( $decodedObject instanceof ActionError ){
		return $decodedObject;
	}
	else{

		$responseId = $decodedObject->getMaster_id();
		$recommendationId = $decodedObject->getRelation_id();
		$add = $decodedObject->getAdd();

		if( $responseId !== NULL && $recommendationId !== NULL && $add !== null ){

			// Get a DAO
			$dao = getDao(new Response());
			// if add is true, add this recommendation to this response
			if ($add){
				$dao->addRelatedItems($recommendationId,$responseId,DataRelationship::fromArray(Response::$RECOMMENDATIONS_RELATIONSHIP));
				// if add is false, remove this recommendation from this response
			} else {
				$dao->removeRelatedItems($recommendationId,$responseId,DataRelationship::fromArray(Response::$RECOMMENDATIONS_RELATIONSHIP));
			}

		} else {
			//error
			return new ActionError("Missing proper parameters (should be masterId int, relationId int, add boolean)");
		}

	}
	return true;

};

function saveObservationRelation(){

	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$decodedObject = convertInputJson();

	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to RelationshipDto');
	}
	else if( $decodedObject instanceof ActionError ){
		return $decodedObject;
	}
	else{

		$responseId = $decodedObject->getMaster_id();
		$observationId = $decodedObject->getRelation_id();
		$add = $decodedObject->getAdd();

		if( $responseId !== NULL && $observationId !== NULL && $add !== null ){

			// Get a DAO
			$dao = getDao(new Response());
			// if add is true, add this observation to this response
			if ($add){
				$dao->addRelatedItems($observationId,$responseId,DataRelationship::fromArray(Response::$OBSERVATIONS_RELATIONSHIP));
				// if add is false, remove this observation from this response
			} else {
				$dao->removeRelatedItems($observationId,$responseId,DataRelationship::fromArray(Response::$OBSERVATIONS_RELATIONSHIP));
			}

		} else {
			//error
			return new ActionError("Missing proper parameters (should be masterId int, relationId int, add boolean)");
		}

	}
	return true;

};

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
function getPIById( $id = NULL ){

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

function savePI(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Observation');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao(new PrincipalInvestigator());
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function saveInspector(){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Observation');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao(new Inspector());
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function savePIRoomRelation($PIId = NULL,$roomId = NULL,$add= NULL){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$decodedObject = convertInputJson();

	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to RelationshipDto');
	}
	else if( $decodedObject instanceof ActionError ){
		return $decodedObject;
	}
	else{

		$PIId = $decodedObject->getMaster_id();
		$roomId = $decodedObject->getRelation_id();
		$add = $decodedObject->getAdd();

		if( $PIId !== NULL && $roomId !== NULL && $add !== null ){

			// Get a DAO
			$dao = getDao(new PrincipalInvestigator());
			// if add is true, add this room to this PI
			if ($add){
				$dao->addRelatedItems($roomId,$PIId,DataRelationship::fromArray(PrincipalInvestigator::$ROOMS_RELATIONSHIP));
			// if add is false, remove this room from this PI
			} else {
				$dao->removeRelatedItems($roomId,$PIId,DataRelationship::fromArray(PrincipalInvestigator::$ROOMS_RELATIONSHIP));
			}

		} else {
			//error
			return new ActionError("Missing proper parameters (should be masterId int, relationId int, add boolean)");
		}

	}
	return true;
};

function savePIContactRelation($PIId = NULL,$contactId = NULL,$add= NULL){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$decodedObject = convertInputJson();

	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to RelationshipDto');
	}
	else if( $decodedObject instanceof ActionError ){
		return $decodedObject;
	}
	else{

		$PIId = $decodedObject->getMaster_id();
		$contactId = $decodedObject->getRelation_id();
		$add = $decodedObject->getAdd();

		if( $PIId !== NULL && $contactId !== NULL && $add !== null ){

			// Get a DAO
			$dao = getDao(new PrincipalInvestigator());
			// if add is true, add this lab contact to this PI
			if ($add){
				$dao->addRelatedItems($contactId,$PIId,DataRelationship::fromArray(PrincipalInvestigator::$LABPERSONNEL_RELATIONSHIP));
			// if add is false, remove this lab contact from this PI
			} else {
				$dao->removeRelatedItems($contactId,$PIId,DataRelationship::fromArray(PrincipalInvestigator::$LABPERSONNEL_RELATIONSHIP));
			}

		} else {
			//error
			return new ActionError("Missing proper parameters (should be masterId int, relationId int, add boolean)");
		}

	}
	return true;
};

function savePIDepartmentRelation($PIID = NULL,$deptId = NULL,$add= NULL){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$decodedObject = convertInputJson();

	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to RelationshipDto');
	}
	else if( $decodedObject instanceof ActionError ){
		return $decodedObject;
	}
	else{

		$PIId = $decodedObject->getMaster_id();
		$deptId = $decodedObject->getRelation_id();
		$add = $decodedObject->getAdd();

		$pi = getPIById($PIId);
		$departments = $pi->getDepartments();
		$departmentToAdd = getDepartmentById($deptId);

		if( $PIId !== NULL && $deptId !== NULL && $add !== null ){

			// Get a DAO
			$dao = getDao(new PrincipalInvestigator());
			// if add is true, add this department to this PI
			if ($add){
				if(!in_array($departmentToAdd, $departments)){
					// only add the department if the pi doesn't already have it
					$dao->addRelatedItems($deptId,$PIId,DataRelationship::fromArray(PrincipalInvestigator::$DEPARTMENTS_RELATIONSHIP));
				}
			// if add is false, remove this department from this PI
			} else {
				$dao->removeRelatedItems($deptId,$PIId,DataRelationship::fromArray(PrincipalInvestigator::$DEPARTMENTS_RELATIONSHIP));
			}

		} else {
			//error
			return new ActionError("Missing proper parameters (should be masterId int, relationId int, add boolean)");
		}

	}
	return true;
};


function saveUserRoleRelation($userID = NULL,$roleId = NULL,$add= NULL){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$decodedObject = convertInputJson();

	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to RelationshipDto');
	}
	else if( $decodedObject instanceof ActionError ){
		return $decodedObject;
	}
	else{

		$userID = $decodedObject->getMaster_id();
		$roleId = $decodedObject->getRelation_id();
		$add = $decodedObject->getAdd();

		if( $userID !== NULL && $roleId !== NULL && $add !== null ){
			$user = getUserById($userID);
			$roles = $user->getRoles();
			$roleToAdd = getRoleById($roleId);

			// Get a DAO
			$dao = getDao(new User());
			// if add is true, add this role to this PI
			if ($add){
				if(!in_array($roleToAdd, $roles)){
					// only add the role if the user doesn't already have it
					$dao->addRelatedItems($roleId,$userID,DataRelationship::fromArray(User::$ROLES_RELATIONSHIP));
				}
				// if add is false, remove this role from this PI
			} else {
				$dao->removeRelatedItems($roleId,$userID,DataRelationship::fromArray(User::$ROLES_RELATIONSHIP));
			}

		} else {
			//error
			return new ActionError("Missing proper parameters (should be masterId int, relationId int, add boolean)");
		}

	}
	return true;
};

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

function saveDepartment($department = null){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	if($department == null){
		$decodedObject = convertInputJson();
	}else{
		$decodedObject = $department;
	}

	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Hazard');
	}
	else if( $decodedObject instanceof ActionError ){
		return $decodedObject;
	}
	else{
		$dao = getDao(new Department());
		$dao->save($decodedObject);
		return $decodedObject;
	}
}

function getAllBuildings( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$dao = getDao(new Building());

	// get all buildings
	$buildings = $dao->getAll();

	// initialize an array of entityMap settings to assign to rooms, instructing them to lazy-load children
	// necessary because rooms by default eager-load buildings, and this would set up an infinite load loop between building->room->building->room...
	$roomMaps = array();
	$roomMaps[] = new EntityMap("eager","getPrincipalInvestigators");
	$roomMaps[] = new EntityMap("lazy","getHazards");
	$roomMaps[] = new EntityMap("lazy","getBuilding");

	$bldgMaps = array();
	$bldgMaps[] = new EntityMap("eager","getRooms");


	///iterate the buildings
	foreach ($buildings as &$building){
		// get this building's rooms
		$rooms = $building->getRooms();
		// iterate this building's rooms and make then lazy loading
		foreach ($rooms as &$room){
			$room->setEntityMaps($roomMaps);
		}
		// make sure this building is loaded with the lazy loading rooms
		$building->setRooms($rooms);
		// ... and make sure that the rooms themselves are loaded eagerly
		$building->setEntityMaps($bldgMaps);
	}

	return $buildings;

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

function initiateInspection($inspectionId = NULL,$piId = NULL,$inspectorIds= NULL){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$inspectionId = getValueFromRequest('inspectionId', $inspectionId);
	$piId = getValueFromRequest('piId', $piId);
	$inspectorIds = getValueFromRequest('inspectorIds', $inspectorIds);

	if( $piId !== NULL && $inspectorIds !== null ){

		// Get this room
		$inspection = new Inspection();
		$dao = getDao($inspection);

		// Set inspection's keyId and PI.
		if (!empty($inspectionId)){
			$inspection = $dao->getById($inspectionId);
		}

		$inspection->setPrincipal_investigator_id($piId);

		if($inspection->getDate_started() == null)$inspection->setDate_started(date("Y-m-d H:i:s"));
		// Save (or update) the inspection
		$dao->save($inspection);
		$pi = $inspection->getPrincipalInvestigator();

		// Remove previous rooms and add the default rooms for this PI.
		$oldRooms = $inspection->getRooms();
		if (!empty($oldRooms)) {
			// removeo the old rooms
			foreach ($oldRooms as $oldRoom) {
				$dao->removeRelatedItems($oldRoom->getKey_id(),$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$ROOMS_RELATIONSHIP));
			}
		}
		// add the default rooms for this PI
		foreach ($pi->getRooms() as $newRoom) {
			$dao->addRelatedItems($newRoom->getKey_id(),$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$ROOMS_RELATIONSHIP));
		}

		// Remove previous inspectors and add the submitted inspectors.
		$oldInspectors = $inspection->getInspectors();
		if (!empty($oldInspectors)) {
			// remove the old inspectors
			foreach ($oldInspectors as $oldInsp) {
				$dao->removeRelatedItems($oldInsp->getKey_id(),$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$INSPECTORS_RELATIONSHIP));
			}
		}
		// add the submitted Inspectors
		foreach ($inspectorIds as $insp) {
			$dao->addRelatedItems($insp,$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$INSPECTORS_RELATIONSHIP));
		}


	} else {
		//error
		return new ActionError("Missing proper parameters (should be inspectionId (nullable int), piId int, inspectorIds (one or more ints))");
	}

	$entityMaps = array();
	$entityMaps[] = new EntityMap("eager","getInspectors");
	$entityMaps[] = new EntityMap("eager","getRooms");
	$entityMaps[] = new EntityMap("lazy","getResponses");
	$entityMaps[] = new EntityMap("eager","getPrincipalInvestigator");
	$entityMaps[] = new EntityMap("lazy","getChecklists");
	$inspection->setEntityMaps($entityMaps);

	return $inspection;
	};


function saveInspectionRoomRelation($roomId = NULL,$inspectionId = NULL,$add= NULL){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$roomId = getValueFromRequest('roomId', $roomId);
	$inspectionId = getValueFromRequest('inspectionId', $inspectionId);
	$add = getValueFromRequest('add', $add);

	if( $roomId !== NULL && $inspectionId !== NULL && $add !== null ){

		// Get this inspection
		$dao = getDao(new Inspection());
		$inspection = $dao->getById($inspectionId);
		// if add is true, add this room to this inspection
		if ($add){
			$dao->addRelatedItems($roomId,$inspectionId,DataRelationship::fromArray(Room::$ROOMS_RELATIONSHIP));
		// if add is false, remove this room from this inspection
		} else {
			$dao->removeRelatedItems($roomId,$inspectionId,DataRelationship::fromArray(Room::$ROOMS_RELATIONSHIP));
		}

	} else {
		//error
		return new ActionError("Missing proper parameters (should be roomId int, inspectionId int, add boolean)");
	}
	return true;

};

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
		$roomIds = $decodedObject->getRooms();
		if (!empty($roomIds)) { $saveRooms = true; } else { $saveRooms = false;}

		$inspectorIds = $decodedObject->getInspectors();
		if (!empty($inspectorIds)) { $saveInspectors = true; } else { $saveInspectors = false;}

		$dao = getDao(new Inspection());

		// Save the Inspection
		$inspection = $dao->save($decodedObject);

		return $inspection;
	}
};

function saveNoteForInspection(){
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

		// Get the inspection and update its Note property
		$inspection = $dao->getById($decodedObject->getEntity_id());
		$inspection->setNote($decodedObject->getText());

		// Save the Inspection
		$dao->save($inspection);

		return true;
	}
};

// Inspection, step 2 (Hazard Assessment)

/**
 * Builds an associative array mapping Hazard IDs to the rooms
 * that contain them. The listed rooms are limited by the Room IDs
 * given as a CSV parameter
 *
 * @param string $roomIds
 * @param Hazard $hazard
 * @return Associative array: [Hazard KeyId] => array( HazardTreeNodeDto )
 */
function getHazardRoomMappingsAsTree( $roomIds = NULL, $hazard = null ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$roomIdsCsv = getValueFromRequest('roomIds', $roomIds);

	if( $roomIdsCsv !== NULL ){
		$LOG->debug("Retrieving Hazard-Room mappings for Rooms: $roomIdsCsv");


		$LOG->debug('Identified ' . count($roomIdsCsv) . ' Rooms');

		//Get all hazards
		if($hazard != null){
		  $allHazards = $hazard;
		}else{
		  $allHazards = getAllHazardsAsTree();
		}

		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getSubHazards");
		$entityMaps[] = new EntityMap("eager","getActiveSubHazards");
		$entityMaps[] = new EntityMap("lazy","getChecklist");
		$entityMaps[] = new EntityMap("lazy","getRooms");
		$entityMaps[] = new EntityMap("lazy","getInspectionRooms");
		$entityMaps[] = new EntityMap("lazy","getHasChildren");
		$entityMaps[] = new EntityMap("lazy","getParentIds");

		$allHazards->setEntityMaps($entityMaps);

		$rooms = array();
		$roomDao = getDao(new Room());

		// Create an array of Room Objects
		foreach($roomIdsCsv as $roomId) {
			array_push($rooms,$roomDao->getById($roomId));
		}

		// filter by room
		foreach ($allHazards->getActiveSubHazards() as $subhazard){
			$entityMaps = array();
			$entityMaps[] = new EntityMap("lazy","getSubHazards");
			$entityMaps[] = new EntityMap("eager","getActiveSubHazards");
			$entityMaps[] = new EntityMap("lazy","getChecklist");
			$entityMaps[] = new EntityMap("lazy","getRooms");
			$entityMaps[] = new EntityMap("eager","getInspectionRooms");
			$entityMaps[] = new EntityMap("eager","getHasChildren");
			$entityMaps[] = new EntityMap("lazy","getParentIds");
			$subhazard->setEntityMaps($entityMaps);
			filterHazards($subhazard,$rooms);
		}
		return $allHazards;
	}
	else{
		//error
		return new ActionError("No request parameter 'roomIds' was provided");
	}
}

function filterHazards (&$hazard, $rooms){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$LOG->debug($hazard->getName());
	$entityMaps = array();
	$entityMaps[] = new EntityMap("lazy","getSubHazards");
	$entityMaps[] = new EntityMap("lazy","getChecklist");
	$entityMaps[] = new EntityMap("lazy","getRooms");
	$entityMaps[] = new EntityMap("eager","getInspectionRooms");
	$entityMaps[] = new EntityMap("eager","getHasChildren");
	$entityMaps[] = new EntityMap("lazy","getParentIds");

	$hazard->setInspectionRooms($rooms);
	$hazard->filterRooms();

	if($hazard->getIsPresent() || $hazard->getParent_hazard_id() != 1000){
		$entityMaps[] = new EntityMap("eager","getActiveSubHazards");
	}else{
		$entityMaps[] = new EntityMap("lazy","getActiveSubHazards");
	}

	$hazard->setEntityMaps($entityMaps);


	foreach ($hazard->getActiveSubhazards() as $subhazard){
		$LOG = Logger::getLogger( 'Action within loop:' . __FUNCTION__ );
		//if we have called this function by passing a hazard from the view, the subhazards will be read as arrays rather than objects, because php
		if(!is_object($subhazard)){
			$subDao = getDao(new Hazard());
			$subhazard = $subDao->getById($subhazard[$Key_id]);
		}

		$subhazard->setInspectionRooms($rooms);
		$subhazard->filterRooms();


		if($subhazard->getIsPresent() == true){
			$LOG->debug($subhazard->getName()." is Present? ". $subhazard->getIsPresent());
			//$entityMaps[] = new EntityMap("eager","getActiveSubHazards");
			$entityMaps = array();
			$entityMaps[] = new EntityMap("lazy","getSubHazards");
			$entityMaps[] = new EntityMap("eager","getActiveSubHazards");
			$entityMaps[] = new EntityMap("lazy","getChecklist");
			$entityMaps[] = new EntityMap("lazy","getRooms");
			$entityMaps[] = new EntityMap("eager","getInspectionRooms");
			$entityMaps[] = new EntityMap("eager","getHasChildren");
			$entityMaps[] = new EntityMap("lazy","getParentIds");
			$subhazard->setEntityMaps($entityMaps);
			filterHazards($subhazard, $rooms);
		}else{
			$entityMaps = array();
			$entityMaps[] = new EntityMap("lazy","getSubHazards");
			$entityMaps[] = new EntityMap("lazy","getActiveSubHazards");
			$entityMaps[] = new EntityMap("lazy","getChecklist");
			$entityMaps[] = new EntityMap("lazy","getRooms");
			$entityMaps[] = new EntityMap("eager","getInspectionRooms");
			$entityMaps[] = new EntityMap("eager","getHasChildren");
			$entityMaps[] = new EntityMap("lazy","getParentIds");
			$subhazard->setEntityMaps($entityMaps);
		}

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
	foreach( $hazard->getActiveSubHazards() as $subHazard ){

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

function getHazardsInRoom( $roomId = NULL, $subHazards ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$roomId = getValueFromRequest('roomId', $roomId);
	$subHazards = getValueFromRequest('subHazards', $subHazards);
	$LOG->debug("subHazards is $subHazards, roomId is $roomId");


	if( $roomId !== NULL ){

		$dao = getDao(new Room());

		//get Room
		$room = $dao->getById($roomId);

		//get hazards
		$hazards = $room->getHazards();

		// if subhazards is false, change all hazard subentities to lazy loading
		if ($subHazards == "false"){
			$entityMaps = array();
			$entityMaps[] = new EntityMap("lazy","getSubHazards");
			$entityMaps[] = new EntityMap("lazy","getActiveSubHazards");
			$entityMaps[] = new EntityMap("lazy","getChecklist");
			$entityMaps[] = new EntityMap("lazy","getRooms");
			$entityMaps[] = new EntityMap("lazy","getInspectionRooms");
			$entityMaps[] = new EntityMap("eager","getParentIds");
			$entityMaps[] = new EntityMap("lazy","getHasChildren");

			foreach ($hazards as &$hazard){
				$hazard->setEntityMaps($entityMaps);
				$parentIds = array();
				$hazard->setParentIds($parentIds);
			}

		}
		return $hazards;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function saveHazardRoomRelations( $hazard = null ){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Inspection');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{

		$dao = getDao(new Hazard());
		// Get the hazard
		$hazard = $decodedObject;

		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getSubHazards");
		$entityMaps[] = new EntityMap("eager","getActiveSubHazards");
		$entityMaps[] = new EntityMap("lazy","getChecklist");
		$entityMaps[] = new EntityMap("lazy","getRooms");
		$entityMaps[] = new EntityMap("eager","getInspectionRooms");
		$entityMaps[] = new EntityMap("eager","getHasChildren");
		$entityMaps[] = new EntityMap("lazy","getParentIds");
		$hazard->setEntityMaps($entityMaps);

		foreach($hazard->getInspectionRooms() as $room){
			if($hazard->getIsPresent() == true){
				//create hazard room relations
				saveHazardRelation($room->getKey_id(),$hazard->getKey_id(),true);
				$room->setContainsHazard(true);
			}else{
				//delete room relations
				saveHazardRelation($room->getKey_id(),$hazard->getKey_id(),false);
				//we must also remove the relationships for any subhazards, so we call recursively
				foreach($hazard->getActiveSubHazards() as $subhazard){
					saveHazardRelation($room->getKey_id(),$subhazard->getKey_id(),false);
					$subhazard->filterRooms();
				}
			}
		}
		$hazard->filterRooms();
		$LOG->debug($hazard);
		//filterHazards($hazard, $hazard->getInspectionRooms());
		return $hazard;

	}
}

function saveHazardRelation($roomId = NULL,$hazardId = NULL,$add= NULL){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	if($roomId == null)$roomId = getValueFromRequest('roomId', $roomId);
	if($hazardId == null)$hazardId = getValueFromRequest('hazardId', $hazardId);
	if($add == null)$add = getValueFromRequest('add', $add);

	if( $roomId !== NULL && hazardId !== NULL && $add !== null ){

		// Get this room
		$dao = getDao(new Room());
		$room = $dao->getById($roomId);
		// if add is true, add this hazard to this room
		if ($add){
			$dao->addRelatedItems($hazardId,$roomId,DataRelationship::fromArray(Room::$HAZARDS_RELATIONSHIP));
		// if add is false, remove this hazard from this room
		} else {
			$dao->removeRelatedItems($hazardId,$roomId,DataRelationship::fromArray(Room::$HAZARDS_RELATIONSHIP));
		}

	} else {
		//error
		return new ActionError("Missing proper parameters (should be roomId int, hazardId int, add boolean)");
	}
	return true;

};

function getSubHazards($hazard = NULL){
	$LOG = Logger::getLogger('Action:' . __FUNCTION__);
	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to Inspection');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{

		$dao = getDao(new Hazard());
		// Get the hazard
		$hazard = $decodedObject;

		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getSubHazards");
		$entityMaps[] = new EntityMap("eager","getActiveSubHazards");
		$entityMaps[] = new EntityMap("lazy","getChecklist");
		$entityMaps[] = new EntityMap("lazy","getRooms");
		$entityMaps[] = new EntityMap("eager","getInspectionRooms");
		$entityMaps[] = new EntityMap("eager","getHasChildren");
		$entityMaps[] = new EntityMap("lazy","getParentIds");
		$hazard->setEntityMaps($entityMaps);
		$hazard->filterRooms();

		filterHazards($hazard, $hazard->getInspectionRooms());
		return $hazard->getActiveSubHazards();
	}
}

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
		$dao = getDao(new Deficiency());
		$keyid = $id;

		// query for Inspection with the specified ID
		return $dao->getById($id);
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
		$dao = getDao(new Response());
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
		// check to see if the roomIds array is populated
		$roomIds = $decodedObject->getRoomIds();

		// start by saving or updating the object.
		$dao = getDao(new DeficiencySelection());
		$ds = $dao->save($decodedObject);

		// remove the old rooms. if any
		foreach ($ds->getRooms() as $room){
			$dao->removeRelatedItems($room->getKey_id(),$ds->getKey_id(),DataRelationship::fromArray(DeficiencySelection::$ROOMS_RELATIONSHIP));
		}

		// if roomIds were provided then save them
		if (!empty($roomIds)){
			foreach ($roomIds as $id){
				$dao->addRelatedItems($id,$ds->getKey_id(),DataRelationship::fromArray(DeficiencySelection::$ROOMS_RELATIONSHIP));
			}

		// else if no roomIds were provided, then just delete this DeficiencySelection
		} else {
			$dao->deleteById($ds->getKey_id());
			return true;
		}

		return $ds;

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
		return new ActionError('Error converting input stream to CorrectiveAction');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		$dao = getDao(new CorrectiveAction());
		$dao->save($decodedObject);
		return $decodedObject;
	}
};

function getChecklistsForInspection( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );
	$id = getValueFromRequest('id', $id);
	if( $id !== NULL ){
		$dao = getDao(new Inspection());

		//get inspection
		$inspection = $dao->getById($id);
		// get the rooms for the inspection
		$rooms = $inspection->getRooms();
		$masterHazards = array();
		//iterate the rooms and find the hazards present
		foreach ($rooms as $room){
			$hazardlist = getHazardsInRoom($room->getKey_id());
			// get each hazard present in the room
			foreach ($hazardlist as $hazard){
				// Check to see if we've already examined this hazard (in an earlier room)
				if (!in_array($hazard->getKey_id(),$masterHazards)){
					// if this is new hazard, add its keyid to the master array...
					$masterHazards[] = $hazard->getKey_id();
					// ... and get its checklist, if there is one
					$checklist = $hazard->getChecklist();
					// if this hazard had a checklist, add it to the checklists array
					if (!empty($checklist)){
						$checklists[] = $checklist;
					}
				}
			}
		}


		if (!empty($checklists)){
			// return the list of checklist objects
			return $checklists;
		} else {
			// no applicable checklists, return false
			return false;
		}

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
		$dao = getDao(new Inspection());

		//get inspection
		$inspection = $dao->getById($id);

		if (empty($inspection) ) {return new ActionError("No Response with id $id exists");}

		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getInspectors");
		$entityMaps[] = new EntityMap("eager","getRooms");
		$entityMaps[] = new EntityMap("lazy","getResponses");
		$entityMaps[] = new EntityMap("eager","getPrincipalInvestigator");
		$entityMaps[] = new EntityMap("eager","getChecklists");
		$inspection->setEntityMaps($entityMaps);
		// pre-init the checklists so that they load their questions and responses
		$checklists = $inspection->getChecklists();
		$inspection->setChecklists($checklists);

		return $inspection;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
}

function getInspectionsByPIId( $id = NULL ){
	//Get responses for Inspection
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$piId = getValueFromRequest('piId', $piId);

	if( $piId !== NULL ){

		$pi = getPIById($piId);

		$inspections = $pi->getInspections();

		return $inspections;
	}
	else{
		//error
		return new ActionError("No request parameter 'inspectionId' was provided");
	}
}

function resetChecklists( $id = NULL ){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$id = getValueFromRequest('id', $id);

	if( $id !== NULL ){
		$dao = getDao(new Inspection());

		//get inspection
		$inspection = $dao->getById($id);

		// Remove previous checklists (if any) and recalculate the required checklist.
		$oldChecklists = $inspection->getChecklists();
		if (!empty($oldChecklists)) {
			// remove the old checklists
			foreach ($oldChecklists as $oldChecklist) {
				$dao->removeRelatedItems($oldChecklist->getKey_id(),$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$CHECKLISTS_RELATIONSHIP));
			}
		}

		// Calculate the Checklists needed according to hazards currently present in the rooms covered by this inspection
		$checklists = getChecklistsForInspection($inspection->getKey_id());
		// add the checklists to this inspection
		foreach ($checklists as &$checklist){
			$dao->addRelatedItems($checklist->getKey_id(),$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$CHECKLISTS_RELATIONSHIP));
			$checklist->setInspectionId($inspection->getKey_id());
		}

		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getInspectors");
		$entityMaps[] = new EntityMap("eager","getRooms");
		$entityMaps[] = new EntityMap("lazy","getResponses");
		$entityMaps[] = new EntityMap("eager","getPrincipalInvestigator");
		$entityMaps[] = new EntityMap("eager","getChecklists");
		$inspection->setEntityMaps($entityMaps);
		$inspection->setChecklists($checklists);
		return $inspection;

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

function login2($username,$password) {
	//Get responses for Inspection
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );


	$username = getValueFromRequest('username', $username);
	$password = getValueFromRequest('password', $password);



	$ldap = new LDAP();

	// if successfully authenticates by LDAP:
	if ($ldap->IsAuthenticated($username,$password)) {

		// Make sure they're an Erasmus user by username lookup
		$dao = getDao(new User());

		$user = $dao->getUserByUsername($username);

		if ($user != null) {
			// put the USER and ROLE into session
			$_SESSION['USER'] = $user;
			$_SESSION['ROLE'] = $user->getRole();
			// return true to indicate success
			return true;
		} else {
			// successful LDAP login, but not an authorized Erasmus user, return false
			return false;
		}
	}

	// otherwise, return false to indicate failure
	return false;
}

function lookupUser($username = NULL) {
	//Get responses for Inspection
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$username = getValueFromRequest('username', $username);

	$ldap = new LDAP();
	$user = new User();

	$fieldsToFind = array("cn","sn","givenName","mail");

	if ($ldapData = $ldap->GetAttr($username, $fieldsToFind)){
		$user->setFirst_name($ldapData["givenName"]);
		$user->setLast_name($ldapData["sn"]);
		$user->setEmail($ldapData["mail"]);
		$user->setUsername($ldapData["cn"]);
	} else {
		return false;
	}

	return $user;
}

function sendInspectionEmail(){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$decodedObject = convertInputJson();
	if( $decodedObject === NULL ){
		return new ActionError('Error converting input stream to EmailDto');
	}
	else if( $decodedObject instanceof ActionError){
		return $decodedObject;
	}
	else{
		// Get this inspection
		$dao = getDao(new Inspection());
		$inspection = $dao->getById($decodedObject->getEntity_id());

		//get the client side email text
		$text = $decodedObject->getText();

		// Init an array of recipient Email addresses and another of inspector email addresses
		$recipientEmails = array();
		$inspectorEmails = array();

		// We'll need a user Dao to get Users and find their email addresses
		$userDao = getDao(new User());

		// Iterate the recipients list and add their email addresses to our array
		foreach ($decodedObject->getRecipient_ids() as $id){
			$user = $userDao->getById($id);
			$recipientEmails[] = $user->getEmail();
		}

		$otherEmails = $decodedObject->getOther_emails();

		if (!empty($otherEmails)) {
			$recipientEmails = array_merge($recipientEmails,$otherEmails);
		}

		// Iterate the inspectors and add their email addresses to our array
		foreach ($inspection->getInspectors() as $inspector){
			$user = $inspector->getUser();
			$inspectorEmails[] = $user->getEmail();
		}

		$footerText = "\n\n Access the results of this inspection, and document any corrective actions taken, by logging into the RSMS portal located at http://radon.qa.sc.edu/rsms with your university is and password.";
		// Send the email
		mail(implode($recipientEmails,","),'EHS Laboratory Safety Inspection Results',$text . $footerText,'From:no-reply@ehs.sc.edu<RSMS Portal>\r\nCc: '. implode($inspectorEmails,","));

		$inspection->setNotification_date(date("Y-m-d H:i:s"));
		$dao->save($inspection);
		return true;
	}

}

function makeFancyNames(){
	$users = getAllUsers();
	foreach($users as $user){
		if(stristr($user->getName(), ", ")){
			$nameParts = explode(", ", $user->getName());
			$user->setFirst_name($nameParts[1]);
			$user->setLast_name($nameParts[0]);
		}elseif(stristr($user->getName(), " ")){
			$nameParts = explode(" ", $user->getName());
			$user->setFirst_name($nameParts[0]);
			$user->setLast_name($nameParts[1]);
		}else{
			$user->setLast_name($user->getName());
		}
		$dao = getDao( new User() );
		$dao->save( $user );
	}
	return getAllUsers();
}

function createOrderIndicesForHazards(){
	$hazards = getAllHazards();
	foreach($hazards as $hazard){
		$subs = $hazard->getSubHazards();
		if($subs != null){
			$i = 1;
			foreach($subs as $sub){
				$sub->setOrder_index(null);
				$sub->setOrder_index($i);
				$dao = getDao( new Hazard() );
				$dao->save( $sub );
				$i++;
			}
		}
	}
	return $hazards;
}

//reorder hazards
function reorderHazards(){
	$LOG = Logger::getLogger( 'Action:' . __FUNCTION__ );

	$hazardId = getValueFromRequest('hazardId', $hazardId);
	$beforeHazardId = getValueFromRequest('beforeHazardId', $beforeHazardId);
	$afterHazardId = getValueFromRequest('afterHazardId', $afterHazardId);

	$LOG->debug('BEFORE ID: '.$beforeHazardId);
	$LOG->debug('AFTER ID: '.$afterHazardId);

	if( $hazardId !== NULL){
		$dao = getDao(new Hazard());
		$hazard = $dao->getById($hazardId);

		//if we are moving a hazard to the lowest order_index, we won't have a hazard before it
		if($beforeHazardId != "null"){
			$beforeHazard = $dao->getById($beforeHazardId);
			$beforeOrderIdx = $beforeHazard->getOrder_index();
		}else{
			$beforeOrderIdx = 0;
		}

		//if we are moving a hazard to the last index, we won't have a hazard after it.
		if($afterHazardId != "null"){
			$afterHazard = $dao->getById($afterHazardId);
			$afterHazardIdx = $afterHazard->getOrder_index();
		}else{
			$afterHazardIdx = $beforeOrderIdx + 1;
		}

		$LOG->debug("before index: ".$beforeOrderIdx);
		$LOG->debug("after index: ".$afterHazardIdx);

		//set the hazard's order index to a random float between the order indices of the other two hazards
		$hazard->setOrder_index(random_float ($beforeOrderIdx,$afterHazardIdx));
		$dao->save($hazard);

		//we get the parent hazard and return it's subhazards because it is easier to keep the order of its subhazards synched between server and view
		return getHazardTreeNode($hazard->getParent_hazard_id());
	}
	else{
		//error
		if($hazardId == null)return new ActionError("No request parameter 'hazardId' was provided");
		if($beforeHazardId == null)return new ActionError("No request parameter 'beforeHazardId' was provided");
		if($afterHazardId == null)return new ActionError("No request parameter 'afterHazardId' was provided");
	}

}
//generate a random float
function random_float ($min,$max) {
	return ($min+lcg_value()*(abs($max-$min)));
}
?>

<?php
/*
 * This file is responsible for providing functions for Action calls after being
 * instantiated
 *
 * If an error should occur, Action functions should return either NULL or
 * an instance of ActionError. (ActionError reccomended) Returning an ActionError allows the public function
 * to provide additional information about the error.
 */
?><?php

class ActionManager {

	private $daoFactory;
	private $isTestModeEnabled;
	
	// during construction, can change daoFactory, for example for testing purposes
	public function __construct( $daoFactory ) {
		// default to factory providing GenericDao, required for normal operation
		if( is_null($daoFactory) ) {
			$daoFactory = new DaoFactory(new GenericDAO());
		}
		$this->daoFactory = $daoFactory;
	}
	
	public function setDaoFactory( $newFactory ) {
		$this->daoFactory = $newFactory;
	}
	
	// will determine whether ActionManager actually uses JsonManager or not.
	public function setTestMode( $newValue ) {
		$this->isTestModeEnabled = $newValue;
	}
	
	public function isTestModeEnabled() {
		return $this->isTestModeEnabled;
	}

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
	public function getValueFromRequest( $valueName, $paramValue = NULL ){
		$LOG = Logger::getLogger('Action:' . __function__);

		if( $paramValue !== NULL ){
			return $paramValue;
		}
		else if( array_key_exists($valueName, $_REQUEST)){
			$request = $_REQUEST[$valueName];
			if(!is_array($request) && stristr($request, "null")) return null;
			if(!is_array($request) && stristr($request, "false")){
				$LOG->debug('value: '.$paramValue);
				return false;
			}
			return $_REQUEST[ $valueName ];
		}
		else{
			return NULL;
		}
	}

	public function convertInputJson(){

		// if being tested, cannot use JsonManager since php://input is read-only
		if( $this->isTestModeEnabled() ) {
			return $_REQUEST["testInput"];
		}
		
		try{
			$decodedObject = JsonManager::decodeInputStream();

			if( $decodedObject === NULL ){
				return new ActionError('No data read from input stream', 202);
			}

			return $decodedObject;
		}
		catch(Exception $e){
			return new ActionError("Unable to decode JSON. Cause: $e");
		}
	}


	public function getDao( $modelObject = NULL ){
		//FIXME: Remove MockDAO
		if( $modelObject === NULL ){
			return new MockDAO();
		}
		else{
			return $this->daoFactory->createDao($modelObject);
		}
	}

	public function loginAction(){ }
	public function logoutAction(){ }

	public function activate(){
		//Get the user
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to GenericCrud');
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$decodedObject->setIsActive(TRUE);
			$dao = $this->getDao();
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}

	public function deactivate(){
		//Get the user
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to GenericCrud');
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$decodedObject->setIsActive(FALSE);
			$dao = $this->getDao();
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}

	// Users Hub
	public function getAllUsers(){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$userDao = $this->getDao( new User() );
		$allUsers = $userDao->getAll('last_name');

		return $allUsers;
	}

	public function getUserById( $id = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new User());
			return $dao->getById($id);
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function getSupervisorByUserId( $id = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new User());
			$user = $dao->getById($id);

			$entityMaps = array();
			$entityMaps[] = new EntityMap("lazy","getLabPersonnel");
			$entityMaps[] = new EntityMap("lazy","getInspections");
			$entityMaps[] = new EntityMap("lazy","getUser");

			$supervisor = $user->getSupervisor();
			if($supervisor != null){
				$supervisor->setEntityMaps($entityMaps);
				return $supervisor;
			}

			return null;

		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function getRoleById( $id = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Role());
			return $dao->getById($id);
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function saveUser(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to User', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao( new User() );
			$dao->save( $decodedObject );
			if($decodedObject->getKey_id()>0)return $decodedObject;
		}
		return new ActionError('Could not save');
	}

	public function getAllRoles(){
		$rolesDao = $this->getDao( new Role() );
		$allRoles = $rolesDao->getAll();
		return $allRoles;
	}

	// Checklist Hub
	public function getChecklistById( $id = NULL ){

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Checklist());
			return $dao->getById($id);
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function getChecklistByHazardId( $id = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );
		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Hazard());
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
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function getAllQuestions(){
		$LOG = Logger::getLogger( 'Action:' . __function__ );
		$questions = array();

		$dao = $this->getDao(new Question());

			$questions = $dao->getAll();

		return $questions;
	}

	public function saveChecklist($checklist = null){
		$LOG = Logger::getLogger('Action:' . __function__);

		//if we have passed a checklist, use it, if not, use the input stream
		if($checklist == null){
			$decodedObject = $this->convertInputJson();
		}else{
			$decodedObject = $checklist	;
		}

		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Checklist', 202);
		}
		else{
			$dao = $this->getDao(new Checklist());

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
						$hazardDao = $this->getDao ($hazard);
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
	}

	public function setMasterHazardsForAllChecklists(){
		//get all of the checklists
		$dao = $this->getDao(new Checklist());
		$checklists = $dao->getAll();

		foreach($checklists as $checklist){
			//if this checklist has a hazard_id, call the saveChecklist public function, which will find and set the master hazard and save the checklist
			if ($checklist->getHazard_id() != null) {
				$this->saveChecklist($checklist);
			}

			$dao->save($checklist);
		}
		return $checklists;
	}

	public function saveQuestion(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Question', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new Question());
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}

	public function saveDeficiency(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Deficiency', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new Deficiency());
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}

	public function saveObservation(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Observation', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new Observation());
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}

	public function saveRecommendation(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Recommendation', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new Recommendation());
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}

	public function saveSupplementalObservation(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to SupplementalObservation', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new SupplementalObservation());
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}

	public function saveSupplementalRecommendation(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to SupplementalRecommendation', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new SupplementalRecommendation());
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}

	// Hazards Hub
	public function getAllHazardsAsTree() {
		$LOG = Logger::getLogger( 'Action:' . __function__ );
		$dao = $this->getDao(new Hazard());
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

	public function getAllHazards(){
		//FIXME: This public function should return a FLAT COLLECTION of ALL HAZARDS; not a Tree
		$LOG = Logger::getLogger( 'Action:' . __function__ );
		$dao = $this->getDao(new Hazard());
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
	}

	public function getHazardTreeNode( $id = NULL){

		// get the node hazard
		$hazard = $this->getHazardById($id);
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
	}


	//FIXME: Remove $name
	public function getHazardById( $id = NULL){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Hazard());
			$hazard = $dao->getById($id);

			return $hazard;
		}
		else{
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	/**
	 * Moves specified hazard to the specified parent
	 */
	public function moveHazardToParent($hazardId = NULL, $parentHazardId = NULL){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		//Get ids
		$hazardId = $this->getValueFromRequest('hazardId', $hazardId);
		$parentHazardId = $this->getValueFromRequest('parentHazardId', $parentHazardId);

		//validate values
		if( $hazardId === NULL || $parentHazardId === NULL ){
			return new ActionError("Invalid Hazard IDs specified: hazardId=$hazardId parentHazardId=$parentHazardId");
		}
		else{
			$LOG->debug("Moving Hazard #$hazardId to new parent Hazard #$parentHazardId");

			$dao = $this->getDao(new Hazard());

			// get Hazard by ID
			$hazard = $this->getHazardById( $hazardId );
			$LOG->trace("Loaded Hazard to move: $hazard");

			$hazard->setParent_hazard_id=$parentHazardId;
			// Save

			$dao->save($hazard);

			//TODO: What do we return?
			$LOG->info("Moved Hazard #$hazardId to new parent Hazard #$parentHazardId");
			return '';
		}
	}

	public function saveHazard(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();

		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Hazard', 202);
		}
		else if( $decodedObject instanceof ActionError ){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new Hazard());
			$hazard = $decodedObject;
			//set the hazard's order index, if it doesn't already have one
			if($decodedObject->getOrder_index() == null){

				//get the hazard's siblings
				if($decodedObject->getParent_hazard_id() != null){
					$parentDao = $this->getDao( new Hazard() );
					$parentHazard = $parentDao->getById( $hazard->getParent_hazard_id() );
					$siblings = $parentHazard->getSubHazards();
					$count = count( $siblings );

					if( $this->getIsAlphabetized( $siblings ) ){

						//the list is in alphabetical order.  Find the right spot for the new hazard
						for($i = 0; $i < count( $siblings ); ++$i) {

							//find the first hazard that comes after the new one in alphabetical order
							if( lcfirst( $hazard->getName() ) < lcfirst( $siblings[$i]->getName() ) ){

								//is our new hazard first in alphabetical order?
								if($i == 0){
									$LOG->debug($hazard->getName().' came first in order, before '. $siblings[$i]->getName());
									$beforeIdx = $siblings[0]->getOrder_index();
									$hazard->setOrder_index( $beforeIdx - 1 );
									break;

								}else{
									$LOG->debug($hazard->getName().' came somewhere in the middle.');
									//our hazard is somewhere between first and last
									$beforeIdx = $siblings[$i-1]->getOrder_index();
									$afterIdx  = $siblings[$i]->getOrder_index();
									$hazard->setOrder_index( ( $beforeIdx + $afterIdx )/2 );
									break;
								}

							}elseif($i == $count-1){
								$LOG->debug($hazard->getName().' came last in order');
								//our new hazard is last in alphabetical order
								$beforeIdx = $siblings[$count-1]->getOrder_index();
								$hazard->setOrder_index( $beforeIdx + 1 );
								break;
							}
						}
					}else{
						//the list is not alphebetized.  Put the new hazard at the end of the list.
						$LOG->debug('list was not alphabetized');
						$hazard->setOrder_index( $siblings[$count-1]->getOrder_index()+1 );
					}
				}
			}
			$dao->save($decodedObject);

			return $decodedObject;
		}
	}

	public function getIsAlphabetized( $list ){
		$LOG = Logger::getLogger('Action:' . __function__);

		$length = count($list);

		foreach($list as $key=>$hazard){
			if( $key != $length-1 && lcfirst( $list[$key]->getName() ) > lcfirst( $list[$key+1]->getName() ) )
			return false;
		}

		return true;
	}

	public function saveRoom(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();

		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Hazard', 202);
		}
		else if( $decodedObject instanceof ActionError ){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new Room());
			$decodedObject = $dao->save($decodedObject);
			return $decodedObject;
		}
	}

	public function removeResponse( $id = NULL ){
		$LOG = Logger::getLogger('Action:' . __function__);

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Response());

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
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function removeDeficiencySelection( $deficiencyId = NULL, $inspectionId = NULL ){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		$LOG->debug($decodedObject);

		if( $decodedObject === NULL  && $deficiencyId == NULL && $inspectionId == NULL ){
			return new ActionError('Error converting input stream to DeficiencySelection');
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{

			if( $decodedObject === NULL  ){
				$dao = $this->getDao(new DeficiencySelection());
				$ds = $dao->getById( $deficiencyId );
				$roomIds = array();
				foreach( $ds->getRooms() as $room ){
					array_push($roomIds, $room->getKey_id());
				}

			}else{
				// check to see if the roomIds array is populated
				$roomIds = $decodedObject->getRoomIds();
			}


			// start by saving or updating the object.
			$dao = $this->getDao(new DeficiencySelection());
			$ds = $dao->getByid($decodedObject->getKey_id());


			foreach ( $ds->getCorrectiveActions() as $action ){
				$dao->removeRelatedItems($action->getKey_id(),$ds->getKey_id(),DataRelationship::fromArray(DeficiencySelection::$CORRECTIVE_ACTIONS_RELATIONSHIP));
			}

			// if roomIds were provided then delete them
			if (!empty($roomIds)){
				foreach ($roomIds as $id){
					$dao->removeRelatedItems($id,$ds->getKey_id(),DataRelationship::fromArray(DeficiencySelection::$ROOMS_RELATIONSHIP));
				}
				
				//if we have removed all the rooms, delete this DeficiencySelection
				
				//clear out our rooms
				$ds->setRooms(null);
				//get a new collection from the db
				if($ds->getRooms() == NULL){
					$dao->deleteById($ds->getKey_id());
				}

   				
			// else if no roomIds were provided, then just delete this DeficiencySelection
			} else {
				$dao->deleteById($ds->getKey_id());
				return true;
			}

			return true;

		}
	}

	public function addCorrectedInInspection( $deficiencyId = NULL, $inspectionId = NULL ){
		$LOG = Logger::getLogger('Action:' . __function__);

		$inspectionId = $this->getValueFromRequest('inspectionId', $inspectionId);
		$deficiencyId = $this->getValueFromRequest('deficiencyId', $deficiencyId);

		if( $inspectionId !== NULL  && $deficiencyId!== NULL){

			// Find the deficiencySelection
			$ds = $this->getDeficiencySelectionByInspectionIdAndDeficiencyId($inspectionId,$deficiencyId);

			if ($ds == null){
				return new ActionError("Couldn't find DeficiencySelection for that Inspection and Deficiency");
			}

			$LOG->debug("Prepare to add Corrected flag to DeficiencySelection: $ds->getKey_id()");

			$dao = $this->getDao($ds);
			$ds->setCorrected_in_inspection(true);

			$dao->save($ds);

			return true;
			}
			else{
				//error
				return new ActionError("Must provide parameters deficiencyId and inspectionId");
			}
	}

	public function removeCorrectedInInspection( $deficiencyId = NULL, $inspectionId = NULL ){
		$LOG = Logger::getLogger('Action:' . __function__);

		$inspectionId = $this->getValueFromRequest('inspectionId', $inspectionId);
		$deficiencyId = $this->getValueFromRequest('deficiencyId', $deficiencyId);

		if( $inspectionId !== NULL  && $deficiencyId!== NULL){

			// Find the deficiencySelection
			$ds = $this->getDeficiencySelectionByInspectionIdAndDeficiencyId($inspectionId,$deficiencyId);

			if ($ds == null){
				return new ActionError("Couldn't find DeficiencySelection for that Inspection and Deficiency");
			}

			$LOG->debug("Prepare to remove Corrected flag from DeficiencySelection: $ds->getKey_id()");

			$dao = $this->getDao($ds);
			$ds->setCorrected_in_inspection(false);

			$dao->save($ds);

			return true;
			}
			else{
				//error
				return new ActionError("Must provide parameters deficiencyId and inspectionId");
			}
	}

	public function getDeficiencySelectionByInspectionIdAndDeficiencyId($inspectionId = null,$deficiencyId = null){
		$LOG = Logger::getLogger('Action:' . __function__);

		$dao = $this->getDao(new Inspection());
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

	public function saveBuilding(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();

		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Building', 202);
		}
		else if( $decodedObject instanceof ActionError ){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new Building());
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}
	//public function saveChecklist(){ }	//DUPLICATE public function

	// Question Hub
	public function getQuestionById( $id = NULL ){

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Question());
			return $dao->getById($id);
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function saveRecommendationRelation(){

		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$decodedObject = $this->convertInputJson();

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
				$dao = $this->getDao(new Response());
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

	}

	public function saveObservationRelation(){

		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$decodedObject = $this->convertInputJson();

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
				$dao = $this->getDao(new Response());
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

	}

	public function getInspector( $id = NULL ){
		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Inspector());
			return $dao->getById($id);
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function getAllInspectors(){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$dao = $this->getDao(new Inspector());

		return $dao->getAll();
	}

	// Inspection, step 1 (PI / Room assessment)
	public function getPIById( $id = NULL ){

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new PrincipalInvestigator());
			return $dao->getById($id);
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function getAllPIs(){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$dao = $this->getDao(new PrincipalInvestigator());

		return $dao->getAll();
	}

	public function getAllRooms(){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$dao = $this->getDao(new Room());

		return $dao->getAll();
	}

	public function getRoomsByPIId( $id = NULL ){
		//Get responses for Inspection
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$piId = $this->getValueFromRequest('piId', $piId);

		if( $piId !== NULL ){

			$pi = $this->getPIById($piId);

			$rooms = $pi->getRooms();

			return $rooms;
		}
		else{
			//error
			return new ActionError("No request parameter 'inspectionId' was provided");
		}
	}

	public function getRoomById( $id = NULL ){
		$id = $this->getValueFromRequest('id', $id);

		$LOG = Logger::getLogger( 'Action:' . __function__ );
		$LOG->trace('getting room');

		if( $id !== NULL ){
			$dao = $this->getDao(new Room());
			return $dao->getById($id);
		}
		else{
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function getPIsByRoomId( $id = NULL ){

		$id = $this->getValueFromRequest('id', $id);

		$LOG = Logger::getLogger( 'Action:' . __function__ );
		$LOG->trace('getting room');

		if( $id !== NULL ){
			$dao = $this->getDao(new Room());
			$room =  $dao->getById($id);
			return $room->getPrincipalInvestigators();
		}
		else{
			return new ActionError("No request parameter 'id' was provided");
		}
	}

	public function savePI(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Observation', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new PrincipalInvestigator());
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}

	public function saveInspector(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Observation', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new Inspector());
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}

	public function savePIRoomRelation($PIId = NULL,$roomId = NULL,$add= NULL){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$decodedObject = $this->convertInputJson();

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
				$dao = $this->getDao(new PrincipalInvestigator());
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
	}

	public function savePIContactRelation($PIId = NULL,$contactId = NULL,$add= NULL){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$decodedObject = $this->convertInputJson();

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
				$dao = $this->getDao(new PrincipalInvestigator());
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
	}

	public function savePIDepartmentRelation($PIID = NULL,$deptId = NULL,$add= NULL){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$decodedObject = $this->convertInputJson();

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

			$pi = $this->getPIById($PIId);
			$departments = $pi->getDepartments();
			$departmentToAdd = $this->getDepartmentById($deptId);

			if( $PIId !== NULL && $deptId !== NULL && $add !== null ){

				// Get a DAO
				$dao = $this->getDao(new PrincipalInvestigator());
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
	}


	public function saveUserRoleRelation($userID = NULL,$roleId = NULL,$add= NULL){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$decodedObject = $this->convertInputJson();

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
				$user = $this->getUserById($userID);
				$roles = $user->getRoles();
				$roleToAdd = $this->getRoleById($roleId);

				// Get a DAO
				$dao = $this->getDao(new User());
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
	}

	//Get a room dto duple
	public function getRoomDtoByRoomId( $id = NULL, $roomName = null, $containsHazard = null, $isAllowed = null ) {
		$id = $this->getValueFromRequest('id', $id);

		$LOG = Logger::getLogger( 'Action:' . __function__ );
		$LOG->trace('getting room');

		if( $id !== NULL ){
			$dao = $this->getDao();
			$room = $dao->getRoomById($id);

			$roomDto = new RoomDto($room->getKey_Id(), $room->getName(), $containsHazard, $isAllowed);

			return $roomDto;
		}
		else{
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function getAllDepartments(){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$dao = $this->getDao(new Department());

		return $dao->getAll();
	}

	public function getAllActiveDepartments(){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$dao = $this->getDao(new Department());

		return $dao->getAll('name', false, true);
	}


	public function getDepartmentById( $id = NULL ){
		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Department());
			return $dao->getById($id);
		}
		else{
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function saveDepartment($department = null){
		$LOG = Logger::getLogger('Action:' . __function__);
		if($department == null){
			$decodedObject = $this->convertInputJson();
		}else{
			$decodedObject = $department;
		}

		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Hazard', 202);
		}
		else if( $decodedObject instanceof ActionError ){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new Department());
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}

	public function getAllBuildings( $id = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$dao = $this->getDao(new Building());

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

	public function getBuildingById( $id = NULL ){
		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Building());
			return $dao->getById($id);
		}
		else{
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function initiateInspection($inspectionId = NULL,$piId = NULL,$inspectorIds= NULL){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$inspectionId = $this->getValueFromRequest('inspectionId', $inspectionId);
		$piId = $this->getValueFromRequest('piId', $piId);
		$inspectorIds = $this->getValueFromRequest('inspectorIds', $inspectorIds);

		if( $piId !== NULL && $inspectorIds !== null ){

			// Get this room
			$inspection = new Inspection();
			$dao = $this->getDao($inspection);

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
	}

	//Appropriately sets relationships for an inspection if an inspector is not inspecting all of a PI's rooms
	public function resetInspectionRooms($inspectionId = NULL, $roomIds = null){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$inspectionId = $this->getValueFromRequest('inspectionId', $inspectionId);
		$roomIds = $this->getValueFromRequest('roomIds', $roomIds);

		if($roomIds !== NULL && $inspectionId !== NULL ){
			$dao = $this->getDao(New Inspection());
			$inspectionDao = $dao->getById($inspectionId);

			$LOG->debug($inspectionDao);

			$this->removeAllInspectionRooms($inspectionDao);

			foreach($roomIds as $id){
				$this->saveInspectionRoomRelation( $id, $inspectionId, true );
			}

		} else {
			return new ActionError("No Inspection or room IDs provided");
		}

		return $this->getHazardRoomMappingsAsTree( $roomIds );

	}

	public function removeAllInspectionRooms(&$inspectionDao){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$inspectionId = $inspectionDao->getKey_id();

		foreach($inspectionDao->getRooms() as $room){
			$this->saveInspectionRoomRelation( $room->getKey_id(), $inspectionId, false );
		}

	}

	public function saveInspectionRoomRelation($roomId = NULL,$inspectionId = NULL,$add= NULL){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$roomId = $this->getValueFromRequest('roomId', $roomId);
		$inspectionId = $this->getValueFromRequest('inspectionId', $inspectionId);
		$add = $this->getValueFromRequest('add', $add);

		if( $roomId !== NULL && $inspectionId !== NULL && $add !== null ){

			// Get this inspection
			$dao = $this->getDao(new Inspection());
			$inspection = $dao->getById($inspectionId);
			// if add is true, add this room to this inspection
			if ($add){
				$dao->addRelatedItems($roomId,$inspectionId,DataRelationship::fromArray(Inspection::$ROOMS_RELATIONSHIP));
			// if add is false, remove this room from this inspection
			} else {
				$dao->removeRelatedItems($roomId,$inspectionId,DataRelationship::fromArray(Inspection::$ROOMS_RELATIONSHIP));
			}

		} else {
			//error
			return new ActionError("Missing proper parameters (should be roomId int, inspectionId int, add boolean)");
		}
		return true;

	}

	public function saveInspection(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Inspection', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{

			$dao = $this->getDao(new Inspection());

			// Save the Inspection
			$inspection = $dao->save($decodedObject);

			return $inspection;
		}
	}

	public function saveNoteForInspection(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Inspection', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{

			$dao = $this->getDao(new Inspection());

			// Get the inspection and update its Note property
			$inspection = $dao->getById($decodedObject->getEntity_id());
			$inspection->setNote($decodedObject->getText());

			// Save the Inspection
			$dao->save($inspection);

			return true;
		}
	}

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
	public function getHazardRoomMappingsAsTree( $roomIds = NULL, $hazard = null ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );
		$roomIdsCsv = $this->getValueFromRequest('roomIds', $roomIds);

		if( $roomIdsCsv !== NULL ){
			$roomIdsString = implode(', ', $roomIdsCsv);
			$LOG->debug("Retrieving Hazard-Room mappings for Rooms: $roomIdsString");


			$LOG->debug('Identified ' . count($roomIdsCsv) . ' Rooms');
			$LOG->debug($roomIdsCsv);
			//Get all hazards
			if($hazard != null){
			  $allHazards = $hazard;
			}else{
			  $allHazards = $this->getAllHazardsAsTree();
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
			$roomDao = $this->getDao(new Room());

			// Create an array of Room Objects
			foreach($roomIdsCsv as $roomId) {
				array_push($rooms,$roomDao->getById($roomId));
			}
			$subs = $allHazards->getActiveSubHazards();

			// filter by room
			foreach ($subs as $subhazard){
				if($subhazard->getKey_id() != 9999){
					$entityMaps = array();
					$entityMaps[] = new EntityMap("lazy","getSubHazards");
					$entityMaps[] = new EntityMap("eager","getActiveSubHazards");
					$entityMaps[] = new EntityMap("lazy","getChecklist");
					$entityMaps[] = new EntityMap("lazy","getRooms");
					$entityMaps[] = new EntityMap("eager","getInspectionRooms");
					$entityMaps[] = new EntityMap("eager","getHasChildren");
					$entityMaps[] = new EntityMap("lazy","getParentIds");
					$subhazard->setEntityMaps($entityMaps);
					//Skip General Hazards
					$this->filterHazards($subhazard,$rooms);
				}else{
					$subs = $this->unsetValue( $subs, $subhazard );
					$allHazards->setSubHazards($subs);
				}
			}
			return $allHazards;
		}
		else{
			//error
			return new ActionError("No request parameter 'roomIds' was provided");
		}
	}
	private function unsetValue(array $array, $value, $strict = TRUE)
	{
	    if(($key = array_search($value, $array, $strict)) !== FALSE) {
	        unset($array[$key]);
	    }
	    return $array;
	}

	public function filterHazards (&$hazard, $rooms){
		$LOG = Logger::getLogger( 'Action:' . __function__ );
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
			$LOG = Logger::getLogger( 'Action within loop:' . __function__ );
			//if we have called this public function by passing a hazard from the view, the subhazards will be read as arrays rather than objects, because php
			if(!is_object($subhazard)){
				$subDao = $this->getDao(new Hazard());
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
				$this->filterHazards($subhazard, $rooms);
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

	//UTILITY public function FOR getHazardRoomMappingsAsTree
	public function getHazardRoomMappings($hazard, $rooms, $searchRoomIds, $parentIds = null){
		$searchRoomIds = $searchRoomIds;
		$LOG = Logger::getLogger( 'Action:' . __function__ );
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

			$node = $this->getHazardRoomMappings($subHazard, $rooms, $searchRoomIds, $parentIds);
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

	public function getHazardsInRoom( $roomId = NULL, $subHazards ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$roomId = $this->getValueFromRequest('roomId', $roomId);
		$subHazards = $this->getValueFromRequest('subHazards', $subHazards);
		$LOG->debug("subHazards is $subHazards, roomId is $roomId");


		if( $roomId !== NULL ){

			$dao = $this->getDao(new Room());

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
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}
	public function getHazardRoomRelations( $roomIds = NULL ){
		$roomIdsCsv = getValueFromRequest('roomIds', $roomIds);

		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getHazards");
		$entityMaps[] = new EntityMap("eager","getHazardRoomRelations");

		$hazardRoomRelations = array();

		if( $roomIdsCsv !== NULL ){
			$LOG->debug("Retrieving Hazard-Room mappings for Rooms: $roomIdsCsv");


			$LOG->debug('Identified ' . count($roomIdsCsv) . ' Rooms');
			$LOG->debug($roomIdsCsv);

			//get the rooms
			foreach( $roomIdsCsv as $roomId ){
				$roomDao = $this->getDao( new Room() );
				$room = $roomDao->getById( $roomId );
				$hazardRoomRelations = array_merge($hazardRoomRelations, $room->getHazardRoomRelations());
			}
		}

		return $hazardRoomRelations;
	}



	public function saveHazardRoomRelations( $hazard = null ){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Inspection');
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{

			$dao = $this->getDao(new Hazard());
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

			$LOG->debug($hazard);

			//make sure we send back the child hazards with a collection of inspection rooms, and that those rooms do not contain the child hazard
			$inspectionRooms = $hazard->getInspectionRooms();
			foreach($inspectionRooms as $room){
				$room->setContainsHazard(false);
			}

			//get the parent so that we can determine if it's present in each of the rooms
			$parentDao = $this->getDao(new Hazard());
			$parent    = $parentDao->getById($hazard->getParent_hazard_id());
			$parent->setInspectionRooms($hazard->getInspectionRooms());
			$parent->filterRooms();
			$parentRooms = $parent->getInspectionRooms();

			$parentsToSkip = array(10000,1,10009,10010);

			foreach($hazard->getInspectionRooms() as $i=>$room){
				if($hazard->getIsPresent() == true){
					$LOG->debug('hazard was present');
					//create hazard room relations

					if( !in_array($parent->getKey_id(), $parentsToSkip) ){
						if($parentRooms[$i]->getContainsHazard() == true){
							$this->saveHazardRelation($room->getKey_id(),$hazard->getKey_id(),true);
							$room->setContainsHazard(true);
						}
					}else{
						$LOG->debug('hazard was a child of root');
						$this->saveHazardRelation($room->getKey_id(),$hazard->getKey_id(),true);
						$room->setContainsHazard(true);
					}

					foreach($hazard->getActiveSubHazards() as $subhazard){
						$subhazard->setInspectionRooms($inspectionRooms);

						$subEntityMaps = array();
						$subEntityMaps[] = new EntityMap("lazy","getSubHazards");
						$subEntityMaps[] = new EntityMap("lazy","getActiveSubHazards");
						$subEntityMaps[] = new EntityMap("lazy","getChecklist");
						$subEntityMaps[] = new EntityMap("lazy","getRooms");
						$subEntityMaps[] = new EntityMap("eager","getInspectionRooms");
						$subEntityMaps[] = new EntityMap("eager","getHasChildren");

						$subhazard->setEntityMaps($subEntityMaps);
					}

				}else{
					//delete room relations
					$this->saveHazardRelation($room->getKey_id(),$hazard->getKey_id(),false);
					//we must also remove the relationships for any subhazards, so we call recursively
					foreach($hazard->getActiveSubHazards() as $subhazard){
						$this->saveHazardRelation($room->getKey_id(),$subhazard->getKey_id(),false);
						$subhazard->filterRooms();
					}
				}
			}


			$hazard->filterRooms();
			$LOG->debug($hazard);
			//$this->filterHazards($hazard, $hazard->getInspectionRooms());
			return $hazard;

		}
	}

	public function saveHazardRelation($roomId = NULL,$hazardId = NULL,$add= NULL, $recurse = NULL){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		if($roomId == null)$roomId = $this->getValueFromRequest('roomId', $roomId);
		if($hazardId == null)$hazardId = $this->getValueFromRequest('hazardId', $hazardId);
		if($add == null)$add = $this->getValueFromRequest('add', $add);
		if($recurse == null)$recurse = $this->getValueFromRequest('recurse', $recurse);


		if( $roomId !== NULL && $hazardId !== NULL && $add !== null ){
			$LOG->debug("ADD's type: ".gettype($add)." add's value: ".$add);
			// Get this room
			$dao = $this->getDao(new Room());
			$room = $dao->getById($roomId);
			// if add is true, add this hazard to this room
			if ($add != false){
				$dao->addRelatedItems($hazardId,$roomId,DataRelationship::fromArray(Room::$HAZARDS_RELATIONSHIP));
			// if add is false, remove this hazard from this room
			} else {
				$hazDao = $this->getDao(new Hazard());
				$hazard = $hazDao->getById($hazardId);
				$LOG->debug("removing " . $hazard->getName() . " from room with key id" . $roomId);
				$dao->removeRelatedItems($hazardId,$roomId,DataRelationship::fromArray(Room::$HAZARDS_RELATIONSHIP));

				//if we are recursing, we need to get the subhazards
				if($recurse == true){
					$subs = $hazard->getActiveSubHazards();
					foreach ($subs as $sub){
						$this->saveHazardRelation($roomId,$sub->getKey_id(),false,true);
					}
				}

			}

		} else {
			//error
			return new ActionError("Missing proper parameters (should be roomId int, hazardId int, add boolean)");
		}
		return true;

	}

	public function getSubHazards($hazard = NULL){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Inspection');
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{

			$dao = $this->getDao(new Hazard());
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

			$this->filterHazards($hazard, $hazard->getInspectionRooms());
			return $hazard->getActiveSubHazards();
		}
	}

	public function saveRoomRelation($hazardId, $roomId){
		//temporarily return true so server returns 200 code
		return true;
	}

	// Inspection, step 3 (Checklist)
	//public function getQuestions(){ }	//DUPLICATE public function
	public function getDeficiencyById( $id = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Deficiency());
			$keyid = $id;

			// query for Inspection with the specified ID
			return $dao->getById($id);
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function saveResponse(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to Response', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new Response());
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}

	public function saveDeficiencySelection(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to DeficiencySelection', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			// check to see if the roomIds array is populated
			$roomIds = $decodedObject->getRoomIds();

			// start by saving or updating the object.
			$dao = $this->getDao(new DeficiencySelection());
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

			$selection = $dao->getById($ds->getKey_id());	
			$LOG->debug($selection);
			
			return $selection;

		}
	}

	public function saveCorrectiveAction(){
		$LOG = Logger::getLogger('Action:' . __function__);
		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to CorrectiveAction', 202);
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			$dao = $this->getDao(new CorrectiveAction());
			$dao->save($decodedObject);
			return $decodedObject;
		}
	}

	public function getChecklistsForInspection( $id = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );
		$id = $this->getValueFromRequest('id', $id);
		if( $id !== NULL ){
			$dao = $this->getDao(new Inspection());

			//get inspection
			$inspection = $dao->getById($id);
			// get the rooms for the inspection
			$rooms = $inspection->getRooms();
			$masterHazards = array();
			//iterate the rooms and find the hazards present
			foreach ($rooms as $room){
				$hazardlist = $this->getHazardsInRoom($room->getKey_id());
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

	public function getInspectionById( $id = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Inspection());

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
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function getInspectionsByPIId( $id = NULL ){
		//Get responses for Inspection
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$piId = $this->getValueFromRequest('piId', $piId);

		if( $piId !== NULL ){

			$pi = $this->getPIById($piId);

			$inspections = $pi->getInspections();

			return $inspections;
		}
		else{
			//error
			return new ActionError("No request parameter 'inspectionId' was provided");
		}
	}

	public function resetChecklists( $id = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Inspection());

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


			$entityMaps = array();
			$entityMaps[] = new EntityMap("eager","getInspectors");
			$entityMaps[] = new EntityMap("eager","getRooms");
			$entityMaps[] = new EntityMap("lazy","getResponses");
			$entityMaps[] = new EntityMap("eager","getPrincipalInvestigator");
			$entityMaps[] = new EntityMap("eager","getChecklists");
			$inspection->setEntityMaps($entityMaps);

			// Calculate the Checklists needed according to hazards currently present in the rooms covered by this inspection
			$checklists = $this->getChecklistsForInspection($inspection->getKey_id());
			// add the checklists to this inspection
			foreach ($checklists as $checklist){

				$dao->addRelatedItems($checklist->getKey_id(),$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$CHECKLISTS_RELATIONSHIP));
				$checklist->setInspectionId($inspection->getKey_id());
				$checklist->setRooms($inspection->getRooms());
				$checklist->filterRooms();

				$entityMaps = array();
				$entityMaps[] = new EntityMap("lazy","getHazard");
				$entityMaps[] = new EntityMap("lazy","getRooms");
				$entityMaps[] = new EntityMap("eager","getInspectionRooms");
				$entityMaps[] = new EntityMap("eager","getQuestions");
				$checklist->setEntityMaps($entityMaps);

			}
			$inspection->setChecklists($checklists);
			return $inspection;
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}


	// Inspection, step 4 (Review, deficiency report)
	public function getDeficiencySelectionsForResponse( $responseId = NULL){
		$responseId = $this->getValueFromRequest('responseId', $responseId);

		if( $responseId !== NULL ){
			$selections = array();

			for( $i = 0; $i < 2; $i++ ){
				$selection = $this->getDeficiencySelectionById($i);
				//TODO: set response ID?
				$selections[] = $selection;
			}

			return $selections;
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	//TODO: Observations?

	public function getRecommendationById( $id = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Recommendation());
			return $dao->getById($id);
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function getRecommendationsForResponse( $responseId = NULL ){
		//get Recommendations for Response

		$responseId = $this->getValueFromRequest('responseId', $responseId);

		if( $responseId !== NULL ){
			$recommendations = array();

			for( $i = 0; $i < 2; $i++ ){
				$recommendation = $this->getRecommendationById($i);
				//TODO: set response?
				$recommendations[] = $recommendation;
			}

			return $recommendations;
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function getObservationById( $id = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Observation());
			return $dao->getById($id);
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	public function getObservationsForResponse( $responseId = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );
		//get Observations for Response

		$responseId = $this->getValueFromRequest('responseId', $responseId);

		if( $responseId !== NULL ){
			$LOG->debug("Generating Observations for response #$responseId");

			$response = $this->getResponseById($id);
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
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	//TODO: remove HACK specifying inspection ID
	public function getResponseById( $id = NULL, $inspectionId = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$id = $this->getValueFromRequest('id', $id);

		if( $id !== NULL ){
			$dao = $this->getDao(new Response());
			return
			$response = $dao->getById($id);

			return $response;
		}
		else{
			//error
			return new ActionError("No request parameter 'id' was provided", 201);
		}
	}

	// Inspection, step 5 (Details, Full Report)
	public function getResponsesForInspection( $inspectionId = NULL){
		//Get responses for Inspection
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$inspectionId = $this->getValueFromRequest('inspectionId', $inspectionId);

		if( $inspectionId !== NULL ){
			$keyid = $inspectionId;

			//TODO: query for Responses with the specified Inspection ID
			$responses = array();
			for( $i = 0; $i < 5; $i++ ){
				$response = $this->getResponseById($i, $keyid);
				//TODO: set Inspection?
				$responses[] = $response;
			}

			return $responses;
		}
		else{
			//error
			return new ActionError("No request parameter 'inspectionId' was provided");
		}
	}

	public function login2($username,$password) {
		//Get responses for Inspection
		$LOG = Logger::getLogger( 'Action:' . __function__ );


		$username = $this->getValueFromRequest('username', $username);
		$password = $this->getValueFromRequest('password', $password);


		// Hardcoded username and password for "emergency accounts"
		if($username === "EmergencyUser" && $password === "RSMS911") {
			$emergencyAccount = true;
		}
		else {
			$emergencyAccount = false;
		}


		$ldap = new LDAP();

		// if successfully authenticates by LDAP:
		if ($ldap->IsAuthenticated($username,$password) || $emergencyAccount) {

			// Make sure they're an Erasmus user by username lookup
			$dao = $this->getDao(new User());

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

	public function lookupUser($username = NULL) {
		//Get responses for Inspection
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$username = $this->getValueFromRequest('username', $username);

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

	public function sendInspectionEmail(){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$decodedObject = $this->convertInputJson();
		if( $decodedObject === NULL ){
			return new ActionError('Error converting input stream to EmailDto');
		}
		else if( $decodedObject instanceof ActionError){
			return $decodedObject;
		}
		else{
			// Get this inspection
			$dao = $this->getDao(new Inspection());
			$inspection = $dao->getById($decodedObject->getEntity_id());

			//get the client side email text
			$text = $decodedObject->getText();

			// Init an array of recipient Email addresses and another of inspector email addresses
			$recipientEmails = array();
			$inspectorEmails = array();

			// We'll need a user Dao to get Users and find their email addresses
			$userDao = $this->getDao(new User());

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

	public function makeFancyNames(){
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
			$dao = $this->getDao( new User() );
			$dao->save( $user );
		}
		return getAllUsers();
	}

	public function createOrderIndicesForHazards(){
		$LOG = Logger::getLogger( 'Action:' . __function__ );
		$hazards = $this->getHazardTreeNode(10000);
		foreach($hazards as $hazard){
			$this->setOrderIndicesForSubHazards( $hazard );
		}
		return $this->getAllHazards();
	}

	public function setOrderIndicesForSubHazards( $hazard = NULL ){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		//if we have passed a hazard, use it, if not, use the input stream
		if($hazard == null){
			$hazardId = $this->getValueFromRequest('hazardId', $hazardId);
			if($hazardId == null)return new ActionError("No request parameter 'hazardId' was provided");
			$hazDao = $this->getDao( new Hazard() );
			$hazard = $hazDao->getById( $hazardId );

			return $hazard;
		}

		$subs = $hazard->getSubHazards();
		if($subs != null){
			$i = 0;
			foreach($subs as $sub){
				$i++;
				$sub->setOrder_index(null);
				$sub->setOrder_index($i);
				$dao = $this->getDao( new Hazard() );
				$LOG->debug($sub->getName()."'s order index is ".$sub->getOrder_index());
				$sub = $dao->save( $sub );
				if($sub->getSubHazards() != null) $this->setOrderIndicesForSubHazards( $sub );
			}
		}

		return $hazard;
	}

	//reorder hazards
	public function reorderHazards($hazardId = null, $beforeHazardId = null, $afterHazardId = null){
		$LOG = Logger::getLogger( 'Action:' . __function__ );

		$hazardId = $this->getValueFromRequest('hazardId', $hazardId);
		$beforeHazardId = $this->getValueFromRequest('beforeHazardId', $beforeHazardId);
		$afterHazardId = $this->getValueFromRequest('afterHazardId', $afterHazardId);

		$LOG->debug('BEFORE ID: '.$beforeHazardId);
		$LOG->debug('AFTER ID: '.$afterHazardId);

		if( $hazardId !== NULL){
			$dao = $this->getDao(new Hazard());
			$hazard = $dao->getById($hazardId);

			//if we are moving a hazard to the lowest order_index, we won't have a hazard before it
			if($beforeHazardId != NULL && $beforeHazardId != "null"){
				$beforeHazard = $dao->getById($beforeHazardId);
				$beforeOrderIdx = $beforeHazard->getOrder_index();
			}else{
				$LOG->debug('There is no before hazard');
				$beforeOrderIdx = 0;
			}

			//if we are moving a hazard to the last index, we won't have a hazard after it.
			if($afterHazardId != NULL && $afterHazardId != "null"){
				$afterHazard = $dao->getById($afterHazardId);
				$afterHazardIdx = $afterHazard->getOrder_index();
			}else{
				$afterHazardIdx = $beforeOrderIdx + 1;
			}

			$LOG->debug("before index: ".$beforeOrderIdx);
			$LOG->debug("after index: ".$afterHazardIdx);

			//set the hazard's order index to a float between halfway between the order indices of the other two hazards
			$hazard->setOrder_index(($beforeOrderIdx+$afterHazardIdx)/2);
			$dao->save($hazard);

			//we get the parent hazard and return it's subhazards because it is easier to keep the order of its subhazards synched between server and view
			return $this->getHazardTreeNode($hazard->getParent_hazard_id());
		}
		else{
			//error
			if($hazardId == null)return new ActionError("No request parameter 'hazardId' was provided");
			if($beforeHazardId == null)return new ActionError("No request parameter 'beforeHazardId' was provided");
			if($afterHazardId == null)return new ActionError("No request parameter 'afterHazardId' was provided");
		}

	}
	//generate a random float
	public function random_float ($min,$max) {
		return ($min+lcg_value()*(abs($max-$min)));
	}
}
?>

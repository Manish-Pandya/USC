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

    /**
     * Chooses a return value based on the parameters. If $paramValue
     * is specified, it is returned. Otherwise, $valueName is taken from $_REQUEST.
     *
     * If $valueName is not present in $_REQUEST, NULL is returned.
     *
     * @param string|unkown $valueName
     * @param string $paramValue
     * @return string|unknown|NULL
     */
    public function getValueFromRequest( $valueName, $paramValue = NULL ){
        $LOG = Logger::getLogger('Action:' . __function__);

        if( $paramValue !== NULL ){
            return $paramValue;
        }
        else if( array_key_exists($valueName, $_REQUEST)){
            if(stristr($_REQUEST[ $valueName ], "null"))return null;
            if(stristr($_REQUEST[ $valueName ], "false")){
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

    public function getInputFile(){
    	try{
    		$decodedObject = JsonManager::getFile();

    		if( $decodedObject === NULL ){
    			return new ActionError('No data read from input stream');
    		}

    		return $decodedObject;
    	}
    	catch(Exception $e){
    		return new ActionError("Unable to decode FILE. Cause: $e");
    	}
    }

    /*
     *
     * @return GenericDAO
     */

    public function getDao( $modelObject = NULL ){
        //FIXME: Remove MockDAO
        if( $modelObject === NULL ){
            return new MockDAO();
        }
        else{
            return new GenericDAO( $modelObject );
        }
    }

    public function getCurrentUserRoles( $user = NULL ){
        $LOG = Logger::getLogger('Action:' . __function__);

        if(array_key_exists("USER", $_SESSION) && $_SESSION['USER'] != NULL && $user == NULL) {
            $user = $_SESSION['USER'];
        } elseif($user != NULL) {
            $_SESSION['USER'] = $user;
        }else{
            return false;
        }

        $roles = array();
        $roles["allRoles"] = array();
        //put an array of all possible roles into the session so we can use it for comparison on the client
        foreach($this->getAllRoles() as $role){
            $roles["allRoles"][] = array($role->getName() => $role->getBit_value());
        }


        //sum up the users roles into a single integer to represent their permission set
        $roles['userPermissions'] = 0;
        $roles['userRoles'] = array();
        foreach($user->getRoles() as $role){
            //$LOG->debug($role);
            $roles['userPermissions'] += $role->getBit_value();
            $roles['userRoles'][] = $role->getName();
        }

        return $roles;
    }

     public function loginAction( $username = NULL ,$password = NULL, $destination = NULL ) {
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $username = $this->getValueFromRequest('username', $username);
        $password = $this->getValueFromRequest('password', $password);
        $destination = $this->getValueFromRequest('destination', $destination);

        if($destination != NULL)$_SESSION['DESTINATION'] = $destination;

        if(!isProduction()){

            // Make sure they're an Erasmus user by username lookup
            $dao = $this->getDao(new User());
            $user = $this->getUserById(1);

            if ($user != null) {
                // ROLE assignment will be based on username, if it directly matches a role name
                $roles = array();
                foreach($this->getAllRoles() as $role) {
                    $roles[] = $role->getName();
                }
                //the name of a real role was input in the form
                if ( in_array($username, $roles) ) {
                    $roleDao = $this->getDao(new Role());
                    $whereClauseGroup = new WhereClauseGroup(array(new WhereClause("name", "=", $username)));
                    $fakeRoles = $roleDao->getAllWhere($whereClauseGroup);

                    $user->setFirst_name("Test user with role:");
                    $user->setLast_name($username);
                    if($username != "Principal Investigator"){
                        $user->setSupervisor_id(1);
                    }else{
                        $principalInvestigator = $this->getPIById(1);
                        $user->setPrincipalInvestigator($principalInvestigator);
                    }

                    $user->setRoles($fakeRoles);
                    $_SESSION['ROLE'] = $this->getCurrentUserRoles($user);

                }
                //the name of a real role was NOT input in the form, get the actual user's roles
                else {
                    $_SESSION['ROLE'] = $this->getCurrentUserRoles($user);
                }

                // put the USER into session
                $_SESSION['USER'] = $user;

                $LOG->debug($_SESSION['ROLE']['userRoles']);

                //get the proper destination based on the user's role
                $nonLabRoles = array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector", "EmergencyUser");
                $LOG->debug(count(array_intersect($_SESSION['ROLE']['userRoles'], $nonLabRoles)));
                if( count(array_intersect($_SESSION['ROLE']['userRoles'], $nonLabRoles)) != 0 ){
                    if($destination == NULL){
                        $_SESSION["DESTINATION"] = 'views/RSMSCenter.php';
                    }
                }
                else{
                    if($destination == NULL)$_SESSION["DESTINATION"] = 'views/lab/MyLab.php';
                }

                if(isset($_SESSION["REDIRECT"])){
                	$LOG->fatal("should redirect");
                	$_SESSION['DESTINATION'] = $this->getDestination();
                }

                // return true to indicate success
                return true;
            } else {
                // successful LDAP login, but not an authorized Erasmus user, return false
                $_SESSION['DESTINATION'] = LOGIN_PAGE;
                return false;
            }
        }else{
            // Hardcoded username and password for "emergency accounts"
            if($username === "EmergencyUser" && $password === "RSMS911") {
                $emergencyAccount = true;
            }
            else {
                $emergencyAccount = false;
            }

            // ROLE assignment will be based on username, if it directly matches a role name
            $roles = array();
            foreach($this->getAllRoles() as $role) {
                $roles[] = $role->getName();
            }
            //the name of a real role was input in the form
            if ( in_array($username, $roles) || $username == "EmergencyUser") {

                if($username != "EmergencyUser"){
                    if($password != "correcthorsebatterystaple"){
                        $_SESSION['ERROR'] = "The username or password you entered was incorrect.";
                        return false;
                    }
                    $user = $this->getUserById(1);
                    $roleDao = $this->getDao(new Role());
                    $whereClauseGroup = new WhereClauseGroup(array(new WhereClause("name", "=", $username)));
                    $fakeRoles = $roleDao->getAllWhere($whereClauseGroup);



                    if($username != "Principal Investigator"){
                        $user->setSupervisor_id(1);
                        $user->setInspector_id(10);
                        $user->setInspector($this->getInspector(10));
                    }else{
                        $principalInvestigator = $this->getPIById(1);
                        $user->setPrincipalInvestigator($principalInvestigator);
                    }
                    $user->setRoles(NULL);
                    $user->setRoles($fakeRoles);
                }else{
                    $user = $this->getUserById(911);
                }

                $user->setFirst_name("Test user with role:");
                $user->setLast_name($username);

                $_SESSION['ROLE'] = $this->getCurrentUserRoles($user);
                // put the USER into session
                $_SESSION['USER'] = $user;

                $_SESSION['DESTINATION'] = $this->getDestination();
                // return true to indicate success
                return true;

            }

            $ldap = new LDAP();

            // if successfully authenticates by LDAP:
            if ($ldap->IsAuthenticated($username,$password) || $emergencyAccount) {

                // Make sure they're an Erasmus user by username lookup
                $dao = $this->getDao(new User());
                $user = $dao->getUserByUsername($username);
                $LOG->debug($user);
                if ($user != null) {
                    // ROLE assignment will be based on username, if it directly matches a role name
                    $roles = array();
                    foreach($this->getAllRoles() as $role) {
                        $roles[] = $role->getName();
                    }

                    //the name of a real role was NOT input in the form, get the actual user's roles
                    $_SESSION['ROLE'] = $this->getCurrentUserRoles($user);

                    // put the USER into session
                    $_SESSION['USER'] = $user;

                    $_SESSION['DESTINATION'] = $this->getDestination();
                    // return true to indicate success
                    return true;
                } else {
                    // successful LDAP login, but not an authorized Erasmus user, return false
                     $_SESSION['ERROR'] = "The username or password you entered was incorrect.";
                     return false;
                }
            }
        }

        // otherwise, return false to indicate failure
        $_SESSION['ERROR'] = "The username or password you entered was incorrect.";
        return false;
    }

    /**
     * Determines if the currently logged in user's lab has permissions to run a method, based on the object type being retrieved, altered or created by that method
     *
     * @param unknown $object
     * @return boolean
     */
    private function getByLab($object){
        //if the current user is an Admin or Inspector, we don't need to filter by lab
        $roles = $this->getCurrentUserRoles();
        $ehsRoles = array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector");
        if(count(array_intersect($roles['userRoles'], $ehsRoles)) > 0){
            return true;
        }

        //Does the PrincipalInvestigator associated with the currently logged in user match the PrincipalInvestigator who owns the object we want to get?
        if($this->getPIIDFromObject($object) == $this->getPIIDFromUser()){
            return true;
        }

        return false;

    }

    /**
     * Gets the key_id of the PrincipalInvestigator associated with a method call.  Used to determine if a User can make a call for a particular object instance.
     *
     * @param unkonwn object
     * @return integer $value| boolean
     */
    protected function getPIIDFromObject($object){
        $LOG = Logger::getLogger('Action:' . __function__);
        //method chains
        $map = array(
                "CorrectiveAction"=>array("getDeficiencySelection", "getResponse", "getInspection", "getPrincipal_investigator_id")
        );

        $value = $object;

        foreach($map[get_class($object)] as $method){
            if($value == NULL)return false;
            $value = $value->$method();
        }

        return $value;
    }

    /**
     * Gets the key_id of the PrincipalInvestigator associated with the user who is currently logged in
     *
     * @param User $user
     * @return integer $value| boolean
     */
    private function getPIIDFromUser(){
        $LOG = Logger::getLogger('Action:' . __function__);
        $user = $this->getCurrentUser();
        $roles = getCurrentUserRoles();

        if(in_array("Principal Investigator", $roles['userRoles'])){
            if($user->getPrincipalInvestigator() != NULL){
                return $user->getPrincipalInvestigator()->getKey_id();
            }
            return false;
        }elseif(in_array("Lab Contact", $roles['userRoles'])){
            if($user->getSupervisor_id() != NULL){
                return $user->getSupervisor_id();
            }
            return false;
        }

        return false;
    }

    private function getDestination(){
     $LOG = Logger::getLogger("" . __function__);
     //get the proper destination based on the user's role
     $nonLabRoles = array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector");
     if( count(array_intersect($_SESSION['ROLE']['userRoles'], $nonLabRoles)) != 0 ){
         $destination = 'views/RSMSCenter.php';
     }else{
         if(in_array("Emergency Account", $_SESSION['ROLE']['userRoles'])){
            $destination = "rsms/views/hubs/emergencyInformationHub.php";
         }else{
			 $destination = 'rsms/views/lab/MyLab.php';
         }
      }
      $LOG->fatal('getting destination');
      if($_SESSION["REDIRECT"] != null){
      	$destination = str_replace("%23", "#", $_SESSION["REDIRECT"]);
      	$destination = str_replace(LOGIN_PAGE, "", $destination);
      }

      return $destination;
    }

    public function prepareRedirect(){
    	$LOG = Logger::getLogger("redirect");
    	$redirect = $this->getValueFromRequest('redirect', $redirect);

    	$_SESSION["REDIRECT"] = $redirect;
    	$LOG->fatal($_SESSION["REDIRECT"]);
       	return true;

    }

    public function logoutAction(){
        //session_destroy();
        $_SESSION['USER'] = null;
        $_SESSION['ROLE'] = null;
        return true;
    }

    public function getCurrentUser(){
        //todo:  when a user is logged in and in session, return the currently logged in user.
        $LOG = Logger::getLogger("action: " . $_SESSION['USER']);
        $entityMaps = array();
        $entityMaps[] = new EntityMap("eager","getPrincipalInvestigator");
        $entityMaps[] = new EntityMap("eager","getInspector");
        $entityMaps[] = new EntityMap("eager","getSupervisor");
        $entityMaps[] = new EntityMap("lazy","getRoles");
        $_SESSION['USER']->setEntityMaps($entityMaps);
        return $_SESSION['USER'];
    }

	public function getCurrentRoles(){
		if($_SESSION && $_SESSION['USER']){
            $user = $_SESSION['USER'];
            $currentRoles = array();
            foreach($user->getRoles() as $role){
                $currentRoles[] = $role->getName();
            }
			return $currentRoles;
		}
		return [];
	}

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
            return new ActionError("No request parameter 'id' was provided");
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
            $entityMaps[] = new EntityMap("lazy","getRooms");
            $entityMaps[] = new EntityMap("lazy","getDepartments");
            $entityMaps[] = new EntityMap("eager","getUser");
            $entityMaps[] = new EntityMap("lazy","getInspections");
            $entityMaps[] = new EntityMap("lazy","getPi_authorization");
            $entityMaps[] = new EntityMap("lazy", "getActiveParcels");
            $entityMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
            $entityMaps[] = new EntityMap("lazy", "getPurchaseOrders");
            $entityMaps[] = new EntityMap("lazy", "getSolidsContainers");
            $entityMaps[] = new EntityMap("lazy", "getPickups");
            $entityMaps[] = new EntityMap("lazy", "getScintVialCollections");
            $entityMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
            $entityMaps[] = new EntityMap("lazy","getOpenInspections");
            $entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
            $entityMaps[] = new EntityMap("lazy","getVerifications");
            $entityMaps[] = new EntityMap("lazy","getBuidling");
            $entityMaps[] = new EntityMap("lazy","getCurrentVerifications");
            $entityMaps[] = new EntityMap("lazy","getWipeTests");

            $supervisor = $user->getSupervisor();
            if($supervisor != null){
                $supervisor->setEntityMaps($entityMaps);
                return $supervisor;
            }

            return null;

        }
        else{
            //error
            return new ActionError("No request parameter 'id' was provided");
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
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    public function saveUser(){
        $LOG = Logger::getLogger('Action:' . __function__);
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to User');
        }
        else if( $decodedObject instanceof ActionError){
            return $decodedObject;
        }
        else{
            $dao = $this->getDao( new User() );
            //if this user is new, make sure it's active
            if($decodedObject->getKey_id() == NULL){
                $decodedObject->setIs_active(true);
            }
            $user = $dao->save( $decodedObject );

            //see if we need to save a PI or Inspector object
            if($decodedObject->getRoles() != NULL){
                foreach($decodedObject->getRoles() as $role){
                    $role = $this->getRoleById($role['Key_id']);
                    if($role->getName() == "Principal Investigator")$savePI 	   = true;
                    if($role->getName() == "Safety Inspector")      $saveInspector = true;
                }
            }

            //user was sent from client with Principal Investigator in roles array
            if(isset($savePI)){
                 //we have a PI for this User.  We should set it's Is_active state equal to the user's is_active state, so that when a user with a PI is activated or deactivated, the PI record also is.
                if($decodedObject->getPrincipalInvestigator() != null){
                    $pi = $decodedObject->getPrincipalInvestigator();
                }else{
                    $pi = new PrincipalInvestigator();
                    $pi->setUser_id($user->getKey_id());
                }

                $pi->setUser_id($user->getKey_id());
                $pi->setIs_active($user->getIs_active());
                $piDao  = $this->getDao(new PrincipalInvestigator());
                $depts = $pi->getDepartments();

                $newPi = $this->savePI($pi);

                foreach($depts as $department){
                	$dto = new RelationshipDto();
                	$dto->setAdd(true);
                	$dto->setMaster_id($newPi->getKey_id());
                	$dto->setRelation_id($department["Key_id"]);
                	$this->savePIDepartmentRelation($dto);
                }

                $newPi->setDepartments($pi->getDepartments());
                $user->setPrincipalInvestigator($newPi);
            }

            //user was sent from client with Saftey Inspector in roles array
            if(isset($saveInspector)){

                //we have an inspector for this User.  We should set it's Is_active state equal to the user's is_active state, so that when a user with a PI is activated or deactivated, the PI record also is.
                if($user->getInspector() != null){
                    $inspector = $user->getInspector();
                }else{
                    $inspector = new Inspector;
                    $inspector->setUser_id($user->getKey_id());
                }
                $inspector->setIs_active($user->getIs_active());
                $inspectorDao  = $this->getDao(new Inspector());
                $user->setInspector($this->saveInspector($inspector));
            }

            if($user->getKey_id()>0){
                $entityMaps = array();
                $entityMaps[] = new EntityMap("eager","getPrincipalInvestigator");
                $entityMaps[] = new EntityMap("eager","getInspector");
                $entityMaps[] = new EntityMap("lazy","getSupervisor");
                $entityMaps[] = new EntityMap("eager","getRoles");

                $user->setEntityMaps($entityMaps);
                return $user;
            }
        }
        return new ActionError('Could not save');
    }

    private function recurseHazardTree( $hazard = null, $weight = null){
    	$LOG = Logger::getLogger(__FUNCTION__);
    	if($hazard == null){
    		$hazard = $this->getHazardById(10000);
    	}

    	foreach($hazard->getActiveSubHazards() as $child){
    		$this->recurseHazardTree($child);
    	}

    	return $hazard;
    }

    public function getAllRoles() {
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
            return new ActionError("No request parameter 'id' was provided");
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
            	$checklist = new Checklist();
            	$checklist->setHazard_id($hazard->getKey_id());
            	$checklist->setName($hazard->getName());
            	$checklist->setIs_active(true);
            	return $checklist;
            }
        }
        else{
            //error
            return new ActionError("No request parameter 'id' was provided");
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
            return new ActionError('Error converting input stream to Checklist');
        }
        else{
            $dao = $this->getDao(new Checklist());

            // Find the name of the master hazard
            if ($decodedObject->getHazard_id() != null) {
                // Get the hazard for this checklist
                $hazard = $decodedObject->getHazard();
                if($decodedObject->getIs_active()==null){
                    $decodedObject->setIs_active(true);
                }
                // Get the array of parent hazards
                $hazard->setParentIds(null);
                $parentIds = $hazard->getParentIds();

                $master_hazard = null;
				$master_id = null;
                // If there are at least 2 hazards, get the second to last one (this is the master category)
                if (!empty($parentIds)){
                    $count = count($parentIds);
                    if ($count >= 2){
                        $masterHazardId = $parentIds[$count - 2];
                        $hazardDao = $this->getDao ($hazard);
                        $masterHazard = $hazardDao->getById($masterHazardId);
                        $master_hazard = $masterHazard->getName();
                        $LOG->fatal($master_hazard . ' | ' . $masterHazardId);
                    }else{
                        //if we don't have a parent hazard, other than Root, we set the master hazard to be the hazard
                        //i.e. Biological Hazards' checklist should have Biological Hazards as its master hazard
                        $master_hazard = $hazard->getName();
                        $masterHazardId = $hazard->getKey_id();

                    }
                }
				$decodedObject->setMaster_id($masterHazardId);
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
        }
        return $checklists;
    }

    public function saveQuestion($question = NULL){
        $LOG = Logger::getLogger('Action:' . __function__);
        if($question !== NULL) {
            $decodedObject = $question;
        }
        else {
            $decodedObject = $this->convertInputJson();
        }
        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to Question');
        }
        else if( $decodedObject instanceof ActionError){
            return $decodedObject;
        }
        else{
            $dao = $this->getDao(new Question());
            if($decodedObject->getOrder_index() == null){
                $LOG->debug($decodedObject);
                $checklistDao = $this->getDao(new Checklist());
                $checklist = $checklistDao->getById($decodedObject->getChecklist_id());
                $qCount    = count($checklist->getQuestions())-1;
                $questions = $checklist->getQuestions();
                if($questions != null){
                	$index     = $questions[$qCount]->getOrder_index();
                }else{
                	$index = -1;
                }
                $decodedObject->setOrder_index($index + 1);
            }
            $dao->save($decodedObject);
            return $decodedObject;
        }
    }

    public function saveDeficiency(){
        $LOG = Logger::getLogger('Action:' . __function__);
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to Deficiency');
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
            return new ActionError('Error converting input stream to Observation');
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
            return new ActionError('Error converting input stream to Recommendation');
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
            return new ActionError('Error converting input stream to SupplementalObservation');
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
            return new ActionError('Error converting input stream to SupplementalRecommendation');
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

    public function saveSupplementalDeficiency(){
        $LOG = Logger::getLogger('Action:' . __function__);
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to SupplementalRecommendation');
        }
        else if( $decodedObject instanceof ActionError){
            return $decodedObject;
        }
        else{
            $dao = $this->getDao(new SupplementalDeficiency());
            $sd = $dao->save($decodedObject);
            // check to see if the roomIds array is populated
            $roomIds = $decodedObject->getRoomIds();

            // remove the old rooms. if any
            foreach ($sd->getRooms() as $room){
                $dao->removeRelatedItems($room->getKey_id(),$sd->getKey_id(),DataRelationship::fromArray(SupplementalDeficiency::$ROOMS_RELATIONSHIP));
            }

            // if roomIds were provided then save them
            if (!empty($roomIds)){
                foreach ($roomIds as $id){
                    $LOG->fatal($id);
                    $dao->addRelatedItems($id,$sd->getKey_id(),DataRelationship::fromArray(SupplementalDeficiency::$ROOMS_RELATIONSHIP));
                }

                // else if no roomIds were provided, then just deactivate this DeficiencySelection
            } else {
                $sd->setIs_active(false);
                $sd = $dao->save($sd);
            }

            $sd = $dao->getById($sd->getKey_id());
            $LOG->debug($sd);

            return $sd;
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
        $entityMaps[] = new EntityMap("eager","getHasChildren");
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
        $entityMaps[] = new EntityMap("eager","getHasChildren");
        $entityMaps[] = new EntityMap("lazy","getParentIds");
        $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");

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
            return new ActionError("No request parameter 'id' was provided");
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
    public function saveHazard($decodedObject = NULL){

        $LOG = Logger::getLogger('Action:' . __function__);

        if( $decodedObject === NULL) {
            $decodedObject = $this->convertInputJson();
        }

        if( $decodedObject === NULL ){
            // that is, still null after checking input parameters *and* stream.
            return new ActionError('Error converting input stream to Hazard');
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
            if($hazard->getChecklist() != null){
                $chDao = $this->getDao(new Checklist());
                $checklist = $hazard->getChecklist();
                $checklist->setIs_active($decodedObject->getIs_active());
                $chDao->save($checklist);
            }

            $dao->save($decodedObject);

            return $decodedObject;
        }
    }

    /**
     * Just like SaveHazard, but it only returns the single parent hazard,
     * without subhazards. THIS STILL SAVES SUBHAZARDS because there's an
     * annoying amount of complexity when saving subhazards. Thus, it's easier
     * to just reuse saveHazard and strip out unneeded data before it hits
     * JSONManager, which is where the real bottleneck occurs.
     */
    public function saveHazardWithoutReturningSubHazards($decodedObject = NULL) {
        $LOG = Logger::getLogger('Action:' . __function__);

        if( $decodedObject === NULL) {
            $decodedObject = $this->convertInputJson();
        }

        if( $decodedObject === NULL ){
            // that is, still null after checking input parameters *and* stream.
            return new ActionError('Error converting input stream to Hazard');
        }
        else if( $decodedObject instanceof ActionError ){
            return $decodedObject;
        }
        else{
            $savedHazard = $this->saveHazard($decodedObject);
            $savedHazard->setSubHazards(null);

            // loading checklist and getHasChildren are required in HazardHub,
            // where this method will primarily be used.
            $newEntityMaps = array();
            $newEntityMaps[] = new EntityMap("lazy","getSubHazards");
            $newEntityMaps[] = new EntityMap("lazy","getActiveSubHazards");
            $newEntityMaps[] = new EntityMap("eager","getChecklist");
            $newEntityMaps[] = new EntityMap("lazy","getRooms");
            $newEntityMaps[] = new EntityMap("lazy","getInspectionRooms");
            $newEntityMaps[] = new EntityMap("eager","getHasChildren");
            $newEntityMaps[] = new EntityMap("lazy","getParentIds");
            $newEntityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");

            $savedHazard->setEntityMaps($newEntityMaps);

            $checklist = $savedHazard->getChecklist();

            if($checklist != NULL){
                $chklstMaps = array();
                $chklstMaps[] = new EntityMap("lazy","getHazard");
                $chklstMaps[] = new EntityMap("lazy","getQuestions");
                $checklist->setEntityMaps($chklstMaps);
                $savedHazard->setChecklist($checklist);
            }

            return $savedHazard;

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
            return new ActionError('Error converting input stream to Hazard');
        }
        else if( $decodedObject instanceof ActionError ){
            return $decodedObject;
        }
        else{
            $dao = $this->getDao(new Room());
            $room = $dao->save($decodedObject);
            if($decodedObject->getPrincipalInvestigators() != NULL){
                foreach($decodedObject->getPrincipalInvestigators() as $pi){
                    //$LOG->fatal($pi["Key_id"] . ' | room: ' . $room->getKey_id());
                    $this->savePIRoomRelation($pi["Key_id"],$room->getKey_id(),true);
                }
            }
            $entityMaps = array();
            $entityMaps[] = new EntityMap("eager","getPrincipalInvestigators");
            $entityMaps[] = new EntityMap("lazy","getHazards");
            $entityMaps[] = new EntityMap("lazy","getHazard_room_relations");
            $entityMaps[] = new EntityMap("lazy","getHas_hazards");
            $entityMaps[] = new EntityMap("eager","getBuilding");
            $entityMaps[] = new EntityMap("lazy","getSolidsContainers");
            $room->setEntityMaps($entityMaps);

            return $room;
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

            foreach ($response->getSupplementalDeficiencies() as $child){
                $dao->removeRelatedItems($child->getKey_id(),$response->getKey_id(),DataRelationship::fromArray(Response::$SUPPLEMENTAL_DEFICIENCIES_RELATIONSHIP));
            }

            $dao->deleteById($id);

            return true;
        }
        else{
            //error
            return new ActionError("No request parameter 'id' was provided");
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
                    $LOG->debug('about to delete def select');
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
            return new ActionError('Error converting input stream to Building');
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
    public function saveCampus(){
        $LOG = Logger::getLogger('Action:' . __function__);
        $decodedObject = $this->convertInputJson();

        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to Building');
        }
        else if( $decodedObject instanceof ActionError ){
            return $decodedObject;
        }
        else{
            $dao = $this->getDao(new Campus());
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
            return new ActionError("No request parameter 'id' was provided");
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
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    public function getAllInspectors(){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $dao = $this->getDao(new Inspector());

        return $dao->getAll(NULL, NULL, TRUE);
    }

    // Inspection, step 1 (PI / Room assessment)
    public function getPIById( $id = NULL, $getRooms = null ){

        $id = $this->getValueFromRequest('id', $id);
        $getRooms = $this->getValueFromRequest('getRooms', $getRooms);

        if( $id !== NULL ){
            $dao = $this->getDao(new PrincipalInvestigator());
            $pi = $dao->getById($id);
            if($getRooms != null && $getRooms == true){
                $entityMaps[] = new EntityMap("eager","getLabPersonnel");
                $entityMaps[] = new EntityMap("eager","getRooms");
                $entityMaps[] = new EntityMap("eager","getDepartments");
                $entityMaps[] = new EntityMap("eager","getUser");
                $entityMaps[] = new EntityMap("lazy","getInspections");
                $entityMaps[] = new EntityMap("lazy","getPi_authorization");
                $entityMaps[] = new EntityMap("lazy", "getActiveParcels");
                $entityMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
                $entityMaps[] = new EntityMap("lazy", "getPurchaseOrders");
                $entityMaps[] = new EntityMap("lazy", "getSolidsContainers");
                $entityMaps[] = new EntityMap("lazy", "getPickups");
                $entityMaps[] = new EntityMap("lazy", "getScintVialCollections");
                $entityMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
                $entityMaps[] = new EntityMap("lazy","getOpenInspections");
                $entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
                $entityMaps[] = new EntityMap("lazy","getVerifications");
                $entityMaps[] = new EntityMap("lazy","getBuidling");
                $entityMaps[] = new EntityMap("lazy","getCurrentVerifications");
		        $entityMaps[] = new EntityMap("lazy","getWipeTests");

                $pi->setEntityMaps($entityMaps);
            }
            return $pi;
        }
        else{
            //error
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    // Inspection, step 1 (PI / Room assessment)
    public function getPiForHazardInventory( $id = NULL ){
        $LOG = Logger::getLogger( 'Action:' . __function__ );
        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ){
            $dao = $this->getDao(new PrincipalInvestigator());
            $pi = $dao->getById($id);

            $buildings = array();

            $roomMaps = array();
            $roomMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
            $roomMaps[] = new EntityMap("lazy","getHazards");
            $roomMaps[] = new EntityMap("lazy","getHazard_room_relations");
            $roomMaps[] = new EntityMap("lazy","getHas_hazards");
            $roomMaps[] = new EntityMap("lazy","getBuilding");
            $roomMaps[] = new EntityMap("lazy","getSolidsContainers");

            $buildingMaps = array();
            $buildingMaps[] = new EntityMap("eager","getRooms");
            $buildingMaps[] = new EntityMap("lazy","getCampus");
            $buildingMaps[] = new EntityMap("lazy","getCampus_id");
            $buildingMaps[] = new EntityMap("lazy","getPhysical_address");

            $rooms = $pi->getRooms();
            foreach($rooms as $room){
                if(!in_array($room->getBuilding(), $buildings)){
                    $buildings[] = $room->getBuilding();
                }
            }

            foreach($buildings as $building){
                $rooms = array();
                foreach($pi->getRooms() as $room){
                    if($room->getBuilding_id() == $building->getKey_id()){
                        $room->setEntityMaps($roomMaps);
                        $rooms[] = $room;
                    }
                }

                $building->setEntityMaps($buildingMaps);
                $building->setRooms($rooms);
            }

            $pi->setBuildings($buildings);

            $entityMaps[] = new EntityMap("lazy","getLabPersonnel");
            $entityMaps[] = new EntityMap("eager","getRooms");
            $entityMaps[] = new EntityMap("eager","getDepartments");
            $entityMaps[] = new EntityMap("eager","getUser");
            $entityMaps[] = new EntityMap("eager","getBuidling");
            $entityMaps[] = new EntityMap("lazy","getInspections");
            $entityMaps[] = new EntityMap("lazy","getPi_authorization");
            $entityMaps[] = new EntityMap("lazy", "getActiveParcels");
            $entityMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
            $entityMaps[] = new EntityMap("lazy", "getPurchaseOrders");
            $entityMaps[] = new EntityMap("lazy", "getSolidsContainers");
            $entityMaps[] = new EntityMap("lazy", "getPickups");
            $entityMaps[] = new EntityMap("lazy", "getScintVialCollections");
            $entityMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
            $entityMaps[] = new EntityMap("lazy","getOpenInspections");
            $entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
            $entityMaps[] = new EntityMap("lazy","getVerifications");
            $entityMaps[] = new EntityMap("lazy","getCurrentVerifications");
            $entityMaps[] = new EntityMap("lazy","getWipeTests");



            $pi->setEntityMaps($entityMaps);
            return $pi;
        }
        else{
            //error
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    public function getAllPIs($rooms = null){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $rooms = $this->getValueFromRequest("rooms", $rooms);

        $dao = $this->getDao(new PrincipalInvestigator());
        $pis = $dao->getAllWith(DataRelationship::fromArray(PrincipalInvestigator::$ROOMS_RELATIONSHIP));
        /** TODO: Instead of $dao->getAll, we gather PIs which are either active or have rooms associated with them. **/
       /* $whereClauseGroup = new WhereClauseGroup( array( new WhereClause("is_active","=","1"), new WhereClause("key_id","IN","(SELECT principal_investigator_id FROM principal_investigator_room)") ) );
        $pis = $dao->getAllWhere($whereClauseGroup, "OR");*/
		$entityMaps = array();

        if($rooms != null){
            $entityMaps[] = new EntityMap("lazy","getLabPersonnel");
            $entityMaps[] = new EntityMap("eager","getRooms");
            $entityMaps[] = new EntityMap("eager","getDepartments");
            $entityMaps[] = new EntityMap("eager","getUser");
            $entityMaps[] = new EntityMap("lazy","getInspections");
            $entityMaps[] = new EntityMap("lazy","getPi_authorization");
            $entityMaps[] = new EntityMap("lazy", "getActiveParcels");
            $entityMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
            $entityMaps[] = new EntityMap("lazy", "getPurchaseOrders");
            $entityMaps[] = new EntityMap("lazy", "getSolidsContainers");
            $entityMaps[] = new EntityMap("lazy", "getPickups");
            $entityMaps[] = new EntityMap("lazy", "getScintVialCollections");
            $entityMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
            $entityMaps[] = new EntityMap("lazy","getOpenInspections");
            $entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
            $entityMaps[] = new EntityMap("lazy","getVerifications");
            $entityMaps[] = new EntityMap("lazy","getBuidling");
            $entityMaps[] = new EntityMap("lazy","getWipeTests");
            $entityMaps[] = new EntityMap("lazy","getCurrentVerifications");

        }else{
			$entityMaps[] = new EntityMap("lazy","getLabPersonnel");
            $entityMaps[] = new EntityMap("lazy","getRooms");
            $entityMaps[] = new EntityMap("eager","getDepartments");
            $entityMaps[] = new EntityMap("eager","getUser");
            $entityMaps[] = new EntityMap("lazy","getInspections");
            $entityMaps[] = new EntityMap("lazy","getPi_authorization");
            $entityMaps[] = new EntityMap("lazy", "getActiveParcels");
            $entityMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
            $entityMaps[] = new EntityMap("lazy", "getPurchaseOrders");
            $entityMaps[] = new EntityMap("lazy", "getSolidsContainers");
            $entityMaps[] = new EntityMap("lazy", "getPickups");
            $entityMaps[] = new EntityMap("lazy", "getScintVialCollections");
            $entityMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
            $entityMaps[] = new EntityMap("lazy","getOpenInspections");
            $entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
            $entityMaps[] = new EntityMap("lazy","getVerifications");
            $entityMaps[] = new EntityMap("lazy","getBuidling");
            $entityMaps[] = new EntityMap("lazy","getWipeTests");
            $entityMaps[] = new EntityMap("lazy","getCurrentVerifications");

		}

		foreach($pis as $pi){
			$pi->setEntityMaps($entityMaps);
		}

        return $pis;
    }

    public function getUsersForUserHub(){
        $userDao = $this->getDao( new User() );
        $users = $userDao->getAll('last_name');

        $entityMaps = array();
        $entityMaps[] = new EntityMap("eager","getPrincipalInvestigator");
        $entityMaps[] = new EntityMap("eager","getInspector");
        $entityMaps[] = new EntityMap("lazy","getSupervisor");
        $entityMaps[] = new EntityMap("eager","getRoles");

        foreach($users as $user){

            if($user->getPrincipalInvestigator() != null){
                $pi = $user->getPrincipalInvestigator();
                $piMaps = array();
                $piMaps[] = new EntityMap("eager","getLabPersonnel");
                $piMaps[] = new EntityMap("eager","getRooms");
                $piMaps[] = new EntityMap("eager","getDepartments");
                $piMaps[] = new EntityMap("eager","getUser");
                $piMaps[] = new EntityMap("lazy","getInspections");
                $piMaps[] = new EntityMap("lazy","getPi_authorization");
                $piMaps[] = new EntityMap("lazy", "getActiveParcels");
                $piMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
                $piMaps[] = new EntityMap("lazy", "getPurchaseOrders");
                $piMaps[] = new EntityMap("lazy", "getSolidsContainers");
                $piMaps[] = new EntityMap("lazy", "getPickups");
                $piMaps[] = new EntityMap("lazy", "getScintVialCollections");
                $piMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
                $piMaps[] = new EntityMap("lazy","getOpenInspections");
                $piMaps[] = new EntityMap("lazy","getQuarterly_inventories");
                $piMaps[] = new EntityMap("lazy","getVerifications");
                $piMaps[] = new EntityMap("lazy","getBuidling");
                $piMaps[] = new EntityMap("lazy","getCurrentVerifications");
                $entityMaps[] = new EntityMap("lazy","getWipeTests");

                $pi->setEntityMaps($piMaps);
            }

            $user->setEntityMaps($entityMaps);
        }

        return $users;
    }

    public function getOpenInspectionsByPIId( $id = null){
        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ){
            $dao = $this->getDao(new PrincipalInvestigator());
            $pi =  $dao->getById($id);
            return $pi->getOpenInspections();
        }
        else{
            //error
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    public function getPisForUserHub(){
        $dao = $this->getDao(new PrincipalInvestigator());
        $pis = $dao->getAll();

        $entityMaps = array();
        $entityMaps[] = new EntityMap("eager","getLabPersonnel");
        $entityMaps[] = new EntityMap("eager","getRooms");
        $entityMaps[] = new EntityMap("eager","getDepartments");
        $entityMaps[] = new EntityMap("eager","getUser");
        $entityMaps[] = new EntityMap("lazy","getInspections");
        $entityMaps[] = new EntityMap("lazy","getPi_authorization");
        $entityMaps[] = new EntityMap("lazy", "getActiveParcels");
        $entityMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
        $entityMaps[] = new EntityMap("lazy", "getPurchaseOrders");
        $entityMaps[] = new EntityMap("lazy", "getSolidsContainers");
        $entityMaps[] = new EntityMap("lazy", "getPickups");
        $entityMaps[] = new EntityMap("lazy", "getScintVialCollections");
        $entityMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
        $entityMaps[] = new EntityMap("lazy","getOpenInspections");
        $entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
        $entityMaps[] = new EntityMap("lazy","getVerifications");
        $entityMaps[] = new EntityMap("lazy","getBuidling");
        $entityMaps[] = new EntityMap("lazy","getCurrentVerifications");
        $entityMaps[] = new EntityMap("lazy","getWipeTests");

        foreach($pis as $pi){
            $pi->setEntityMaps($entityMaps);
        }
        return $pis;
    }

    public function getUserByPiUserId( $id = NULL ){

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ){
            $user = $this->getUserById($id);
            return $user->getPrincipalInvestigator();
        }
        else{
            //error
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    public function getAllRooms($allLazy = true){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $dao = $this->getDao(new Room());

        $rooms = $dao->getAll();

        // initialize an array of entityMap settings to assign to rooms, instructing them to lazy-load children
        // necessary because rooms by default eager-load buildings, and this would set up an infinite load loop between building->room->building->room...
        $roomMaps = array();
        if($allLazy == NULL){
	        $roomMaps[] = new EntityMap("eager","getPrincipalInvestigators");
	        $roomMaps[] = new EntityMap("lazy","getHazards");
	        $roomMaps[] = new EntityMap("lazy","getBuilding");
	        $roomMaps[] = new EntityMap('eager', 'getBuilding_id');
	        $roomMaps[] = new EntityMap("lazy","getHazard_room_relations");
	        $roomMaps[] = new EntityMap("lazy","getHas_hazards");
	        $roomMaps[] = new EntityMap("lazy","getSolidsContainers");

        }else{
        	$roomMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
        	$roomMaps[] = new EntityMap("lazy","getHazards");
        	$roomMaps[] = new EntityMap("lazy","getBuilding");
        	$roomMaps[] = new EntityMap('eager', 'getBuilding_id');
        	$roomMaps[] = new EntityMap("lazy","getHazard_room_relations");
        	$roomMaps[] = new EntityMap("lazy","getHas_hazards");
        	$roomMaps[] = new EntityMap("lazy","getSolidsContainers");

        }

        $piMaps = array();
        $piMaps[] = new EntityMap("lazy","getLabPersonnel");
        $piMaps[] = new EntityMap("lazy","getRooms");
        $piMaps[] = new EntityMap("eager","getDepartments");
        $piMaps[] = new EntityMap("eager","getUser");
        $piMaps[] = new EntityMap("lazy","getInspections");
        $piMaps[] = new EntityMap("lazy","getPi_authorization");
        $piMaps[] = new EntityMap("lazy", "getActiveParcels");
        $piMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
        $piMaps[] = new EntityMap("lazy", "getPurchaseOrders");
        $piMaps[] = new EntityMap("lazy", "getSolidsContainers");
        $piMaps[] = new EntityMap("lazy", "getPickups");
        $piMaps[] = new EntityMap("lazy", "getScintVialCollections");
        $piMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
        $piMaps[] = new EntityMap("lazy","getOpenInspections");
        $piMaps[] = new EntityMap("lazy","getQuarterly_inventories");
        $piMaps[] = new EntityMap("lazy","getVerifications");
        $piMaps[] = new EntityMap("lazy","getBuidling");
        $piMaps[] = new EntityMap("lazy","getCurrentVerifications");
        $piMaps[] = new EntityMap("lazy","getWipeTests");

        $userMaps = array();
        $userMaps[] = new EntityMap("lazy","getPrincipalInvestigator");
        $userMaps[] = new EntityMap("lazy","getInspector");
        $userMaps[] = new EntityMap("lazy","getSupervisor");
        $userMaps[] = new EntityMap("lazy","getRoles");
        $userMaps[] = new EntityMap("lazy","getPrimary_department");

        foreach($rooms as $room){
			if($allLazy == NULL){
	            foreach($room->getPrincipalInvestigators() as $pi){
	                $pi->setEntityMaps($piMaps);

	                $user = $pi->getUser();


	                $user->setEntityMaps($userMaps);
	            }
			}
            $room->setEntityMaps($roomMaps);

        }

        return $rooms;
    }

    public function getAllPrincipalInvestigatorRoomRelations(){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $dao = $this->getDao(new PrincipalInvestigatorRoomRelation());

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
            return new ActionError("No request parameter 'id' was provided");
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

    public function getPIsByClassInstance( $decodedObject = NULL ){
     $LOG = Logger::getLogger( 'Action:' . __function__ );

        $decodedObject = $this->convertInputJson();

        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to RelationshipDto');
        }
        else if( $decodedObject instanceof ActionError ){
            return $decodedObject;
        }


        $pis = $decodedObject->getPrincipalInvestigators();

        $entityMaps = array();
        $entityMaps[] = new EntityMap("lazy","getLabPersonnel");
        $entityMaps[] = new EntityMap("lazy","getRooms");
        $entityMaps[] = new EntityMap("lazy","getDepartments");
        $entityMaps[] = new EntityMap("eager","getUser");
        $entityMaps[] = new EntityMap("lazy","getInspections");
        $entityMaps[] = new EntityMap("lazy","getPi_authorization");
        $entityMaps[] = new EntityMap("lazy", "getActiveParcels");
        $entityMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
        $entityMaps[] = new EntityMap("lazy", "getPurchaseOrders");
        $entityMaps[] = new EntityMap("lazy", "getSolidsContainers");
        $entityMaps[] = new EntityMap("lazy", "getPickups");
        $entityMaps[] = new EntityMap("lazy", "getScintVialCollections");
        $entityMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
        $entityMaps[] = new EntityMap("lazy","getOpenInspections");
        $entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
        $entityMaps[] = new EntityMap("lazy","getVerifications");
        $entityMaps[] = new EntityMap("lazy","getBuidling");
        $entityMaps[] = new EntityMap("lazy","getCurrentVerifications");
        $entityMaps[] = new EntityMap("lazy","getWipeTests");

        foreach($pis as $pi){
            $pi->setEntityMaps($entityMaps);
        }
        return $pis;
    }

    public function savePI( $pi = NULL){
        $LOG = Logger::getLogger('Action:' . __function__);
        if($pi == null){
            $decodedObject = $this->convertInputJson();
        }else{
            $decodedObject = $pi;
        }
        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to Observation');
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

    public function saveInspector($inspector = null){
        $LOG = Logger::getLogger('Action:' . __function__);
        if($inspector == null){
            $decodedObject = $this->convertInputJson();
        }else{
            $decodedObject = $inspector;
        }
        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to Observation');
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
        $LOG->fatal($decodedObject);

        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to RelationshipDto');
        }
        else if( $decodedObject instanceof ActionError ){
            return $decodedObject;
        }
        else{

            if($PIId == NULL && $roomId == NULL && $add == NULL){
                $PIId = $decodedObject->getMaster_id();
                $roomId = $decodedObject->getRelation_id();
                $add = $decodedObject->getAdd();
            }

            //$LOG->fatal('pi_id: ' . $PIId . "room_id: " . $roomId . "add: " . $add);
            $room = $this->getRoomById($roomId);

            if( $PIId !== NULL && $roomId !== NULL && $add !== null ){

                // Get a DAO
                $dao = $this->getDao(new PrincipalInvestigator());
                // if add is true, add this room to this PI
                if ($add){
                    //$LOG->fatal('trying to add');
                    $dao->addRelatedItems($roomId,$PIId,DataRelationship::fromArray(PrincipalInvestigator::$ROOMS_RELATIONSHIP));
                // if add is false, remove this room from this PI
                } else {
                    $dao->removeRelatedItems($roomId,$PIId,DataRelationship::fromArray(PrincipalInvestigator::$ROOMS_RELATIONSHIP));
                    //set our hazard flags for the room.

                }

            } else {
                //error
                return new ActionError("Missing proper parameters (should be masterId int, relationId int, add boolean)");
            }

        }


        $entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getPrincipalInvestigators");
		$entityMaps[] = new EntityMap("lazy","getHazards");
		$entityMaps[] = new EntityMap("lazy","getHazard_room_relations");
		$entityMaps[] = new EntityMap("eager","getHas_hazards");
		$entityMaps[] = new EntityMap("eager","getBuilding");
		$entityMaps[] = new EntityMap("lazy","getSolidsContainers");
		$room->setEntityMaps($entityMaps);

        return $room;
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

    public function savePIDepartmentRelations($piId = NULL, $departmentIds = NULL){
        $piId = $this->getValueFromRequest('piId', $piId);
        $departmentIds = $this->getValueFromRequest('departmentIds', $departmentIds);
        $LOG = Logger::getLogger( 'Action:' . __function__ );
        $LOG->debug($this->getValueFromRequest('departmentIds', $departmentIds));
        foreach($departmentIds as $departmentId){
            $relation = new RelationshipDto();
            $relation->setMaster_id($piId);
            $relation->setRelation_id($departmentId);
            $relation->setAdd(true);
            $this->savePIDepartmentRelation($relation);
        }
        return true;
    }

    public function savePIDepartmentRelation($decodedObject){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        if($decodedObject == null)$decodedObject = $this->convertInputJson();

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

    public function saveUserRoleRelations($userId = null, $roleIds = null){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $userId = $this->getValueFromRequest('userId', $userId);
        $roleIds = $this->getValueFromRequest('roleIds', $roleIds);
        $LOG->debug($roleIds);
        foreach($roleIds as $roleId){
            $relation = new RelationshipDto();
            $relation->setMaster_id($userId);
            $relation->setRelation_id($roleId);
            $relation->setAdd(true);
            $this->saveUserRoleRelation($relation);
        }
        return true;
    }

    public function saveUserRoleRelation($relation = null){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        if($relation == null){
            $decodedObject = $this->convertInputJson();
        }else{
            $decodedObject = $relation;
        }

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
                // if add is true, add this role to this USER
                if ($add){
                    if(!in_array($roleToAdd, $roles)){

                        //add PI record if role is PI
                        if($roleToAdd->getName() == 'Principal Investigator'){
                            //if the user already has a PI, get that PI
                            if($user->getPrincipalInvestigator() != NULL){
                                $pi = $user->getPrincipalInvestigator();
                            }else{
                                $pi = new PrincipalInvestigator();
                                $pi->setUser_id($userID);
                            }

                            $pi->setIs_active(true);
                            if(!$this->savePI($pi))return new ActionError('The PI record was not saved');
                        }

                        //add Inspector record if role is inspector
                        if($roleToAdd->getName() == 'Safety Inspector'){
                            $LOG->debug('trying to save inspector');
                            //if the user already has an Inspector, get that Inspector
                            if($user->getInspector() != NULL){
                                $inspector = $user->getInspector();
                            }else{
                                $inspector = new Inspector();
                                $inspector->setUser_id($userID);
                            }
                            if(!$this->saveInspector($inspector))return new ActionError('The inspector record was not saved');
                        }

                        //All Lab Contacts are also Lab Personnel, so make sure Lab Contacts have that role as well
                        if($roleToAdd->getName() == 'Lab Contact'){
                            $addContact = true;
                            foreach($roles as $role){
                                if($role->getName() == 'Lab Personnel') $addContact = false;
                            }
                            if($addContact == true){
                                $allRoles = $this->getAllRoles();
                                foreach($allRoles as $role){
                                    if($role->getName() == "Lab Personnel"){
                                        $labPersonnelKeyid = $role->getKey_id();
                                        break;
                                    }
                                }
                                $personnelRelation = new RelationshipDto();
                                $personnelRelation->setAdd(true);
                                $personnelRelation->setMaster_id($userID);
                                $personnelRelation->setRelation_id($roleId);
                                //$LOG->fatal($personnelRelation);
                                $dao->addRelatedItems($labPersonnelKeyid,$userID,DataRelationship::fromArray(User::$ROLES_RELATIONSHIP));
                            }
                        }

                        // only add the role if the user doesn't already have it
                        $dao->addRelatedItems($roleId,$userID,DataRelationship::fromArray(User::$ROLES_RELATIONSHIP));
                    }
                // if add is false, remove this role from this PI
                } else {
                    $dao->removeRelatedItems($roleId,$userID,DataRelationship::fromArray(User::$ROLES_RELATIONSHIP));
                    if($roleToAdd->getName() == 'Principal Investigator'){
                        $LOG->debug('trying to deactivate pi');
                        $pi = $user->getPrincipalInvestigator();
                        $dao = $this->getDao(new PrincipalInvestigator());
                        $pi->setIs_active(false);
                        $dao->save($pi);
                    }
                    if($roleToAdd->getName() == 'Safety Inspector'){
                        $LOG->debug('trying to deactivate Inspector');
                        $inspector = $user->getInspector();
                        $dao = $this->getDao(new Inspector());

                        $inspector->setIs_active(false);
                        $dao->save($inspector);
                    }
                }

            } else {
                //error
                return new ActionError("Missing proper parameters (should be masterId int, relationId int, add boolean)");
            }

        }
        return true;
    }

    public function getPIByUserId($id = null){
        $id = $this->getValueFromRequest('id', $id);
        $userDao = $this->getDao(new User());
        $user = $userDao->getById($id);
        return $user->getPrincipalInvestigator();
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
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    public function getAllDepartments(){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $dao = $this->getDao(new Department());

        return $dao->getAll();
    }

    public function getAllDepartmentsWithCounts(){
        $dao = $this->getDao(new Department());
        return $dao->getAllDepartmentsAndCounts();
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
            return new ActionError("No request parameter 'id' was provided");
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
            return new ActionError('Error converting input stream to Hazard');
        }
        else if( $decodedObject instanceof ActionError ){
            return $decodedObject;
        }
        else{
            $dao = $this->getDao(new Department());
            $department = $dao->save($decodedObject);
            return $dao->getDepartmentDtoById($department->getKey_id());
        }
    }

    public function getAllBuildings( $id = NULL, $skipRooms = null, $skipPis = null ){
        $LOG = Logger::getLogger( 'Action:' . __function__ );
        $skipRooms = $this->getValueFromRequest('skipRooms', $skipRooms);
        $skipPis = $this->getValueFromRequest('skipPis', $skipPis);

        $dao = $this->getDao(new Building());

        // get all buildings
        $buildings = $dao->getAll();

        $bldgMaps = array();
        if($skipRooms == NULL){
            // initialize an array of entityMap settings to assign to rooms, instructing them to lazy-load children
            // necessary because rooms by default eager-load buildings, and this would set up an infinite load loop between building->room->building->room...
            $roomMaps = array();

            if($skipPis != null){
                $roomMaps[] = new EntityMap("eager","getPrincipalInvestigators");
            }else{
                $roomMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
            }

            $roomMaps[] = new EntityMap("lazy","getHazards");
            $roomMaps[] = new EntityMap("lazy","getHazard_room_relations");
            $roomMaps[] = new EntityMap("lazy","getHas_hazards");
            $roomMaps[] = new EntityMap("lazy","getBuilding");
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
        }
        return $buildings;

    }

    public function getAllCampuses(){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $dao = $this->getDao(new Campus());
        return $dao->getAll();
    }

    public function getBuildingById( $id = NULL ){
        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ){
            $dao = $this->getDao(new Building());
            return $dao->getById($id);
        }
        else{
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    public function getRoomsByBuildingId( $id=null ){
        if($id == null)$id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ){
            $dao = $this->getDao(new Building());
            $building = $dao->getById($id);

            $roomMaps[] = new EntityMap("lazy","getHazards");
            $roomMaps[] = new EntityMap("lazy","getHazard_room_relations");
            $roomMaps[] = new EntityMap("lazy","getHas_hazards");
            $roomMaps[] = new EntityMap("lazy","getBuilding");
            // get this building's rooms
            $rooms = $building->getRooms();
            // iterate this building's rooms and make then lazy loading
            foreach ($rooms as &$room){
                $room->setEntityMaps($roomMaps);
            }
            // make sure this building is loaded with the lazy loading rooms
            // ... and make sure that the rooms themselves are loaded eagerly
            $building->setEntityMaps($bldgMaps);

            return $rooms;
        }
        else{
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    public function initiateInspection($inspectionId = NULL,$piId = NULL,$inspectorIds= NULL,$rad = NULL){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $inspectionId = $this->getValueFromRequest('inspectionId', $inspectionId);
        $piId = $this->getValueFromRequest('piId', $piId);
        $inspectorIds = $this->getValueFromRequest('inspectorIds', $inspectorIds);
        $rad = $this->getValueFromRequest('rad', $rad);

        if( $piId !== NULL && $inspectorIds !== null ){

            $inspection = new Inspection();
            $dao = $this->getDao($inspection);

            // Set inspection's keyId and PI.
            if (!empty($inspectionId)){
                $inspection = $dao->getById($inspectionId);
            }

            if($rad != null){
                $inspection->setIs_rad(true);
            }

            if($inspection->getSchedule_year() == NULL){
                $year = $this->getCurrentYear();
                $inspection->setSchedule_year($year);
            }

            if($inspection->getSchedule_month() == null){
                $month = date('m');
                $inspection->setSchedule_month($month);
            }

            $inspection->setPrincipal_investigator_id($piId);

            //if($inspection->getDate_started() == null)$inspection->setDate_started(date("Y-m-d H:i:s"));
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
        $entityMaps[] = new EntityMap("eager","getStatus");

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
            return new ActionError('Error converting input stream to Inspection');
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

    public function scheduleInspection(){
        $LOG = Logger::getLogger('Action:' . __function__);
        $decodedObject = $this->convertInputJson();
        $inspectionDao = $this->getDao( new Inspection() );
        if( $decodedObject->getInspections()->getKey_id() != NULL ){
            $inspection = $inspectionDao->getById( $decodedObject->getInspections()->getKey_id() );
        }else{
            $inspection = new Inspection();
        }

        $inspection->setSchedule_month( $decodedObject->getInspections()->getSchedule_month() );
        if($decodedObject->getInspections()->getSchedule_year())$inspection->setSchedule_year( $decodedObject->getInspections()->getSchedule_year() );

        $inspection->setPrincipal_investigator_id($decodedObject->getPi_key_id());
        $inspection = $inspectionDao->save( $inspection );

        if($inspection->getRooms() != null){
            foreach($inspection->getRooms() as $room){
                //remove old room relationship
                $this->saveInspectionRoomRelation($room->getKey_id(),$inspection->getKey_id(),false);
            }
        }

        foreach($decodedObject->getBuilding_rooms() as $room){
            //save room relationships
            $this->saveInspectionRoomRelation($room["Key_id"],$inspection->getKey_id(),true);
        }

        if($inspection->getInspectors() != null){
            foreach($inspection->getInspectors() as $inspector){
                //remove old inspector relationships
                $LOG->debug($inspector);
                $inspectionDao->removeRelatedItems($inspector->getKey_id(),$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$INSPECTORS_RELATIONSHIP));
            }
            $inspection->setInspectors(null);
        }

        foreach($decodedObject->getInspections()->getInspectors() as $inspector){
            //save inspector relationships
            $inspectionDao->addRelatedItems($inspector["Key_id"],$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$INSPECTORS_RELATIONSHIP ));
        }

        //When an Inspection is scheduled, labs should complete a verification before the inspection takes place
        if($inspection->getSchedule_month() != null){
            //first, see if this verification has already been created
            $whereClauseGroup = new WhereClauseGroup(
                array(new WhereClause("inspection_id","=",$inspection->getKey_id()))
            );
            $verificationDao =  new GenericDAO(new Verification());

            $verifications = $verificationDao->getAllWhere($whereClauseGroup);
            if(!empty($verifications)){
                $verification = $verifications[0];
            }else{
                $verification = new Verification();
            }

            //the verification is due one month before the first day of the scheduled month of the inspection
            $inspectionDate = date_create($inspection->getSchedule_year() . "-" . $inspection->getSchedule_month() );
            $verification->setDue_date($inspectionDate->format("Y-m-d H:i:s"));
            $verification->setInspection_id($inspection->getKey_id());
            $verification->setPrincipal_investigator_id($inspection->getPrincipal_investigator_id());
            $verificationDao->save($verification);
        }

        $entityMaps = array();
        $entityMaps[] = new EntityMap("eager","getInspectors");
        $entityMaps[] = new EntityMap("eager","getRooms");
        $entityMaps[] = new EntityMap("eager","getResponses");
        $entityMaps[] = new EntityMap("eager","getDeficiency_selections");
        $entityMaps[] = new EntityMap("eager","getPrincipalInvestigator");
        $entityMaps[] = new EntityMap("eager","getStatus");
        $entityMaps[] = new EntityMap("lazy","getChecklists");

        $inspection->setEntityMaps($entityMaps);
        return $inspection;
    }

    public function saveNoteForInspection(){
        $LOG = Logger::getLogger('Action:' . __function__);
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to Inspection');
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

      public function submitCAP(){
        $LOG = Logger::getLogger('Action:' . __function__);
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to Inspection');
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
            $LOG->debug("Retrieving Hazard-Room mappings for Rooms: $roomIdsCsv");
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
            $entityMaps[] = new EntityMap("eager","getInspectionRooms");
            $entityMaps[] = new EntityMap("eager","getHasChildren");
            $entityMaps[] = new EntityMap("lazy","getParentIds");
            $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");

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
                    $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");

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

    public function filterHazards (&$hazard, $rooms, $generalHazard = null){
        $LOG = Logger::getLogger( 'Action:' . __function__ );
        $LOG->debug($hazard->getName());
        $entityMaps = array();
        $entityMaps[] = new EntityMap("lazy","getSubHazards");
        $entityMaps[] = new EntityMap("lazy","getChecklist");
        $entityMaps[] = new EntityMap("lazy","getRooms");
        $entityMaps[] = new EntityMap("eager","getInspectionRooms");
        $entityMaps[] = new EntityMap("eager","getHasChildren");
        $entityMaps[] = new EntityMap("lazy","getParentIds");
        $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");


        $hazard->setInspectionRooms($rooms);
        $hazard->filterRooms();

        if(stristr($hazard->getName, 'general hazard') || $generalHazard){
                $generalHazard = true;
                if($hazard->getIsPresent() != true){
                    $this->saveHazardRoomRelations( $hazard, $rooms );
                }
        }

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

            if($generalHazard)$subhazard->setIsPresent(true);

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
                $this->filterHazards($subhazard, $rooms, $generalHazard);
            }else{
                $entityMaps = array();
                $entityMaps[] = new EntityMap("lazy","getSubHazards");
                $entityMaps[] = new EntityMap("lazy","getActiveSubHazards");
                $entityMaps[] = new EntityMap("lazy","getChecklist");
                $entityMaps[] = new EntityMap("lazy","getRooms");
                $entityMaps[] = new EntityMap("eager","getInspectionRooms");
                $entityMaps[] = new EntityMap("eager","getHasChildren");
                $entityMaps[] = new EntityMap("lazy","getParentIds");
                $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");

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
                $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");

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
    }

    public function getHazardsInRoomByPi( $roomId = NULL, $piId ){
        $LOG = Logger::getLogger( 'Action:' . __function__ );
        $roomId = $this->getValueFromRequest('roomId', $roomId);
        $subHazards = $this->getValueFromRequest('subHazards', $subHazards);
        $LOG->debug("subHazards is $subHazards, roomId is $roomId");


        if( $roomId !== NULL ){

            $whereClauseGroup = new WhereClauseGroup(
                                    array(
                                        new WhereClause("room_id", "=", $roomId),
                                        new WhereClause("principal_investigator_id", "=", $piId),
                                    )
                                );

            $piHazRoomDao = $this->getDao(new PrincipalInvestigatorHazardRoomRelation());
            $piHazRooms = $piHazRoomDao->getAllWhere($whereClauseGroup);

            //key_ids of hazards which are at branch level.  we use these to make sure that branch hazards are not excluded when the PI has direct children of them
            $branchIds = array(1, 10009, 10010, 9999);
            $branchChildIds= array();
            foreach($branchIds as $id){
                $branchChildIds[$id]  = $this->getBranchChildIds($id);
            }

            // if subhazards is false, change all hazard subentities to lazy loading
            $entityMaps = array();
            $entityMaps[] = new EntityMap("lazy","getSubHazards");
            $entityMaps[] = new EntityMap("lazy","getActiveSubHazards");
            $entityMaps[] = new EntityMap("lazy","getChecklist");
            $entityMaps[] = new EntityMap("lazy","getRooms");
            $entityMaps[] = new EntityMap("lazy","getInspectionRooms");
            $entityMaps[] = new EntityMap("eager","getParentIds");
            $entityMaps[] = new EntityMap("lazy","getHasChildren");
            $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");

            $hazards = array();
            $hazardIds = array();

            foreach ($piHazRooms as $piHazardRoom){
                if(!in_array($piHazardRoom->getHazard_id(), $hazardIds)){
                    $hazard = $piHazardRoom->getHazard();
                    $hazard->setEntityMaps($entityMaps);
                    $hazardIds[] = $piHazardRoom->getHazard_id();
                    $hazards[] = $hazard;
                }
            }

            //make sure we get all the general hazards, too
            $generalHazard = $this->getHazardById(9999);
            $generalHazards = $this->getFlatHazardBranch($generalHazard);

            $hazards = array_merge($hazards, $generalHazards);


            foreach($branchIds as $id){
                if(!in_array($id, $hazardIds)){
                    //if the id isn't in the array, do we need this branch parent?
                    //check to see if our array of all relevant hazard key_ids has at least one of the key_ids of direct children of the branch level hazard
                    if( $branchChildIds[$id] != null && array_intersect($branchChildIds[$id], $hazardIds) != null ){
                        array_push($hazards, $this->getHazardById($id));
                    }
                }
            }
            array_push($hazards, $this->getHazardById(9999));

            return $hazards;
        }
        else{
            //error
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    /**
     * Returns an array of the ids of the children of a hazard
     *
     * @param int $branchHazardId
     * @return array $ids
     */
    private function getBranchChildIds($branchHazardId){
        $hazard = $this->getHazardById($branchHazardId);
        $ids = array();
        foreach($hazard->getSubHazards() as $hazard){
            $ids[] = $hazard->getKey_id();
        }
        return $ids;
    }

    /**
     * Returns a one-dimensional array of all hazards in a branch
     *
     * @param int $branchHazardId
     * @return array $hazards
     */
    private function getFlatHazardBranch($hazard, &$hazards = null){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        if($hazards == null){
            $hazards = array($hazard);
        }
        $hazards = array_merge($hazards, $hazard->getSubHazards());
        foreach($hazard->getSubHazards() as $child){
            $this->getFlatHazardBranch($child, $hazards);
        }


        return $hazards;
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



    public function saveHazardRoomRelations( $hazard = null, $rooms = null ){
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
            $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");

            $hazard->setEntityMaps($entityMaps);

            $LOG->debug($hazard);

            //if we are pulling general hazards for an inspection, we need to make sure that they are all in every room
            //we pass rooms to saveHazardRoomRelations so that we can set the relationships
            if($rooms){
                $hazard->setIsPresent(true);
                $hazard->setInspectionRooms($rooms);
            }

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
                        $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");

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

    public function getGrandma($hazard){
        $parentBranchIds = array(1,10009,10010);
        $LOG = Logger::getLogger( 'Action:' . __function__ );
        $LOG->debug('hazard id is '.$hazardId);
        $parentDao = $this->getDao(new Hazard());
        $granny    = $parentDao->getById($hazard->getParent_hazard_id());

        if(!in_array($granny->getKey_id(), $parentBranchIds)){
            $this->getGrandma($granny);
        }else{
            return $granny;
        }

    }

    public function saveHazardRelation($roomId = NULL,$hazardId = NULL,$add= NULL, $recurse = NULL){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        if($roomId == null)$roomId = $this->getValueFromRequest('roomId', $roomId);
        if($hazardId == null)$hazardId = $this->getValueFromRequest('hazardId', $hazardId);
        if($add == null)$add = $this->getValueFromRequest('add', $add);
        if($recurse == null)$recurse = $this->getValueFromRequest('recurse', $recurse);

        if( $roomId !== NULL && $hazardId !== NULL && $add !== null ){
            // Get this room
            $dao = $this->getDao(new Room());
            $room = $dao->getById($roomId);
            $hazDao = $this->getDao(new Hazard());
            $hazard = $hazDao->getById($hazardId);
            $granny = $this->getGrandma($hazard);
            if($granny != null)$grannysRooms = $granny->getRooms();

            // if add is true, add this hazard to this room
            if ($add != false){
                $dao->addRelatedItems($hazardId,$roomId,DataRelationship::fromArray(Room::$HAZARDS_RELATIONSHIP));
                if($granny != null && !in_array($room, $grannysRooms)){
                    $LOG->debug('about to add parent hazard');
                    $dao->addRelatedItems($granny->getKey_id(),$roomId,DataRelationship::fromArray(Room::$HAZARDS_RELATIONSHIP));
                }
            // if add is false, remove this hazard from this room
            } else {
                $dao->removeRelatedItems($hazardId,$roomId,DataRelationship::fromArray(Room::$HAZARDS_RELATIONSHIP));
                $parentBranchIds = array(1,10009,10010);

                if(in_array($hazard->getParent_hazard_id(), $parentBranchIds)){
                    //do any hazards in this room have a parent id in $parentBranchIds
                    $hazards = $room->getHazards();
                    $delete = true;
                    foreach ($hazards as $roomHazard){
                        if($hazard->getParent_hazard_id() == $roomHazard->getParent_hazard_id()){
                            $delete = false;
                        }
                    }

                    //remove relevant parent hazard
                    if($delete == true){
                        $dao->removeRelatedItems($hazard->getParent_hazard_id(),$roomId,DataRelationship::fromArray(Room::$HAZARDS_RELATIONSHIP));
                    }
                }

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
            $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");

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
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    public function saveResponse(){
        $LOG = Logger::getLogger('Action:' . __function__);
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to Response');
        }
        else if( $decodedObject instanceof ActionError){
            return $decodedObject;
        }
        else{
            $dao = $this->getDao(new Response());
            //If this question was previously answered no, and then the answer was changed, we need to break deficiency relationships
            if($decodedObject->getKey_id() != null){
                $oldResponse = $dao->getById( $decodedObject->getKey_id() );
                //if the response's answer is not no, we should break any deficiency relationships
                if( !stristr( $decodedObject->getAnswer,'no' ) ){
                    foreach( $oldResponse->getDeficiencySelections() as $selection ){
                        $LOG->debug($selection);
                        $dao->removeRelatedItems($selection->getKey_id(),$oldResponse->getKey_id(),DataRelationship::fromArray(Response::$DEFICIENCIES_RELATIONSHIP));
                    }
                }
            }

            $response = $dao->save($decodedObject);

            return $response;
        }
    }

    public function saveDeficiencySelection(){
        $LOG = Logger::getLogger('Action:' . __function__);
        $decodedObject = $this->convertInputJson();
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
            $dao = $this->getDao(new DeficiencySelection());
            $ds = $dao->save($decodedObject);
            $LOG->fatal($decodedObject);
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


    public function saveOtherDeficiencySelection(DeficiencySelection $deficiencySelection = NULL  ){
        $LOG = Logger::getLogger('Action:' . __function__);
        $decodedObject = $this->convertInputJson();
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
                // else if no roomIds were provided, then just deactivate this DeficiencySelection
            } else {
                $ds->setIs_active(false);
                $dao->save($ds);
            }
            $selection = $dao->getById($ds->getKey_id());

            return $selection;

        }
    }

    public function saveCorrectiveAction(){
        $LOG = Logger::getLogger('Action:' . __function__);
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to CorrectiveAction');
        }
        else if( $decodedObject instanceof ActionError){
            return $decodedObject;
        }
        else{

            $dao = $this->getDao(new CorrectiveAction());
            $dao->save($decodedObject);
            //$LOG->fatal($this->getPIIDFromObject($decodedObject));

            return $decodedObject;
        }
    }

    public function deleteCorrectiveActionFromDeficiency(){
        $LOG = Logger::getLogger('Action:' . __function__);
        $decodedObject = $this->convertInputJson();
        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to CorrectiveAction');
        }
        else if( $decodedObject instanceof ActionError){
            return $decodedObject;
        }
        else{
            $action = end($decodedObject->getCorrectiveActions());
            $dao = new GenericDAO(new CorrectiveAction());
            if($dao->deleteById($action["Key_id"])){
                $decodedObject->setCorrectiveActions(null);
                return $decodedObject;
            }else{
                return new ActionError("Something Went Wrong");
            }

        }
    }

    private function canDo($thing){
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
            //hash the rooms by their key ids so we can quickly push the relevant ones into a potentially large collection of checklists
            $orderedRooms = array();
            foreach($rooms as $room){
                $orderedRooms[$room->getKey_id()] = $room;
            }


            $masterHazards = array();
            //iterate the rooms and find the hazards present

            $LOG->fatal($inspection->getPrincipal_investigator_id);
            foreach ($orderedRooms as $room){
                $hazardlist = $this->getHazardsInRoomByPi($room->getKey_id(), $inspection->getPrincipal_investigator_id());
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
                        	$checklist->setParent_hazard_id($hazard->getParent_hazard_id());
                            $checklist->setOrderIndex($hazard->getOrder_index());
                            $checklists[] = $checklist;
                        }
                    }
                }
            }

            foreach($checklists as $checklist){
                $checklist->setInspectionRooms($orderedRooms);
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
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    public function getInspectionsByPIId( $id = NULL ){
        //Get responses for Inspection
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $piId = $this->getValueFromRequest('id', $piId);

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

    public function getArchivedInspectionsByPIId( $id = NULL){
        //Get responses for Inspection
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $piId = $this->getValueFromRequest('piId', $piId);

        if( $piId !== NULL ){

            $pi = $this->getPIById($piId);

            $inspectionsDao = $this->getDao(new Inspection);
            $whereClauseGroup = new WhereClauseGroup( array( new WhereClause("cap_submitted_date","IS NOT", "NULL") ) );
            $inspections = $inspectionsDao->getAllWhere($whereClauseGroup);

            return $inspections;
        }
        else{
            //error
            return new ActionError("No request parameter 'inspectionId' was provided");
        }
    }

    public function resetChecklists( $id = NULL, $report = null  ){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $id = $this->getValueFromRequest('id', $id);
        $report = $this->getValueFromRequest('report', $report);
        if( $id !== NULL ){
            $dao = $this->getDao(new Inspection());

            //get inspection
            $inspection = $dao->getById($id);

            // check if this is an inspection we're just starting
            if( $inspection->getDate_started() == NULL ) {
                $inspection->setDate_started(date("Y-m-d H:i:s"));
                $dao->save($inspection);
            }

            // Remove previous checklists (if any) and recalculate the required checklist.
            $oldChecklists = $inspection->getChecklists();
            if (!empty($oldChecklists) && $report == null) {
                // remove the old checklists
                foreach ($oldChecklists as $oldChecklist) {
                    $dao->removeRelatedItems($oldChecklist->getKey_id(),
                    						 $inspection->getKey_id(),
                    						 DataRelationship::fromArray(Inspection::$CHECKLISTS_RELATIONSHIP));
                }
            }


            // Calculate the Checklists needed according to hazards currently present in the rooms covered by this inspection
            if($report == null){
                $LOG->fatal('should be getting new list of checklists');
                $checklists = $this->getChecklistsForInspection($inspection->getKey_id());
            }
            //if we are loading a report instead of a list of checklists for an inspection, we don't update the list of checklists
            else{
                $LOG->fatal('should be retrieving old checklists');
                $checklists = $oldChecklists;
            }

            $hazardIds = array();
            // add the checklists to this inspection
            foreach ($checklists as $checklist){
                if($report == null){
                    $dao->addRelatedItems($checklist->getKey_id(),$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$CHECKLISTS_RELATIONSHIP));
                    $checklist->setInspectionId($inspection->getKey_id());
                    $checklist->setRooms($inspection->getRooms());
                    //filter the rooms, but only for hazards that aren't in the General branch, which should always have all the rooms for an inspection
                    //9999 is the key_id for General Hazard
                    if($checklist->getMaster_id() != 9999){
                        $checklist->filterRooms($inspection->getPrincipal_investigator_id());
                    }
                }

                $entityMaps = array();
                $entityMaps[] = new EntityMap("lazy","getHazard");
                $entityMaps[] = new EntityMap("lazy","getRooms");
                $entityMaps[] = new EntityMap("eager","getInspectionRooms");
                $entityMaps[] = new EntityMap("eager","getQuestions");
                $checklist->setEntityMaps($entityMaps);
                $hazardIds[] = $checklist->getHazard_id();

            }

			//recurse down hazard tree.  look in checklists array for each hazard.  if checklist is found, push it into ordered array.
            $orderedChecklists = array();
            $orderedChecklists = $this->recurseHazardTreeForChecklists($checklists, $hazardIds, $orderedChecklists, $this->getHazardById(10000));
            $inspection->setChecklists( $orderedChecklists );

            $entityMaps = array();
            $entityMaps[] = new EntityMap("eager","getInspectors");
            $entityMaps[] = new EntityMap("eager","getRooms");
            $entityMaps[] = new EntityMap("lazy","getResponses");
            $entityMaps[] = new EntityMap("eager","getPrincipalInvestigator");
            $entityMaps[] = new EntityMap("eager","getChecklists");
            $inspection->setEntityMaps($entityMaps);

            //make sure we get the right rooms for our branch level checklists
            //ids of the branch level hazards, excluding general, which is always in every room
            $realBranchIds = array(1,10009,100010);
            $neededRoomIds = array();
            $neededRooms   = array();
            foreach($orderedChecklists as $list){

                if(in_array($list->getHazard_id(), $realBranchIds)){
                    //if(!in_array(,$neededRoomIds))
                    //evaluate what rooms we need.  any room a checklist for a child of this one has should be pushed
                    $childLists =  $this->getChildLists($list, $orderedChecklists);
                    foreach($childLists as $childList){
                        foreach($childList->getInspectionRooms() as $room){
                            if(!in_array($room->getKey_id(), $neededRoomIds)){
                                array_push($neededRoomIds, $room->getKey_id());
                                array_push($neededRooms, $room);
                            }
                        }
                    }
                    $list->setInspectionRooms($neededRooms);
                }
            }

            return $inspection;
        }
        else{
            //error
            return new ActionError("No request parameter 'id' was provided");
        }


    }

    private function getChildLists(Checklist $list, array $orderedChecklists){
        $lists = array();
        foreach($orderedChecklists as $child){
            if($child->getKey_id() != $list->getKey_id() && $child->getMaster_id() == $list->getHazard_id()){
                $lists[] = $child;
            }
        }
        return $lists;
    }

    private function  recurseHazardTreeForChecklists( &$checklists, $hazardIds, &$orderedChecklists, $hazard = null ) {
    	$LOG = Logger::getLogger( 'Action:' . __function__ );

    	if($hazard == null){
    		//get the "Root hazard".  It's key_id is 10000, hence the magic number
    		$hazard = $this->getHazardById(10000);
    	}
    	if($orderedChecklists == NULL){
    		$orderedChecklists = array();
    	}
	    foreach($hazard->getActiveSubHazards() as $child){
	    	$idx = $this->findChecklist( $child->getChecklist(), $checklists );
	    	if(in_array($child->getKey_id(), $hazardIds)  && (int) $idx !== false ){
                array_push($orderedChecklists,$checklists[$idx]);
	    		unset($checklists[$idx]);
	    	}

    		$this->recurseHazardTreeForChecklists($checklists, $hazardIds, $orderedChecklists, $child);
	    }
	    return $orderedChecklists;

	}

	private function findChecklist($checklist, $lists){
		if($checklist == NULL)return false;
		$LOG = Logger::getLogger(__FUNCTION__);
		foreach($lists as $key=>$list){
			if($list->getKey_id() == $checklist->getKey_id()){
				return (int) $key;
			}
		}
		return false;
	}

    public function getDeficiencySelectionById( $id = NULL ){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ){
            $dao = $this->getDao();
            return $dao->getDeficiencySelectionById($id);
        }
        else{
            //error
            return new ActionError("No request parameter 'id' was provided");
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
            return new ActionError("No request parameter 'id' was provided");
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
            return new ActionError("No request parameter 'id' was provided");
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
            return new ActionError("No request parameter 'id' was provided");
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
            return new ActionError("No request parameter 'id' was provided");
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
            return new ActionError("No request parameter 'id' was provided");
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
            return new ActionError("No request parameter 'id' was provided");
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

    public function loginTest(){

    }



    public function lookupUser($username = NULL) {
        //Get responses for Inspection
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $username = $this->getValueFromRequest('username', $username);

        $ldap = new LDAP();
        $user = new User();

        $fieldsToFind = array("cn","sn","givenName","mail");
        if ($ldapData = $ldap->GetAttr($username, $fieldsToFind)){
            $user->setFirst_name(ucfirst(strtolower($ldapData["givenName"])));
            $user->setLast_name(ucfirst(strtolower($ldapData["sn"])));
            $user->setEmail(strtolower($ldapData["mail"]));
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

            $footerText = "\n\n Access the results of this inspection, and document any corrective actions taken, by logging into the RSMS portal located at http://radon.qa.sc.edu/rsms with your university ID and password.";
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
                $beforeOrderIdx = $hazard->getOrder_index() - 1;
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
            return $hazard;
            return $this->getHazardTreeNode($hazard->getParent_hazard_id());
        }
        else{
            //error
            if($hazardId == null)return new ActionError("No request parameter 'hazardId' was provided");
            if($beforeHazardId == null)return new ActionError("No request parameter 'beforeHazardId' was provided");
            if($afterHazardId == null)return new ActionError("No request parameter 'afterHazardId' was provided");
        }

    }

    public function getInspectionSchedule($year = NULL){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        // read the Year value from the request.
        $year = $this->getValueFromRequest('year', $year);

        // If the year is null, choose the current year.
        if ($year == null){
            $year = $this->getCurrentYear();
        }
                // Call the database
		$LOG->fatal('getting schedule for ' . $year);
        $dao = $this->getDao(new Inspection());
        $inspectionSchedules = $dao->getInspectionsByYear($year);

        foreach ($inspectionSchedules as &$is){
            if ($is->getInspection_id() !== null){
                $inspection = $dao->getById($is->getInspection_id());

                $entityMaps = array();
                $entityMaps[] = new EntityMap("eager","getInspectors");
                $entityMaps[] = new EntityMap("lazy","getRooms");
                $entityMaps[] = new EntityMap("lazy","getResponses");
                $entityMaps[] = new EntityMap("lazy","getDeficiency_selections");
                $entityMaps[] = new EntityMap("lazy","getPrincipalInvestigator");
                $entityMaps[] = new EntityMap("lazy","getChecklists");
                $entityMaps[] = new EntityMap("eager","getStatus");

                $inspection->setEntityMaps($entityMaps);

                $filteredRooms = array();
                $rooms = $inspection->getRooms();
                foreach( $rooms as $key=>$room ){
                	if( $room->getBuilding_id() == $is->getBuilding_key_id() ){
                		array_push($filteredRooms, $room);
                	}
                }
                $is->setInspection_rooms($filteredRooms);

                $is->setInspections($inspection);
            }

            $piDao = $this->getDao(new PrincipalInvestigator());
            $pi = $piDao->getById($is->getPi_key_id());
            $rooms = $pi->getRooms();
            $pi_bldg_rooms = array();
            foreach ($rooms as $room){
                //$LOG->debug($room);
                $bldg = $room->getBuilding();
                if ($bldg->getKey_id() == $is->getBuilding_key_id()){
                    $pi_bldg_rooms[] = $room;
                }
            }
            $is->setBuilding_rooms($pi_bldg_rooms);
        }

        return $inspectionSchedules;
    }

    public function getAllLabLocations(){
        $LOG = Logger::getLogger( 'Action:' . __function__ );

        $dao = $this->getDao(new Inspection());
        $rooms = $dao->getAllLocations();
        //return $rooms;
        $packedLocations = array();
        $skip = false;
        $previousRoomID = 0;
        foreach ($rooms as &$roomDTO){
            if ( $roomDTO->getRoom_id() !== NULL && $roomDTO->getPi_key_id() !== null && $previousRoomID !== $roomDTO->getRoom_id() ){
                    $roomDao = $this->getDao(new Room());
                    $room = $roomDao->getById($roomDTO->getRoom_id());
                    $pis = $room->getPrincipalInvestigators();
                    $entityMaps = array();
                    $entityMaps[] = new EntityMap("lazy","getLabPersonnel");
                    $entityMaps[] = new EntityMap("lazy","getRooms");
                    $entityMaps[] = new EntityMap("eager","getDepartments");
                    $entityMaps[] = new EntityMap("eager","getUser");
                    $entityMaps[] = new EntityMap("lazy","getInspections");
                    $entityMaps[] = new EntityMap("lazy","getPi_authorization");
                    $entityMaps[] = new EntityMap("lazy", "getActiveParcels");
                    $entityMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
                    $entityMaps[] = new EntityMap("lazy", "getPurchaseOrders");
                    $entityMaps[] = new EntityMap("lazy", "getSolidsContainers");
                    $entityMaps[] = new EntityMap("lazy", "getPickups");
                    $entityMaps[] = new EntityMap("lazy", "getScintVialCollections");
                    $entityMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
                    $entityMaps[] = new EntityMap("lazy","getOpenInspections");
                    $entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
                    $entityMaps[] = new EntityMap("lazy","getVerifications");
                    $entityMaps[] = new EntityMap("lazy","getBuidling");
                    $entityMaps[] = new EntityMap("lazy","getCurrentVerifications");
                    $entityMaps[] = new EntityMap("lazy","getWipeTests");

                    foreach($pis as $pi){
                        $pi->setEntityMaps($entityMaps);
                    }
                    $roomDTO->setPrincipal_investigators($pis);
                    $previousRoomID == $roomDTO->getRoom_id();
                }
        }

        return $rooms;

    }

    /**
     * Swaps two questions in a checklist. TODO This could be generalized to work
     * on any two entities with a setOrderIndex method.
     *
     * @param int $firstKeyId
     * @param int $secondKeyId
     */
    public function swapQuestions($id1 = NULL, $id2 = NULL) {

        $LOG = Logger::getLogger( 'Action:' . __function__ );
        $firstKeyId = $this->getValueFromRequest('firstKeyId', $id1);
        $secondKeyId = $this->getValueFromRequest('secondKeyId', $id2);

        // get objects we're modifying
        $questionDao = $this->getDao(new Question());
        $firstObject = $questionDao->getById($firstKeyId);
        $secondObject = $questionDao->getById($secondKeyId);

        // make sure both questions are part of the same checklist
        if( $firstObject->getChecklist_id() !== $secondObject->getChecklist_id() ) {
            return new ActionError("Questions had different parent Checklist");
        }

        // swap order indicies of the two objects
        $index1 = $firstObject->getOrder_index();
        $index2 = $secondObject->getOrder_index();
        $firstObject->setOrder_index($index2);
        $secondObject->setOrder_index($index1);
        $this->saveQuestion($firstObject);
        $this->saveQuestion($secondObject);

        // It's easier on the client if we pass back the modified parent list.
        // Not strictly necessary and slightly slower, but far simpler. Can change later.
        $checklistDao = $this->getDao(new Checklist());
        $parentList = $checklistDao->getById( $firstObject->getChecklist_id() );
        return $parentList;
    }

    public function getLocationCSV(){
        $LOG = Logger::getLogger( 'Action:' . __function__ );
        $roomDao = $this->getDao(new Room());
        $rooms = $roomDao->getAll(NULL,NULL,true);

        usort($rooms, function($a, $b)
        {
            return strcmp($a->getBuilding()->getName(), $b->getBuilding()->getName());
        });

        $csvDate = date("F j, Y");
        $LOG->debug($csvDate);
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"$csvDate\"Lab_Locations.csv");
        $output = fopen('php://output', 'w');
        fputcsv($output, array('Building', 'Room', 'Lab PIS', 'Departments'));

        foreach ($rooms as $room){
            $i = 0;
            $building = $room->getBuilding();
            $piString = '';
            $departmentString = '';
            foreach($room->getPrincipalInvestigators() as $pi){
                $i++;
                $user = $pi->getUser();
                $piString .= $user->getName();
                $departments = $pi->getDepartments();
                $j = 0;
                foreach($departments as $dept){
                    $j++;

                    $departmentString .= $dept->getName();
                    if( $j != count($departments) ){
                        $departmentString .= "\n";
                        $piString .= "\n";
                    }
                }
                if( $i != count($room->getPrincipalInvestigators() ) ){
                    $piString .= "\n\n ";
                    $departmentString .= "\n\n";
                }
            }
            if($piString == '')$piString = 'Unassigned';
            if($departmentString == '')$departmentString = 'Unassigned';

            fputcsv($output, array($building->getName(), $room->getName(), $piString, $departmentString));

        }
        fclose($output);
    }

    public function getMyLab($id = null){

        if($id==null)$id = $this->getValueFromRequest('id', $id);
        if($id==null)return new ActionError('No request param "id" provided.');
        $principalInvestigator = $this->getPIById($id);

        $entityMaps[] = new EntityMap("lazy","getLabPersonnel");
        $entityMaps[] = new EntityMap("lazy","getRooms");
        $entityMaps[] = new EntityMap("lazy","getDepartments");
        $entityMaps[] = new EntityMap("eager","getUser");
        $entityMaps[] = new EntityMap("eager","getInspections");
        $entityMaps[] = new EntityMap("lazy","getPi_authorization");
        $entityMaps[] = new EntityMap("lazy", "getActiveParcels");
        $entityMaps[] = new EntityMap("lazy", "getCarboyUseCycles");
        $entityMaps[] = new EntityMap("lazy", "getPurchaseOrders");
        $entityMaps[] = new EntityMap("lazy", "getSolidsContainers");
        $entityMaps[] = new EntityMap("lazy", "getPickups");
        $entityMaps[] = new EntityMap("lazy", "getScintVialCollections");
        $entityMaps[] = new EntityMap("lazy", "getCurrentScintVialCollections");
        $entityMaps[] = new EntityMap("lazy","getOpenInspections");
        $entityMaps[] = new EntityMap("lazy","getQuarterly_inventories");
        $entityMaps[] = new EntityMap("eager","getVerifications");
        $entityMaps[] = new EntityMap("lazy","getBuidling");
        $entityMaps[] = new EntityMap("lazy","getCurrentVerifications");
        $entityMaps[] = new EntityMap("lazy","getWipeTests");

        $principalInvestigator->setEntityMaps($entityMaps);

        return $principalInvestigator;
    }

    //generate a random float
    public function random_float ($min,$max) {
        return ($min+lcg_value()*(abs($max-$min)));
    }

    public function getCurrentYear(){
        return date("Y");
    }

    public function getAllSupplementalObservations(){
        $dao = $this->getDao(new SupplementalObservation());
        return $dao->getAll();
    }

    public function getRelationships( $class1 = NULL, $class2 = NULL ){
    	$LOG = Logger::getLogger( 'Action:' . __function__ );

    	if($class1==NULL)$class1 = $this->getValueFromRequest('class1', $class1);
    	if($class2==NULL)$class2 = $this->getValueFromRequest('class2', $class2);

    	// make sure first letter of class name is capitalized.
    	$class1 = ucfirst($class1);
    	$class2 = ucfirst($class2);

    	$relationshipFactory = new RelationshipMappingFactory();
    	// get the relationship mapping for the relevant classes
    	$relationship = $relationshipFactory->getRelationship($class1, $class2);

    	if( $relationship instanceof ActionError ) {
    		return $relationship;
    	}

    	$dao = new GenericDAO(new RelationDto());

    	$relationships = $dao->getRelationships($relationship);
    	//$LOG->fatal($relationships);
    	return $relationships;
    }

    public function setMasterHazardIds(){
    	$l = Logger::getLogger("hazardthingus");
    	$dao = new GenericDAO(new Hazard());
    	$hazards = $dao->getLeafLevelHazards();

    	$entityMaps = array();
    	$entityMaps[] = new EntityMap("lazy","getSubHazards");
    	$entityMaps[] = new EntityMap("lazy","getActiveSubHazards");
    	$entityMaps[] = new EntityMap("lazy","getChecklist");
    	$entityMaps[] = new EntityMap("lazy","getRooms");
    	$entityMaps[] = new EntityMap("lazy","getInspectionRooms");
    	$entityMaps[] = new EntityMap("lazy","getHasChildren");
    	$entityMaps[] = new EntityMap("lazy","getParentIds");
    	$entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");

    	foreach($hazards as $hazard){
    		$this->setMasterHazardId($hazard);
    		$hazard->setEntityMaps($entityMaps);
    	}

    	return $hazards;
    }
    /*
     * sets a hazard's branch level parent's key id as its master_hazard_id and saves it
     * @param Hazard $hazard
     * @param Hazard $parent The parent of the hazard so we can recurce up the tree if we need to
     * @return Hazard $hazard
     */
    private function setMasterHazardId(Hazard &$hazard, Hazard $parentId = null){
    	$l = Logger::getLogger("hazardthingus");

    	//key_ids of the branch level hazards, which are children of the root hazard, plus root hazard's id
    	$masterIds = array(1,9999,10009,10010,10000);

    	if($parentId != null){
    		$id = $parentId;
    		if($id == null){
    			return null;
    		}
    	}else{
    		$id = $hazard->getParent_hazard_id();
    		if($id == null){
    			return null;
    		}
    	}

    	if(in_array($id, $masterIds)){
    		$hazard->setMaster_hazard_id($id);
    		$this->saveHazard($hazard);
    	}else{
    		$parentId = $this->getHazardById($id)->getParent_hazard_id();
    		$this->setMasterHazardId($hazard, $parentId);
    	}
    	return null;
    	//return $this->saveHazard($hazard);

    }
}
?>

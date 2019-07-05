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

	public function getCurrentRoles(){
		if($_SESSION && $_SESSION['USER']){
			$user = $_SESSION['USER'];
			$currentRoles = array();
			foreach($user->getRoles() as $role){
				$currentRoles[] = $role->getName();
			}
			$this->getPropertyByName("PrincipalInvestigator", 1, "rooms" );
			return $currentRoles;
		}
		return array();
	}

    public function getUserDefaultPage() {
        return $this->getDestination();
    }

    /**
     * Chooses a return value based on the parameters. If $paramValue
     * is specified, it is returned. Otherwise, $valueName is taken from $_REQUEST.
     *
     * If $valueName is not present in $_REQUEST, NULL is returned.
     *
     * @param string|NULL $valueName
     * @param string|NULL $paramValue
     * @return string|object|false|NULL
     */
    public function getValueFromRequest( $valueName, $paramValue = NULL ){
        $LOG = Logger::getLogger('Action:' . __function__);

        if( $paramValue !== NULL ){
            return $paramValue;
        }
        else {
            return ActionDispatcher::getValueFromRequest( $valueName );
        }
    }

    public function convertInputJson( $modelObject = null){
        try{
            $decodedObject = JsonManager::decodeInputStream($modelObject);

            if( $decodedObject === NULL ){
                return new ActionError('No data read from input stream');
            }

            return $decodedObject;
        }
        catch(Exception $e){
            return new ActionError("Unable to decode JSON. Cause: $e");
        }
    }

    public function readRawInputJson(){
        try{
            return JsonManager::readRawJsonFromInputStream();
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
        return new GenericDAO( $modelObject );
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

    public function getDepartmentForUser( User &$user ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $department = null;
        $department_id = $user->getPrimary_department_id();

        if( $department_id == NULL ){

            $pi = $this->getPrincipalInvestigatorOrSupervisorForUser( $user );

            try{
                // user is a PI and may not have a 'primary department' assigned
                $department = $pi->getDepartments()[0];
            }
            catch( Exception $err ){
                $LOG->error("Unable to determine Department for this PI user");
            }
        }
        else {
            $dao = new GenericDAO(new Department());
            $department = $dao->getById($department_id);
        }

        return $department;
    }

    /**
     * Authenticate via LDAP
     */
    protected function loginLdap( $username, $password, $destination = NULL ){
        if( !ApplicationConfiguration::get('server.auth.providers.ldap', false) ){
            // LDAP auth is disabled
            return false;
        }

        $LOG = Logger::getLogger( __CLASS__ . '.' . __function__ );
        $LOG->debug("Attempt LDAP authentication for $username");

        // LDAP may not be loaded
        if( !class_exists('LDAP') ){
            $LOG->error("Attempting LDAP authentication, but no LDAP provider has been defined");
            return false;
        }

        $ldap = new LDAP();

        // if successfully authenticates by LDAP:
        try{
            if ($ldap->IsAuthenticated($username,$password)) {
                return $this->handleUsernameAuthorization($username);
            }
        }
        catch(Exception $e){
            if( stristr($e->getMessage(), 'Invalid Credentials') ){
                // Ignore this exception; it just indicates wrong password
            }
            else{
                $LOG->error("Error authenticating user over LDAP: " . $e->getMessage());
            }
        }

        $LOG->debug("LDAP AUTHENTICATION FAILED");
        return false;
    }

    /**
     * Wrapper for non-production login.
     * Because RSMS does not track passwords, this is a less-secure path
     * than LDAP.
     */
    protected function loginDev( $username, $password, $destination = NULL ){
        if( !ApplicationConfiguration::get('server.auth.providers.dev.impersonate', false) ){
            // Dev impersonate auth is disabled
            return false;
        }

        $LOG = Logger::getLogger( __CLASS__ . '.' . __function__ );
        $LOG->warn("Attempt DEV-IMPERSONATE authentication for '$username'");
        if( $password == ApplicationConfiguration::get('server.auth.providers.dev.impersonate.password') ){
            return $this->handleUsernameAuthorization($username);
        }

        $LOG->info("DEV-IMPERSONATE AUTHENTICATION FAILED");
        return false;
    }

    /**
     * Validate login for impersonatable test user with variable role
     */
    protected function loginAsRole($username, $password, $destination){
        if( !ApplicationConfiguration::get('server.auth.providers.dev.role', false) ){
            // Dev-role auth is disabled
            return false;
        }

        $LOG = Logger::getLogger( __CLASS__ . '.' . __function__ );
        $LOG->debug("Attempt DEV-ROLE authentication for $username");

        // ROLE assignment will be based on username, if it directly matches a role name
        $roles = array();
        foreach($this->getAllRoles() as $role) {
            $roles[] = $role->getName();
        }

        //the name of a real role was input in the form
        //hardcoded password for mock role login...
        if ( in_array($username, $roles) && $password == ApplicationConfiguration::get('server.auth.providers.dev.role.password') ) {
            // Look up test user and mix in requested role
            // Default to Test User with ID 1
            $user = $this->getUserById(1);

            $roleDao = $this->getDao(new Role());
            $whereClauseGroup = new WhereClauseGroup(array(new WhereClause("name", "=", $username)));
            $fakeRoles = $roleDao->getAllWhere($whereClauseGroup);

            $user->setFirst_name("Test user with role:");
            $user->setLast_name($username);

            if($username != "Principal Investigator"){
                $user->setSupervisor_id(1);
                $user->setInspector_id(10);
                $user->setInspector($this->getInspector(10));
            }else{
                $principalInvestigator = $this->getPIById(1);
                $user->setPrincipalInvestigator($principalInvestigator);
            }

            $user->setRoles($fakeRoles);
            $_SESSION['ROLE'] = $this->getCurrentUserRoles($user);

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
                $_SESSION['DESTINATION'] = $this->getDestination();
                $LOG->info("should redirect to " . $_SESSION['DESTINATION']);
            }

            // return true to indicate success
            return true;
        }

        return false;
    }

    /**
     * Login to hard-coded 'emergency' user
     */
    protected function loginEmergency($username, $password, $destination = NULL){
        // Check configured Emergency auth; enable by default
        if( !ApplicationConfiguration::get('server.auth.providers.emergency', true) ){
            // Emergency auth is disabled
            return false;
        }

        $LOG = Logger::getLogger( __CLASS__ . '.' . __function__ );

        // Hardcoded username and password for "emergency accounts"
        if($username === "EmergencyUser" && $password === ApplicationConfiguration::get('server.auth.providers.emergency.password')) {
            $LOG->info("Attempt emergency-user authentication");
            return $this->handleUsernameAuthorization("EmergencyUser");
        }

        return false;
    }

    /**
     * Perform final login actions for the given username.
     * It is assumed that the requestor has already been authenticated
     */
    protected function handleUsernameAuthorization($username){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __function__ );

        // Make sure they're an Erasmus user by username lookup
        $dao = new UserDAO();
        $user = $dao->getUserByUsername($username);

        if ($user == null) {
            // User does not exist
            $LOG->info("No such user '$username'");
            return false;
        }
        else if( !$user->getIs_active() ){
            // User is not active
            $LOG->info("Local authentication succeeded, but the user is inactive: $user");

            // successful LDAP login, but not an enabled Erasmus user, return false
            $_SESSION['ERROR'] = "Your account has been disabled. If you believe this is in error, please contact your administrator.";
            return false;
        }
        else {
            //the name of a real role was NOT input in the form, get the actual user's roles
            $_SESSION['ROLE'] = $this->getCurrentUserRoles($user);

            // put the USER into session
            $_SESSION['USER'] = $user;

            $_SESSION['DESTINATION'] = $this->getDestination();

            // return true to indicate success
            return true;
        }
    }

    public function impersonateUserAction($impersonateUsername = NULL, $currentPassword = NULL) {
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $LOG->info("User " . $this->getCurrentUser()->getUsername() . " attempting to impersonate $impersonateUsername");

        if( $impersonateUsername == $this->getCurrentUser()->getUsername() ){
            return new ActionError("Cannot impersonate yourself", 400);
        }

        if( isset($_SESSION['IMPERSONATOR']) ){
            return new ActionError("Cannot impersonate another user while impersonation session is active", 400);
        }

        // TODO: Verify current user's password
        // copy current-user info into session
        $_SESSION['IMPERSONATOR'] = array(
            'USER' => $_SESSION['USER'],
            'ROLE' => $_SESSION['ROLE']
        );

        return $this->handleUsernameAuthorization( $impersonateUsername );
    }

    public function stopImpersonating(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        if( isset($_SESSION['IMPERSONATOR']) ){
            $LOG->info("Closing impersonation session...");
            $_SESSION['USER'] = $_SESSION['IMPERSONATOR']['USER'];
            $_SESSION['ROLE'] = $_SESSION['IMPERSONATOR']['ROLE'];
            $_SESSION['IMPERSONATOR'] = null;
            $LOG->info("Impersonation session closed");

            return true;
        }

        // No one to stop impersonating
        return false;
    }

    public function getImpersonatableUsernames(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $userDao = new GenericDAO(new User());
        // Get all ACTIVE users; no sort
        $allUsers = $userDao->getAll("last_name", false, true);

        return array_map( function($u){ return new ImpersonatableUser($u); }, $allUsers);
    }

     public function loginAction( $username = NULL ,$password = NULL, $destination = NULL ) {
        $LOG = Logger::getLogger( __CLASS__ . '.' . __function__ );

        $username = $this->getValueFromRequest('username', $username);
        $password = $this->getValueFromRequest('password', $password);
        $destination = $this->getValueFromRequest('destination', $destination);

        if($destination != NULL)$_SESSION['DESTINATION'] = $destination;

        $loggedIn = false;

        // Handle special cases: hard-coded logins
        $special = $this->loginEmergency($username, $password, $destination)
            ||     $this->loginAsRole($username, $password, $destination);

        if( $special ){
            $LOG->warn("Handled hard-coded login of user '$username'");
            $loggedIn = true;
        }
        else {
            // Handle 'normal' login
            $loggedIn = $this->loginLdap($username, $password, $destination)
                ||      $this->loginDev($username, $password, $destination);
        }

        if( !$loggedIn ){
            $LOG->info("Failed login attempt for '$username'");
            // otherwise, return false to indicate failure
            $_SESSION['ERROR'] = "The username or password you entered was incorrect.";
            return false;
        }
        else{
            $LOG->info("Successful login attempt for '$username'");
            return true;
        }
    }

    /**
     * Determines if the currently logged in user's lab has permissions to run a method, based on the object type being retrieved, altered or created by that method
     *
     * @param object $object
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
     * @param object object
     * @return integer|boolean $value
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
        $roles = $this->getCurrentUserRoles();

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

    private function sessionHasRoles($roles){
        return count( array_intersect($_SESSION['ROLE']['userRoles'], $roles)) > 0;
    }

    private function getDestination(){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        if( isset($_SESSION["REDIRECT"]) ){
            $LOG->debug("User requested specific redirect");
            $destination = str_replace("%23", "#", $_SESSION["REDIRECT"]);
            $destination = str_replace(LOGIN_PAGE, "", $destination);
        }
        else{
            $LOG->debug('Get default destination for user');

            //get the proper destination based on the user's role
            // TODO: Assign defaults to each role and select user's destination from those
            //   This would keep these hard-coded role names out of here!

            // Non-lab roles go to RSMSCenter
            if( $this->sessionHasRoles(array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector")) ){
                $destination = 'views/RSMSCenter.php';
                $LOG->debug("User has non-lab roles");
            }

            // Emergency account goes to emergency hub
            else if( $this->sessionHasRoles( array("Emergency Account")) ){
                $destination = "views/hubs/emergencyInformationHub.php";
                $LOG->debug("User has emergency role");
            }

            // non-PI Department Chair goes to Reports
            else if( !$this->sessionHasRoles( array("Principal Investigator")) && $this->sessionHasRoles( array("Department Chair")) ){
                $destination = "reports/";
                $LOG->debug("User is a non-PI Department Chair");
            }

            // Otherwise, go to My Lab
            else {
                $destination = 'views/lab/MyLab.php';
                $LOG->debug("User has no special-case roles");
            }

            $LOG->debug("User's default Destination: $destination");
        }

        $LOG->info("Direct user to $destination");
        return $destination;
    }

    public function prepareRedirect( $redirect ){
    	$LOG = Logger::getLogger("redirect");
    	$redirect = $this->getValueFromRequest('redirect', $redirect);

    	$_SESSION["REDIRECT"] = $redirect;
        $LOG->info("Prepare redirect to " . $_SESSION["REDIRECT"]);
       	return true;

    }

    public function logoutAction(){
        //session_destroy();
        $_SESSION['USER'] = null;
        $_SESSION['ROLE'] = null;
        return true;
    }

    public function getCurrentUser(){
        // when a user is logged in and in session, return the currently logged in user.
        EntityManager::with_entity_maps(User::class, array(
            EntityMap::eager("getPrincipalInvestigator"),
            EntityMap::eager("getInspector"),
            EntityMap::eager("getSupervisor"),
            EntityMap::lazy("getRoles")
        ));

        EntityManager::with_entity_maps(PrincipalInvestigator::class, array(
            EntityMap::lazy("getRooms"),
            EntityMap::lazy("getLabPersonnel"),
            EntityMap::lazy("getUser")
        ));

        return $_SESSION['USER'];
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $userDao = $this->getDao( new User() );
        $allUsers = $userDao->getAll('last_name');

        return $allUsers;
    }

    public function getUserById( $id = NULL ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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

    public function getUserByUsername( $username ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        if( $username !== NULL ){
            $dao = new UserDAO();
            return $dao->getUserByUsername($username);
        }

        return null;
    }

    public function getSupervisorByUserId( $id = NULL ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ){
            $dao = $this->getDao(new User());
            $user = $dao->getById($id);

            $supervisor = $user->getSupervisor();
            if($supervisor != null){

                EntityManager::with_entity_maps(PrincipalInvestigator::class, array(
                    EntityMap::lazy("getLabPersonnel"),
                    EntityMap::lazy("getRooms"),
                    EntityMap::lazy("getDepartments"),
                    EntityMap::eager("getUser"),
                    EntityMap::lazy("getInspections"),
                    EntityMap::lazy("getPi_authorization"),
                    EntityMap::lazy("getActiveParcels"),
                    EntityMap::lazy("getCarboyUseCycles"),
                    EntityMap::lazy("getPurchaseOrders"),
                    EntityMap::lazy("getSolidsContainers"),
                    EntityMap::lazy("getPickups"),
                    EntityMap::lazy("getScintVialCollections"),
                    EntityMap::lazy("getCurrentScintVialCollections"),
                    EntityMap::lazy("getOpenInspections"),
                    EntityMap::lazy("getQuarterly_inventories"),
                    EntityMap::lazy("getVerifications"),
                    EntityMap::lazy("getBuidling"),
                    EntityMap::lazy("getCurrentVerifications"),
                    EntityMap::lazy("getWipeTests")
                ));

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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

    public function unassignLabUser($uid, $inactive = FALSE){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $uid = $this->getValueFromRequest('uid', $uid);
        $inactive = $this->getValueFromRequest('inactive', $inactive);
        if( !isset($uid) ){
            return new ActionError("ID is required", 400);
        }

        $userDao = new GenericDAO(new User());
        $user = $userDao->getById($uid);
        if( !isset($user) ){
            return new ActionError("No such user", 404);
        }

        if( $inactive == TRUE ){
            $LOG->info("Inactivating $user");
            $user->setIs_active(false);
        }

        // Unlink user from supervisor
        $pi = $user->getSupervisor();
        if( isset($pi) ){
            $LOG->info("Unassigning $user from $pi");
            $user->setSupervisor(null);
            $user->setSupervisor_id( null );
        }

        // If they are a Lab Contact, revoke that role
        if( CoreSecurity::userHasRoles($user, array('Lab Contact')) ){
            $LOG->debug("$user is Lab Contact");
            $roleDao = new RoleDAO();
            $contactRole = $roleDao->getByName('Lab Contact');

            $rel = new RelationshipDto();
            $rel->setMaster_id($user->getKey_id());
            $rel->setRelation_id($contactRole->getKey_id());
            $rel->setAdd(false);

            $LOG->info("Remove Lab Contact role from $user");
            $this->saveUserRoleRelation($rel);
        }

        $saved = $userDao->save($user);
        return $this->buildUserDTO($saved);
    }

    public function assignLabUserToPI($piid, $uid, $labContact = false){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $piid = $this->getValueFromRequest('piid', $piid);
        $uid = $this->getValueFromRequest('uid', $uid);
        $labContact = $this->getValueFromRequest('labContact', $labContact);

        // Prepare to add Lab Personnel role, and either add or remove lab contact role
        $roleActions = array(
            'Lab Personnel' => true,
            'Lab Contact' => $labContact
        );

        if( !isset($piid) || !isset($uid) ){
            return new ActionError("IDs are required", 400);
        }

        $userDao = new GenericDAO(new User());
        $piDao = new PrincipalInvestigatorDAO();

        $user = $userDao->getById($uid);
        if( !isset($user) ){
            return new ActionError("No such user", 404);
        }

        $pi = $piDao->getByid($piid);
        if( !isset($pi) ){
            return new ActionError("No such PI", 404);
        }

        // All required data is present; assign the user
        if( !$user->getIs_active() ){
            $LOG->info("Activating $user");
            $user->setIs_active(true);
        }

        $LOG->info("Assigning $user to $pi");
        $user->setSupervisor_id( $pi->getKey_id() );

        // Add/remove role(s) to user
        $roleDao = new RoleDAO();
        foreach($roleActions as $requiredRoleName => $addRole){
            if( $addRole == !CoreSecurity::userHasRoles($user, array($requiredRoleName)) ){
                $LOG->debug("$user does not have role '$requiredRoleName'");
                $requiredRole = $roleDao->getByName($requiredRoleName);

                $rel = new RelationshipDto();
                $rel->setMaster_id($user->getKey_id());
                $rel->setRelation_id($requiredRole->getKey_id());
                $rel->setAdd($addRole);

                $LOG->info( ($addRole ? 'Add' : 'Remove') . " $requiredRole " . ($addRole ? 'to' : 'from') . "$user");
                $this->saveUserRoleRelation($rel);
            }
        }

        // Empty object role value to force re-query (which will include the newly-saved values)
        $user->setRoles(null);

        $LOG->debug("Saving $user");
        $saved = $userDao->save($user);

        return $this->buildUserDTO($saved);
    }

    public function saveUser( $user = null ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        if( !isset($user) ){
            $decodedObject = $this->convertInputJson();
        }
        else{
            $decodedObject = $user;
        }

        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to User');
        }
        else if( $decodedObject instanceof ActionError){
            return $decodedObject;
        }
        else{
            $LOG->debug("Prepare to save user");
            $dao = $this->getDao( new User() );
            //if this user is new, make sure it's active
            if($decodedObject->getKey_id() == NULL){
                $decodedObject->setIs_active(true);
            }

            $user = $dao->save( $decodedObject );
            if( $user instanceof ActionError ){
                $LOG->error("Error saving user: $user");
                return $user;
            }

            $LOG->info("Saved user details: $user");

            // Save Roles
            if($decodedObject->getRoles() != NULL){
                $LOG->debug("Updating user roles...");

                // Collect IDs of existing & new roles
                function fn_getRoleId($r){
                    if( is_array($r) ){
                        return $r['Key_id'];
                    }
                    else{
                        return $r->getKey_id();
                    }
                };

                $userDao = new GenericDAO(new User());
                function updateRole($dao, $rid, $uid, $add) {
                    if( $add ){
                        $dao->addRelatedItems($rid, $uid, DataRelationship::fromArray(User::$ROLES_RELATIONSHIP));
                    }
                    else{
                        $dao->removeRelatedItems($rid, $uid, DataRelationship::fromArray(User::$ROLES_RELATIONSHIP));
                    }
                }

                $newRoleIds = array_map( 'fn_getRoleId', $decodedObject->getRoles() );
                $oldRoleIds = array_map( 'fn_getRoleId', $user->getRoles());

                $LOG->trace("New Role IDs: " . implode(', ', $newRoleIds));
                $LOG->trace("Old Role IDs: " . implode(', ', $oldRoleIds));

                /** Roles present in old entity which should be removed */
                $rolesToUnlink = array_diff($oldRoleIds, $newRoleIds);

                /** Roles not present in old entity which should be added */
                $rolesToAdd = array_diff($newRoleIds, $oldRoleIds);

                $LOG->debug("Roles requiring update: " . (count($rolesToUnlink) + count($rolesToAdd)));
                if( !empty($rolesToUnlink) ){
                    foreach($rolesToUnlink as $r){
                        $LOG->debug("Unlink Role #$r from User #" . $user->getKey_id());
                        updateRole($userDao, $r, $user->getKey_id(), false);
                    }
                }

                if( !empty($rolesToAdd) ){
                    foreach($rolesToAdd as $r){
                        $LOG->debug("Link Role #$r from User #" . $user->getKey_id());
                        updateRole($userDao, $r, $user->getKey_id(), true);
                    }
                }

                // Special-case: If user is assigned Lab Contact, make sure they also have Lab Personnel
                $allRoles = $this->getAllRoles();
                $_contactRole = null;
                $_personnelRole = null;
                foreach ($allRoles as $role){
                    if( $role->getName() == 'Lab Contact'){
                        $_contactRole = $role;
                    }
                    else if( $role->getName() == 'Lab Personnel'){
                        $_personnelRole = $role;
                    }
                }

                $_roles = $user->getRoles();
                $_isContact = in_array($_contactRole, $_roles);
                $_isPersonnel = in_array($_personnelRole, $_roles);

                if( $_isContact && !$_isPersonnel ){
                    $LOG->warn("User is assigned Lab Contact role but not Lab Peronnel role");
                    $LOG->info("Adding Lab Personnel role to user " . $user->getKey_id());
                    updateRole($userDao, $_personnelRole->getKey_id(), $user->getKey_id(), true);
                }

                // Clear roles to force object update...
                $user->setRoles(null);

                // Is this a newly-added Lab Contact?
                if( $_isContact ){
                    HooksManager::hook('after_save_lab_contact', $user);
                }
            }

            //see if we need to save a PI or Inspector object
            $savePI = false;
            $saveInspector = false;
            if($decodedObject->getRoles() != NULL){
                $LOG->debug("Check roles for special-cases");
                foreach($decodedObject->getRoles() as $role){
                    $role = $this->getRoleById($role['Key_id']);
                    if($role->getName() == "Principal Investigator")$savePI 	   = true;
                    if($role->getName() == "Safety Inspector")      $saveInspector = true;
                }
                $LOG->debug("PI:$savePI | inspector:$saveInspector");
            }

            //user was sent from client with Principal Investigator in roles array
            if($savePI){
                $LOG->debug("Processing PI details");
                 //we have a PI for this User.  We should set it's Is_active state equal to the user's is_active state, so that when a user with a PI is activated or deactivated, the PI record also is.
                if($decodedObject->getPrincipalInvestigator() != null){
                    $LOG->debug("Retrieve PI details from incoming data");
                    $pi = $decodedObject->getPrincipalInvestigator();
                    // FIXME: Assemble array into PI; JsonManager decoding may not have gone deep enough
                    if( is_array($pi) ){
                        $pi = JsonManager::assembleObjectFromDecodedArray($pi, new PrincipalInvestigator());
                    }
                }else{
                    $LOG->debug("Create new PI entity");
                    $pi = new PrincipalInvestigator();
                    $pi->setUser_id($user->getKey_id());
                }

                // Look up the old PI (if any)
                $pi_dao = new PrincipalInvestigatorDAO();
                $old_pi = $pi_dao->getByUserId( $user->getKey_id() );
                $LOG->debug("Old PI: $old_pi");

                $pi->setUser_id($user->getKey_id());
                $pi->setIs_active($user->getIs_active());

                $newPi = $this->savePI($pi);
                $LOG->info("Saved PI details: $pi");

                //set hazard relationships for any rooms the pi has
                $LOG->debug("Process rooms");
                foreach($newPi->getRooms() as $room){

                    $room->getHazardTypesArePresent();
                    $room = $this->saveRoom($room);
                    $LOG->debug("Saved $room");
                }

                // TODO: Only remove department if it isn't incoming
                if( isset($old_pi) && is_object($old_pi) ){
                    $LOG->debug("Removing old departments from PI #" . $old_pi->getKey_id());
                    // Remove all pre-existing departments
                    $old_depts = $old_pi->getDepartments();
                    if( isset($old_depts) ){
                        foreach($old_depts as $dept){
                            $LOG->debug("Unlink $dept");
                            $pi_dao->removeRelatedItems($dept->getKey_id(), $old_pi->getKey_id(), DataRelationship::fromArray(PrincipalInvestigator::$DEPARTMENTS_RELATIONSHIP));
                        }
                    }
                }
                else{
                    $LOG->debug("No old departments");
                }

                // Save incoming Departments
                $depts = $pi->getDepartments();
                if( isset($depts) ){
                    $LOG->debug("Link " . count($depts) . " incoming departments");
                    foreach($depts as $dept){
                        $LOG->debug("Linking dept #" . $dept['Key_id']);
                        $pi_dao->addRelatedItems($dept['Key_id'], $pi->getKey_id(), DataRelationship::fromArray(PrincipalInvestigator::$DEPARTMENTS_RELATIONSHIP));
                    }
                }

                $newPi->setDepartments($pi->getDepartments());
                $user->setPrincipalInvestigator($newPi);
            }

            //user was sent from client with Saftey Inspector in roles array
            if($saveInspector){
                $LOG->debug("Processing Inspector details");

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
                EntityManager::with_entity_maps(User::class, array(
                    EntityMap::eager("getPrincipalInvestigator"),
                    EntityMap::eager("getInspector"),
                    EntityMap::lazy("getSupervisor"),
                    EntityMap::eager("getRoles")
                ));

                EntityManager::with_entity_maps(PrincipalInvestigator::class, array(
                    EntityMap::lazy("getUser")
                ));

                return $dao->getById($user->getKey_id());
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
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
                        $LOG->debug($master_hazard . ' | ' . $masterHazardId);
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

            // Verify that the related Inspection isn't archived
            $inspection = $decodedObject->getResponse()->getInspection();
            if( $inspection->getIsArchived() ){
                // Forbid modifications to this inspection
                return new ActionError("Cannot save recommendation for an Archived inspection", 403);
            }

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
                    $LOG->debug($id);
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $dao = $this->getDao(new Hazard());
        // get the Root of the hazard tree
        $root = $dao->getById(10000);

        // Define which subentities to load
        EntityManager::with_entity_maps(Hazard::class, array(
            EntityMap::lazy("getSubhazards"),
            EntityMap::eager("getActiveSubhazards"),
            EntityMap::eager("getHasChildren"),
            EntityMap::lazy("getChecklist"),
            EntityMap::lazy("getRooms"),
            EntityMap::lazy("getInspectionRooms")
        ));

        // Return the object
        return $root;
    }

    public function getAllHazards(){
        //FIXME: This public function should return a FLAT COLLECTION of ALL HAZARDS; not a Tree
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $dao = $this->getDao(new Hazard());
        $hazards = $dao->getAll();

        EntityManager::with_entity_maps(Hazard::class, array(
            EntityMap::lazy("getSubHazards"),
            EntityMap::lazy("getActiveSubHazards"),
            EntityMap::lazy("getChecklist"),
            EntityMap::lazy("getRooms"),
            EntityMap::lazy("getInspectionRooms"),
            EntityMap::eager("getHasChildren"),
            EntityMap::lazy("getParentIds"),
            EntityMap::lazy("getPrincipalInvestigators")
        ));

        return $hazards;
    }

    public function getHazardTreeNode( $id = NULL){

        // get the node hazard
        $hazard = $this->getHazardById($id);
        $hazards = array();

        // prepare a load map for the subHazards to load Subhazards lazy but Checklist eagerly.
        EntityManager::with_entity_maps(Hazard::class, array(
            EntityMap::lazy("getSubHazards"),
            EntityMap::lazy("getActiveSubHazards"),
            EntityMap::eager("getChecklist"),
            EntityMap::lazy("getRooms"),
            EntityMap::lazy("getInspectionRooms"),
            EntityMap::eager("getHasChildren"),
            EntityMap::lazy("getParentIds")
        ));

        // prepare a load map for Checklist to load all lazy.
        EntityManager::with_entity_maps(Checklist::class, array(
            EntityMap::lazy("getHazard"),
            EntityMap::lazy("getQuestions")
        ));

        // For each child hazard, init a lazy-loading checklist, if there is one
        foreach ($hazard->getSubHazards() as $child){
            $checklist = $child->getChecklist();

            // push this hazard onto the hazards array
            $hazards[] = $child;

        }

        // Return the child hazards
        return $hazards;
    }


    //FIXME: Remove $name
    public function getHazardById( $id = NULL){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
            EntityManager::with_entity_maps(Hazard::class, array(
                EntityMap::lazy("getSubHazards"),
                EntityMap::lazy("getActiveSubHazards"),
                EntityMap::eager("getChecklist"),
                EntityMap::lazy("getRooms"),
                EntityMap::lazy("getInspectionRooms"),
                EntityMap::eager("getHasChildren"),
                EntityMap::lazy("getParentIds"),
                EntityMap::lazy("getPrincipalInvestigators")
            ));

            $checklist = $savedHazard->getChecklist();

            if($checklist != NULL){
                EntityManager::with_entity_maps(Checklist::class, array(
                    EntityMap::lazy("getHazard"),
                    EntityMap::lazy("getQuestions")
                ));

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

    private function _before_save_room_check_room_pis(Room &$room, Array $oldPIs, Array $newPIs){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $getIds = function($pi){
            if(is_array($pi) )
                return $pi['Key_id'];
            return $pi->getKey_id();
        };

        $LOG->debug("Verifying room changes...");
        $existingPiIds = array_map($getIds, $oldPIs);
        $LOG->debug("Existing PIs: " . implode(', ', $existingPiIds));

        $incomingPiIds = array_map($getIds, $newPIs);
        $LOG->debug("Incoming PIs: " . implode(', ', $incomingPiIds));

        $removingPiIds = array_diff($existingPiIds, $incomingPiIds);
        if( !empty($removingPiIds) ){
            $LOG->debug("Validate unassignment of PIs: " . implode(', ', $removingPiIds));
            return $this->validateRoomUnassignments($room, $removingPiIds);
        }

        $LOG->debug("No PIs are being unassigned");
        return true;
    }

    public function saveRoom($room = null){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        if($room == null){
            $decodedObject = $this->convertInputJson();
        }else{
            $decodedObject = $room;
        }

        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to Hazard');
        }
        else if( $decodedObject instanceof ActionError ){
            return $decodedObject;
        }
        else{
            $dao = new RoomDAO();

            $room = null;
            if( $decodedObject->hasPrimaryKeyValue() ){
                // Update existing room
                $room = $this->getRoomById($decodedObject->getKey_id());
                $LOG->info("Update existing room $room");
            }
            else{
                // Create new room
                $room = new Room();
                $LOG->info("Create new Room");
            }

            if(is_array( $decodedObject->getPrincipalInvestigators() )){
                $LOG->debug($decodedObject);

                // First, validate any PI removals
                $canSaveRoom = $this->_before_save_room_check_room_pis(
                    $room,
                    $room->getPrincipalInvestigators() ?? [],
                    $decodedObject->getPrincipalInvestigators()
                );

                if( !$canSaveRoom ){
                    return new ActionError("One or more PIs have Hazards assigned to room " . $room->getKey_id(), 200);
                }

                if( $room->getPrincipalInvestigators() != null ){
                    // Remove any existing
                    foreach ($room->getPrincipalInvestigators() as $child){
                        $dao->removeRelatedItems($child->getKey_id(),$room->getKey_id(),DataRelationship::fromArray(Room::$PIS_RELATIONSHIP));
                    }
                }

                // Add any new
                foreach($decodedObject->getPrincipalInvestigators() as $pi){
                    //$LOG->fatal($pi["Key_id"] . ' | room: ' . $room->getKey_id());
                    if(gettype($pi) == "array"){
                        $piId = $pi["Key_id"];
                    }else{
                        $piId = $pi->getKey_id();
                    }
                    $dao->addRelatedItems($piId,$room->getKey_id(),DataRelationship::fromArray(Room::$PIS_RELATIONSHIP));

                }
            }

            $LOG->info("Saving... $decodedObject");
            $room = $dao->save($decodedObject);

            EntityManager::with_entity_maps(Room::class, array(
                EntityMap::eager("getPrincipalInvestigators"),
                EntityMap::lazy("getHazards"),
                EntityMap::lazy("getHazard_room_relations"),
                EntityMap::lazy("getHas_hazards"),
                EntityMap::eager("getBuilding"),
                EntityMap::lazy("getSolidsContainers")
            ));

            $LOG->info("Saved $room");
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

    public function addCorrectedInInspection( $deficiencyId = NULL, $inspectionId = NULL, $supplemental = null ){
        $LOG = Logger::getLogger('Action:' . __function__);

        $inspectionId = $this->getValueFromRequest('inspectionId', $inspectionId);
        $deficiencyId = $this->getValueFromRequest('deficiencyId', $deficiencyId);
        $supplemental = $this->getValueFromRequest('supplemental', $supplemental);

        if( $inspectionId !== NULL  && $deficiencyId!== NULL){

            // Find the deficiencySelection
            if($supplemental == null){
                $ds = $this->getDeficiencySelectionByInspectionIdAndDeficiencyId($inspectionId,$deficiencyId);
            }else{
                $sd = new GenericDAO(new SupplementalDeficiency());
                $ds = $sd->getById($deficiencyId);
            }

            if ($ds == null){
                return new ActionError("Couldn't find DeficiencySelection for that Inspection and Deficiency");
            }

            $LOG->debug("Prepare to add Corrected flag to DeficiencySelection: " . $ds->getKey_id());

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

    public function removeCorrectedInInspection( $deficiencyId = NULL, $inspectionId = NULL, $supplemental = null ){
        $LOG = Logger::getLogger('Action:' . __function__);

        $inspectionId = $this->getValueFromRequest('inspectionId', $inspectionId);
        $deficiencyId = $this->getValueFromRequest('deficiencyId', $deficiencyId);
        $supplemental = $this->getValueFromRequest('supplemental', $supplemental);

        if( $inspectionId !== NULL  && $deficiencyId!== NULL){

            // Find the deficiencySelection
            if($supplemental == null){
                $ds = $this->getDeficiencySelectionByInspectionIdAndDeficiencyId($inspectionId,$deficiencyId);
            }else{
                $sd = new GenericDAO(new SupplementalDeficiency());
                $ds = $sd->getById($deficiencyId);
            }

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
            $decodedObject =$dao->save($decodedObject);
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

        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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

        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
                EntityManager::with_entity_maps(PrincipalInvestigator::class, array(
                    EntityMap::eager("getLabPersonnel"),
                    EntityMap::eager("getRooms"),
                    EntityMap::eager("getDepartments"),
                    EntityMap::eager("getUser"),
                    EntityMap::lazy("getInspections"),
                    EntityMap::lazy("getPi_authorization"),
                    EntityMap::lazy("getActiveParcels"),
                    EntityMap::lazy("getCarboyUseCycles"),
                    EntityMap::lazy("getPurchaseOrders"),
                    EntityMap::lazy("getSolidsContainers"),
                    EntityMap::lazy("getPickups"),
                    EntityMap::lazy("getScintVialCollections"),
                    EntityMap::lazy("getCurrentScintVialCollections"),
                    EntityMap::lazy("getOpenInspections"),
                    EntityMap::lazy("getQuarterly_inventories"),
                    EntityMap::lazy("getVerifications"),
                    EntityMap::lazy("getBuidling"),
                    EntityMap::lazy("getCurrentVerifications"),
                    EntityMap::lazy("getWipeTests")
                ));
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ){
            $dao = $this->getDao(new PrincipalInvestigator());
            $pi = $dao->getById($id);

            $buildings = array();

            EntityManager::with_entity_maps(PrincipalInvestigator::class, array(
                EntityMap::lazy("getLabPersonnel"),
                EntityMap::eager("getRooms"),
                EntityMap::eager("getDepartments"),
                EntityMap::eager("getUser"),
                EntityMap::eager("getBuidling"),
                EntityMap::lazy("getInspections"),
                EntityMap::lazy("getPi_authorization"),
                EntityMap::lazy("getActiveParcels"),
                EntityMap::lazy("getCarboyUseCycles"),
                EntityMap::lazy("getPurchaseOrders"),
                EntityMap::lazy("getSolidsContainers"),
                EntityMap::lazy("getPickups"),
                EntityMap::lazy("getScintVialCollections"),
                EntityMap::lazy("getCurrentScintVialCollections"),
                EntityMap::lazy("getOpenInspections"),
                EntityMap::lazy("getQuarterly_inventories"),
                EntityMap::lazy("getVerifications"),
                EntityMap::lazy("getCurrentVerifications"),
                EntityMap::lazy("getWipeTests")
            ));

            EntityManager::with_entity_maps(Room::class, array(
                EntityMap::lazy("getPrincipalInvestigators"),
                EntityMap::lazy("getHazards"),
                EntityMap::lazy("getHazard_room_relations"),
                EntityMap::lazy("getHas_hazards"),
                EntityMap::lazy("getBuilding"),
                EntityMap::lazy("getSolidsContainers")
            ));

            EntityManager::with_entity_maps(Building::class, array(
                EntityMap::eager("getRooms"),
                EntityMap::lazy("getCampus"),
                EntityMap::lazy("getCampus_id"),
                EntityMap::lazy("getPhysical_address")
            ));

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
                        $rooms[] = $room;
                    }
                }

                $building->setRooms($rooms);
            }

            $pi->setBuildings($buildings);

            return $pi;
        }
        else{
            //error
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    /**
     * Retrieve basic details (Key_id, Name) of all Buildings
     */
    public function getAllBuildingNames(){
        $dao = $this->getDao(new Building());

        // get all buildings
        $buildings = $dao->getAll();
        $infos = DtoFactory::buildDtos($buildings, 'DtoFactory::buildingToDto');

        return $infos;
    }

    /**
     * Retrieve basic details (Key_id, Name) of all Rooms in a given building
     */
    public function getAllBuildingRoomNames($buildingId){
        $buildingId = $this->getValueFromRequest('buildingId', $buildingId);

        // get building
        $dao = $this->getDao(new Building());
        $building = $dao->getById($buildingId);

        // Get details for its rooms
        $rooms = $building->getRooms();
        $infos = DtoFactory::buildDtos($rooms, 'DtoFactory::roomToDto');

        return $infos;
    }

    /**
     * Retrieve basic details (Key_id, Name) of all Principal Investigators
     */
    public function getAllPINames(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $dao = $this->getDao(new PrincipalInvestigator());
        $pis = $dao->getAllWith(DataRelationship::fromArray(PrincipalInvestigator::$ROOMS_RELATIONSHIP));

        // Reduce the PIs to just IDs and Names
        $infos = DtoFactory::buildDtos($pis, 'DtoFactory::piToDto');

        return $infos;
    }

    public function getAllPIs($rooms = null){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $rooms = $this->getValueFromRequest("rooms", $rooms);

        $dao = $this->getDao(new PrincipalInvestigator());
        $pis = $dao->getAllWith(DataRelationship::fromArray(PrincipalInvestigator::$ROOMS_RELATIONSHIP));
        /** TODO: Instead of $dao->getAll, we gather PIs which are either active or have rooms associated with them. **/
       /* $whereClauseGroup = new WhereClauseGroup( array( new WhereClause("is_active","=","1"), new WhereClause("key_id","IN","(SELECT principal_investigator_id FROM principal_investigator_room)") ) );
        $pis = $dao->getAllWhere($whereClauseGroup, "OR");*/

        EntityManager::with_entity_maps(PrincipalInvestigator::class, array(
            EntityMap::eager("getLabPersonnel"),
            EntityMap::eager("getRooms"),
            EntityMap::eager("getDepartments"),
            EntityMap::eager("getUser"),
            EntityMap::lazy("getInspections"),
            EntityMap::lazy("getPi_authorization"),
            EntityMap::lazy("getActiveParcels"),
            EntityMap::lazy("getCarboyUseCycles"),
            EntityMap::lazy("getPurchaseOrders"),
            EntityMap::lazy("getSolidsContainers"),
            EntityMap::lazy("getPickups"),
            EntityMap::lazy("getScintVialCollections"),
            EntityMap::lazy("getCurrentScintVialCollections"),
            EntityMap::lazy("getOpenInspections"),
            EntityMap::lazy("getQuarterly_inventories"),
            EntityMap::lazy("getVerifications"),
            EntityMap::lazy("getBuidling"),
            EntityMap::lazy("getWipeTests"),
            EntityMap::lazy("getCurrentVerifications")
        ));

        return $pis;
    }

    protected function getPIDetails( PrincipalInvestigator $pi ){
        $piBuildings = $pi->getBuildings();
        $deptDtos = DtoFactory::buildDtos($pi->getDepartments(), 'DtoFactory::departmentToDto');

        $dto = DtoFactory::buildDto($pi, array(
            'Departments' => $deptDtos,
            'User' => $pi->getUser(),
            'Name' => $pi->getName()
        ));

        return $dto;
    }

    public function getAllPIDetails(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $dao = $this->getDao(new PrincipalInvestigator());
        $pis = $dao->getAll();

        // Convert to DTOs
        $dtos = array();
        foreach($pis as $pi){
            $dto = $this->getPIDetails($pi);

            $dtos[] = $dto;
        }

        return $dtos;
    }

    public function buildPIDTO( PrincipalInvestigator $pi ){
        $piDao = new PrincipalInvestigatorDAO();
        $piBuildings = $piDao->getBuildings($pi->getKey_id());
        $buildingDtos = DtoFactory::buildDtos($piBuildings, 'DtoFactory::buildingToDto');
        $deptDtos = DtoFactory::buildDtos($pi->getDepartments(), 'DtoFactory::departmentToDto');
        $roomDtos = DtoFactory::buildDtos($pi->getRooms(), 'DtoFactory::roomToDto');
        $personnelDtos = DtoFactory::buildDtos($pi->getLabPersonnel(), 'DtoFactory::userToDto');

        return DtoFactory::buildDto($pi, array(
            'Name' => $pi->getName(),
            'Position' => $pi->getUser()->getPosition(),
            'Departments' => $deptDtos,
            'Buildings' => $buildingDtos,
            'Rooms' => $roomDtos,
            'LabPersonnel' => $personnelDtos
        ));
    }

    public function buildUserDTO( User $user ){
        // Roles to DTOs
        $roleDtos = DtoFactory::buildDtos($user->getRoles(), 'DtoFactory::roleToDto');

        // PI to DTO (or null)
        $pi = $user->getPrincipalInvestigator();
        $piDto = null;
        if( isset($pi) ){
            $piDto = $this->buildPIDTO($pi);
        }

        $supervisorDto = DtoFactory::piToDto($user->getSupervisor());

        // Inspector to DTO (or null)
        $inspectorDao = new InspectorDAO();

        // Pull Inspector from cache to avoid aggressive queries
        // If the inspector/user cache is not populated, inspector will be omitted
        $inspector = $inspectorDao->getInspectorByUserIdFromCache($user->getKey_id());
        $inspectorDto = DtoFactory::buildDto($inspector);

        $primaryDept = null;
        if( $user->getPrimary_department() != null ){
            $primaryDept = DtoFactory::departmentToDto($user->getPrimary_department());
        }

        // User to DTO, including only fields relevant to this endpoint
        return new GenericDto(array(
            'Class' => get_class($user),
            'Key_id' => $user->getKey_id(),
            'Is_active' => (bool) $user->getIs_active(),
            'Username' => $user->getUsername(),
            'Name' => $user->getName(),
            'First_name' => $user->getFirst_name(),
            'Last_name' => $user->getLast_name(),
            'Office_phone' => $user->getOffice_phone(),
            'Emergency_phone' => $user->getEmergency_phone(),
            'Lab_phone' => $user->getLab_phone(),
            'Email' => $user->getEmail(),
            'Position' => $user->getPosition(),
            'Roles' => $roleDtos,
            'Supervisor_id' => $user->getSupervisor_id(),
            'Supervisor' => $supervisorDto,
            'Primary_department' => $primaryDept,
            'PrincipalInvestigator' => $piDto,
            'Inspector' => $inspectorDto,
        ));
    }

    public function getUsersForPIHub(){
        return $this->getUsersForUserHub();
    }

    public function getUsersForUserHub(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $users = $this->getAllUsers();

        // TODO: Convert to DTOs
        /** We need:
            User
                Key_id
                First_name
                Last_name
                Office PHone
                Lab Phone
                Email
                Emergency Phone
                Roles
                Supervisor_id

                Principal Investigator
                    Key_id
                    Departments
                    Buildings

                Investigator
        */

        // Populate the Inspector/User cache to avoid querying for each user
        //   Most users are not inspectors, so this will prevent many unproductive SELECTs
        $inspectorDao = new InspectorDAO();
        $inspectorDao->getAll();

        /** Closure to convert a User to a GenericDto */
        $_mgr = $this;
        $fn_userToDto = function($u) use ($_mgr){
            return $_mgr->buildUserDTO($u);
        };

        // Filter nameless users
        $namedUsers = array_filter($users, function($u){
            if($u->getUsername() == null){
                Logger::getLogger(__CLASS__ . '.' . __FUNCTION__)->warn("User has no name: $u");
                return false;
            }

            return true;
        });

        $dtos = array_map($fn_userToDto, $namedUsers);
        return $dtos;
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

        EntityManager::with_entity_maps(PrincipalInvestigator::class, array(
            EntityMap::eager("getLabPersonnel"),
            EntityMap::eager("getRooms"),
            EntityMap::eager("getDepartments"),
            EntityMap::eager("getUser"),
            EntityMap::lazy("getInspections"),
            EntityMap::lazy("getPi_authorization"),
            EntityMap::lazy("getActiveParcels"),
            EntityMap::lazy("getCarboyUseCycles"),
            EntityMap::lazy("getPurchaseOrders"),
            EntityMap::lazy("getSolidsContainers"),
            EntityMap::lazy("getPickups"),
            EntityMap::lazy("getScintVialCollections"),
            EntityMap::lazy("getCurrentScintVialCollections"),
            EntityMap::lazy("getOpenInspections"),
            EntityMap::lazy("getQuarterly_inventories"),
            EntityMap::lazy("getVerifications"),
            EntityMap::lazy("getBuidling"),
            EntityMap::lazy("getCurrentVerifications"),
            EntityMap::lazy("getWipeTests")
        ));

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

    public function getAllRoomDetails(){

        EntityManager::with_entity_maps(Room::class, array(
            EntityMap::lazy("getHazards"),
            EntityMap::lazy("getBuilding"),
            EntityMap::eager('getBuilding_id'),
            EntityMap::lazy("getHazard_room_relations"),
            EntityMap::lazy("getHas_hazards"),
            EntityMap::lazy("getSolidsContainers")
        ));

        // Retrieve all rooms, ordered by Name
        $dao = new RoomDAO();
        $rooms = $dao->getAll("name");

        $roomDtos = array();
        foreach($rooms as $room){
            // Initialize present-hazard flags
            $room->getHazardTypesArePresent();

            // Retrieve all PIs, including Active
            $pis = $dao->getRelatedItemsById(
                $room->getKey_Id(),
                DataRelationship::fromArray(Room::$PIS_RELATIONSHIP),
                NULL, FALSE, TRUE);

            $piDtos = array();
            foreach($pis as $pi){
                $dto = $this->getPIDetails($pi);
                $piDtos[] = $dto;
            }

            $roomDto = DtoFactory::buildDto($room, array(
                'Name' => $room->getName(),
                'Building_id' => $room->getBuilding_id(),
                'Building_name' => $room->getBuilding_name(),
                'Purpose' => $room->getPurpose(),
                'PrincipalInvestigators' => $piDtos,

                'Bio_hazards_present' => $room->getBio_hazards_present(),
                'Chem_hazards_present' => $room->getChem_hazards_present(),
                'Rad_hazards_present' => $room->getRad_hazards_present(),
                'Lasers_present' => $room->getLasers_present(),
                'Xrays_present' => $room->getXrays_present(),
                'Recombinant_dna_present' => $room->getRecombinant_dna_present(),
                'Flammable_gas_present' => $room->getFlammable_gas_present(),
                'Toxic_gas_present' => $room->getToxic_gas_present(),
                'Corrosive_gas_present' => $room->getCorrosive_gas_present(),
                'Hf_present' => $room->getHf_present(),
                'Animal_facility' => $room->getAnimal_facility()
            ));

            $roomDtos[] = $roomDto;
        }

        return $roomDtos;
    }

    public function getAllRooms($allLazy = NULL){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $dao = new RoomDAO();

        $rooms = $dao->getAll("name");
        $allLazy = $this->getValueFromRequest('allLazy', $allLazy);

        // initialize an array of entityMap settings to assign to rooms, instructing them to lazy-load children
        // necessary because rooms by default eager-load buildings, and this would set up an infinite load loop between building->room->building->room...
        EntityManager::with_entity_maps(Room::class, array(
            EntityMap::lazy("getHazards"),
            EntityMap::lazy("getBuilding"),
            EntityMap::eager('getBuilding_id'),
            EntityMap::lazy("getHazard_room_relations"),
            EntityMap::lazy("getHas_hazards"),
            EntityMap::lazy("getSolidsContainers")
        ));

        if($allLazy == NULL){
            EntityManager::with_entity_maps(Room::class, array(
                EntityMap::eager("getPrincipalInvestigators")
            ));

        }else{
            EntityManager::with_entity_maps(Room::class, array(
                EntityMap::lazy("getPrincipalInvestigators")
            ));
        }

        EntityManager::with_entity_maps(PrincipalInvestigator::class, array(
            EntityMap::lazy("getLabPersonnel"),
            EntityMap::lazy("getRooms"),
            EntityMap::eager("getDepartments"),
            EntityMap::eager("getUser"),
            EntityMap::lazy("getInspections"),
            EntityMap::lazy("getPi_authorization"),
            EntityMap::lazy("getActiveParcels"),
            EntityMap::lazy("getCarboyUseCycles"),
            EntityMap::lazy("getPurchaseOrders"),
            EntityMap::lazy("getSolidsContainers"),
            EntityMap::lazy("getPickups"),
            EntityMap::lazy("getScintVialCollections"),
            EntityMap::lazy("getCurrentScintVialCollections"),
            EntityMap::lazy("getCurrentIsotopeInventories"),
            EntityMap::lazy("getOtherWasteTypes"),
            EntityMap::lazy("getOpenInspections"),
            EntityMap::lazy("getQuarterly_inventories"),
            EntityMap::lazy("getVerifications"),
            EntityMap::lazy("getBuidling"),
            EntityMap::lazy("getCurrentVerifications"),
            EntityMap::lazy("getWipeTests")
        ));

        EntityManager::with_entity_maps(User::class, array(
            EntityMap::lazy("getPrincipalInvestigator"),
            EntityMap::lazy("getInspector"),
            EntityMap::lazy("getSupervisor"),
            EntityMap::lazy("getRoles"),
            EntityMap::lazy("getPrimary_department")
        ));

        return $rooms;
    }

    public function getAllPrincipalInvestigatorRoomRelations(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $dao = $this->getDao(new PrincipalInvestigatorRoomRelation());

        return $dao->getAll();
    }

    public function getRoomsByPIId( $id = NULL ){
        //Get responses for Inspection
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $piId = $this->getValueFromRequest('piId', $id);

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

        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $LOG->trace("getting room $id");

        if( $id !== NULL ){
            $dao = new RoomDAO();
            return $dao->getById($id);
        }
        else{
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    public function getPIsByRoomId( $id = NULL ){
        $id = $this->getValueFromRequest('id', $id);

        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $LOG->trace('getting room');

        if( $id !== NULL ){
            $dao = new RoomDAO();
            $room =  $dao->getById($id);
            return $room->getPrincipalInvestigators();
        }
        else{
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    public function getPIsByClassInstance( $decodedObject = NULL ){
     $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $decodedObject = $this->convertInputJson();

        if( $decodedObject === NULL ){
            return new ActionError('Error converting input stream to RelationshipDto');
        }
        else if( $decodedObject instanceof ActionError ){
            return $decodedObject;
        }


        $pis = $decodedObject->getPrincipalInvestigators();

        EntityManager::with_entity_maps(PrincipalInvestigator::class, array(
            EntityMap::lazy("getLabPersonnel"),
            EntityMap::lazy("getRooms"),
            EntityMap::lazy("getDepartments"),
            EntityMap::eager("getUser"),
            EntityMap::lazy("getInspections"),
            EntityMap::lazy("getPi_authorization"),
            EntityMap::lazy("getActiveParcels"),
            EntityMap::lazy("getCarboyUseCycles"),
            EntityMap::lazy("getPurchaseOrders"),
            EntityMap::lazy("getSolidsContainers"),
            EntityMap::lazy("getPickups"),
            EntityMap::lazy("getScintVialCollections"),
            EntityMap::lazy("getCurrentScintVialCollections"),
            EntityMap::lazy("getOpenInspections"),
            EntityMap::lazy("getQuarterly_inventories"),
            EntityMap::lazy("getVerifications"),
            EntityMap::lazy("getBuidling"),
            EntityMap::lazy("getCurrentVerifications"),
            EntityMap::lazy("getWipeTests")
        ));

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
            $decodedObject = $dao->save($decodedObject);
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

    private function validateRoomUnassignments($room, Array $removePiIds = null){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $LOG->debug("Validating room unassignment: $room");

        // Remove this room assignment
        // First, check if this room has any hazards in it
        $hazardRoomRelations = $room->getHazard_room_relations();

        if( empty($hazardRoomRelations) ){
            // No hazards in this room; nothing more to check
            $LOG->debug("Room has no hazards");
            return true;
        }
        else {
            // This room has hazards assigned to it
            $LOG->debug("Room has hazard assignments");

            // Map relations to their PI IDs
            $piAssignments = array_unique(
                array_map(function($rel){
                    return $rel->getPrincipal_investigator_id();
                }, $hazardRoomRelations)
            );

            // Verify that the specified PIs have no hazards in this room
            if( isset($removePiIds) && !empty($removePiIds) ) {
                $LOG->debug("Checking hazard assignments for PIs: " . implode(', ', $removePiIds));

                $piAssignments = array_filter($piAssignments, function($assignedPiId) use ($removePiIds){
                    return in_array($assignedPiId, $removePiIds);
                });

                if( empty($piAssignments) ){
                    // The specified PIs have no hazards assigned to this room
                    $LOG->debug("PIs have no assignments in room: " . implode(', ', $removePiIds));
                    return true;
                }
            }

            $LOG->error("Cannot remove Room assignment: PIs (" . implode(', ', $piAssignments) . ") have Hazards assigned to $room");
            return false;
        }
    }

    public function savePIRoomRelation($PIId = NULL,$roomId = NULL,$add= NULL){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        if($PIId == NULL && $roomId == NULL && $add == NULL){
            // Attempt to decode input
            $decodedObject = $this->convertInputJson();

            if( $decodedObject === NULL ){
                return new ActionError('Error converting input stream to RelationshipDto');
            }
            else if( $decodedObject instanceof ActionError ){
                return $decodedObject;
            }

            // Extract required details from decoded input
            $PIId = $decodedObject->getMaster_id();
            $roomId = $decodedObject->getRelation_id();
            $add = $decodedObject->getAdd();
        }
        else{
            $decodedObject = new RelationshipDto();
            $decodedObject->setMaster_id($PIId);
            $decodedObject->setRelation_id($roomId);
            $decodedObject->setAdd($add);
        }

        if( $PIId !== NULL && $roomId !== NULL && $add !== null ){
            $LOG->info( ($add ? 'Add' : 'Remove') . " PI #$PIId from Room #$roomId");

            // Get a DAO
            $dao = $this->getDao(new PrincipalInvestigator());

            // Look up the room
            $room = $this->getRoomById($roomId);
            if( !isset($room) ){
                return new ActionError("No such room $roomId", 404);
            }

            if( !$add ) {
                // First, validate the unassignment
                if( !$this->validateRoomUnassignments($room, array($PIId)) ){
                    $LOG->error("Cannot remove Room assignment: PI #$PIId has Hazards assigned to room #$roomId");
                    return new ActionError("PI $PIId has Hazards assigned to room $roomId", 200);
                }

                // Remove this room assignment
                $LOG->debug("PI $PIId Has no hazards assigned to room $roomId. Unassigning...");
                // PI has no hazards assigned to this room
                // Remove the assignment
                $dao->removeRelatedItems($roomId, $PIId,
                    DataRelationship::fromArray(PrincipalInvestigator::$ROOMS_RELATIONSHIP));
            }
            else{
                $LOG->debug("Assigning PI $PIId to room $roomId...");
                // Add the room to this PI
                $dao->addRelatedItems($roomId, $PIId,
                    DataRelationship::fromArray(PrincipalInvestigator::$ROOMS_RELATIONSHIP));
            }

            EntityManager::with_entity_maps(Room::class, array(
                EntityMap::eager("getPrincipalInvestigators"),
                EntityMap::lazy("getHazards"),
                EntityMap::lazy("getHazard_room_relations"),
                EntityMap::eager("getHas_hazards"),
                EntityMap::eager("getBuilding"),
                EntityMap::lazy("getSolidsContainers")
            ));

            return $room;
        }
        else {
            //error
            return new ActionError("Missing proper parameters (should be masterId int, relationId int, add boolean)");
        }
    }

    public function savePIContactRelation($PIId = NULL,$contactId = NULL,$add= NULL){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
                            $LOG->info("Saving $pi");
                            if(!$this->savePI($pi))
                                return new ActionError('The PI record was not saved');
                        }

                        //add Inspector record if role is inspector
                        if($roleToAdd->getName() == 'Safety Inspector' || $roleToAdd->getName() == 'Radiation Inspector'){
                            $LOG->debug('trying to save inspector');
                            //if the user already has an Inspector, get that Inspector
                            if($user->getInspector() != NULL){
                                $inspector = $user->getInspector();
                            }else{
                                $inspector = new Inspector();
                                $inspector->setUser_id($userID);
                            }

                            $LOG->info("Saving $inspector");
                            if(!$this->saveInspector($inspector))
                                return new ActionError('The inspector record was not saved');
                        }

                        //All Lab Contacts are also Lab Personnel, so make sure Lab Contacts have that role as well
                        if($roleToAdd->getName() == 'Lab Contact'){
                            $addContact = true;
                            foreach($roles as $role){
                                if($role->getName() == 'Lab Personnel')
                                    $addContact = false;
                            }

                            if($addContact == true){
                                $allRoles = $this->getAllRoles();
                                foreach($allRoles as $role){
                                    if($role->getName() == "Lab Personnel"){
                                        $labPersonnelKeyid = $role->getKey_id();
                                        break;
                                    }
                                }

                                $LOG->info("Adding 'Lab Personnel' role to $userID");
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

    public function getPrincipalInvestigatorOrSupervisorForUser( User $user ){
        if( $user->getSupervisor_id() != null ){
            return $user->getSupervisor();
        }
        else {
            return $user->getPrincipalInvestigator();
        }
    }

    //Get a room dto duple
    public function getRoomDtoByRoomId( $id = NULL, $roomName = null, $containsHazard = null, $isAllowed = null ) {
        $id = $this->getValueFromRequest('id', $id);

        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $dao = $this->getDao(new Department());

        return $dao->getAll();
    }

    public function getAllDepartmentsWithCounts(){
        $dao = $this->getDao(new Department());
        $allDepartments = $dao->getAll();

        $deptCampusData = array();
        foreach($allDepartments as $dept){
            $deptInfo = new DepartmentDto($dept);

            // Get campus info for this department
            $campuses = $dao->getCampusCountsForDepartment($dept->getKey_id());

            // If there is no info; add an empty descriptor
            if( count($campuses) == 0 ){
                $empty = new DepartmentCampusInfoDto();
                $empty->setPi_count(0);
                $empty->setRoom_count(0);
                $campuses[] = $empty;
            }

            $deptInfo->setCampuses( $campuses );

            $deptCampusData[] = $deptInfo;
        }

        return $deptCampusData;
    }


    public function getAllActiveDepartments(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $skipRooms = $this->getValueFromRequest('skipRooms', $skipRooms);
        $skipPis = $this->getValueFromRequest('skipPis', $skipPis);

        $dao = $this->getDao(new Building());

        // get all buildings
        $buildings = $dao->getAll();

        if($skipRooms == NULL){
            // initialize an array of entityMap settings to assign to rooms, instructing them to lazy-load children
            // necessary because rooms by default eager-load buildings, and this would set up an infinite load loop between building->room->building->room...
            $roomMaps = array();

            if($skipPis != null){
                $roomMaps[] = EntityMap::eager("getPrincipalInvestigators");
            }else{
                $roomMaps[] = EntityMap::lazy("getPrincipalInvestigators");
            }

            $roomMaps[] = EntityMap::lazy("getHazards");
            $roomMaps[] = EntityMap::lazy("getHazard_room_relations");
            $roomMaps[] = EntityMap::lazy("getHas_hazards");
            $roomMaps[] = EntityMap::lazy("getBuilding");

            EntityManager::with_entity_maps(Room::class, $roomMaps);
            EntityManager::with_entity_maps(Building::class, array(EntityMap::eager("getRooms")));
        }

        return $buildings;

    }

    public function getAllCampuses(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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

            EntityManager::with_entity_maps(Room::class, array(
                EntityMap::lazy("getHazards"),
                EntityMap::lazy("getHazard_room_relations"),
                EntityMap::lazy("getHas_hazards"),
                EntityMap::lazy("getBuilding")
            ));

            return $building->getRooms();
        }
        else{
            return new ActionError("No request parameter 'id' was provided");
        }
    }

    public function initiateInspection($inspectionId = NULL,$piId = NULL,$inspectorIds= NULL,$rad = NULL, $roomIds=null){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $inspectionId = $this->getValueFromRequest('inspectionId', $inspectionId);
        $piId = $this->getValueFromRequest('piId', $piId);
        $inspectorIds = $this->getValueFromRequest('inspectorIds', $inspectorIds);
        $rad = $this->getValueFromRequest('rad', $rad);
        $roomIds = $this->getValueFromRequest('roomIds', $roomIds);

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
                $LOG->trace("Inspection Year: $year");
            }

            if($inspection->getSchedule_month() == null){
                $month = date('m');
                $inspection->setSchedule_month($month);
                $LOG->trace("Inspection Month: $month");
            }

            $inspection->setPrincipal_investigator_id($piId);

            //if($inspection->getDate_started() == null)$inspection->setDate_started(date("Y-m-d H:i:s"));
            // Save (or update) the inspection
            $dao->save($inspection);

            // Remove previous rooms and add the default rooms for this PI.
            $LOG->trace("Update inspection rooms");
            if($roomIds){
                $oldRooms = $inspection->getRooms();
                if (!empty($oldRooms)) {
                    // removeo the old rooms
                    foreach ($oldRooms as $oldRoom) {
                        $dao->removeRelatedItems($oldRoom->getKey_id(),$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$ROOMS_RELATIONSHIP));
                    }
                }
                // add the default rooms for this PI
                foreach ($roomIds as $id) {
                    $dao->addRelatedItems($id,$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$ROOMS_RELATIONSHIP));
                }
            }

            // Remove previous inspectors and add the submitted inspectors.
            $LOG->trace("Update inspection inspectors");
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

            // Remove previous lab contacts
            $LOG->trace("Update inspection Lab Contacts");
            $oldContacts = $inspection->getLabPersonnel();
            if( !empty($oldContacts) ){
                foreach($oldContacts as $contact){
                    $dao->removeRelatedItems(
                        $contact->getKey_id(),
                        $inspection->getKey_id(),
                        DataRelationship::fromArray(Inspection::$INSPECTION_LAB_PERSONNEL_RELATIONSHIP));
                }
            }

            // add contacts from Principal Investigator
            $pi = $inspection->getPrincipalInvestigator();
            foreach($pi->getLabPersonnel() as $contact){
                // Ensure that this user is a 'Lab Contact'
                $hasContactRole = count(array_filter( $contact->getRoles(), function($role){
                    return $role->getName() == 'Lab Contact';
                }));

                if( $hasContactRole ){
                    $LOG->debug("Add Lab Contact $contact to $inspection");
                    $dao->addRelatedItems(
                        $contact->getKey_id(),
                        $inspection->getKey_id(),
                        DataRelationship::fromArray(Inspection::$INSPECTION_LAB_PERSONNEL_RELATIONSHIP ));
                }
                else {
                    $LOG->trace("Personnel $contact is not a lab contact");
                }
            }

        } else {
            //error
            return new ActionError("Missing proper parameters (should be inspectionId (nullable int), piId int, inspectorIds (one or more ints))");
        }

        EntityManager::with_entity_maps(Inspection::class, array(
            EntityMap::eager("getInspectors"),
            EntityMap::eager("getRooms"),
            EntityMap::lazy("getResponses"),
            EntityMap::eager("getPrincipalInvestigator"),
            EntityMap::lazy("getChecklists"),
            EntityMap::eager("getStatus")
        ));

        $LOG->debug("Inspection initiated: $inspection");
        return $inspection;
    }

    //Appropriately sets relationships for an inspection if an inspector is not inspecting all of a PI's rooms
    public function resetInspectionRooms($inspectionId = NULL, $roomIds = null){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $inspectionId = $inspectionDao->getKey_id();

        foreach($inspectionDao->getRooms() as $room){
            $this->saveInspectionRoomRelation( $room->getKey_id(), $inspectionId, false );
        }

    }

    public function saveInspectionRoomRelation($roomId = NULL,$inspectionId = NULL,$add= NULL){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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

            // Retrieve existing Inspection, if any
            if( $decodedObject->hasPrimaryKeyValue() ){
                $beforeSaved = $dao->getById($decodedObject->getKey_id());
            }

            // Save the Inspection
            $inspection = $dao->save($decodedObject);
            HooksManager::hook('after_inspection_saved', array($inspection, $beforeSaved));

            return $inspection;
        }
    }

    public function scheduleInspection(){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $decodedObject = $this->convertInputJson();
        $inspectionDao = $this->getDao( new Inspection() );
        if( $decodedObject->getInspections()->getKey_id() != NULL ){
            $LOG->info("Updating Scheduled Inspection");
            $inspection = $inspectionDao->getById( $decodedObject->getInspections()->getKey_id() );
            if( $LOG->isTraceEnabled() ){
                $LOG->trace($inspection);
            }
        }else{
            $LOG->info("Scheduling New Inspection");
            $inspection = new Inspection();
        }

        $inspection->setSchedule_month( $decodedObject->getInspections()->getSchedule_month() );
        if($decodedObject->getInspections()->getSchedule_year())$inspection->setSchedule_year( $decodedObject->getInspections()->getSchedule_year() );

        $inspection->setPrincipal_investigator_id($decodedObject->getPi_key_id());
        $inspection = $inspectionDao->save( $inspection );

        // remove old lab contact relationship
        if($inspection->getLabPersonnel() != null){
            $LOG->trace("Remove old lab personnel from $inspection");
            foreach($inspection->getLabPersonnel() as $contact){
                $inspectionDao->removeRelatedItems(
                    $contact->getKey_id(),
                    $inspection->getKey_id(),
                    DataRelationship::fromArray(Inspection::$INSPECTION_LAB_PERSONNEL_RELATIONSHIP));
            }
        }

        // Save Personnel relationships
        $LOG->debug("Save lab personnel for $inspection");
        $pi = $inspection->getPrincipalInvestigator();
        foreach($pi->getLabPersonnel() as $contact){
            // Ensure that this user is a 'Lab Contact'
            $hasContactRole = count(array_filter( $contact->getRoles(), function($role){
                return $role->getName() == 'Lab Contact';
            }));

            if( $hasContactRole ){
                $LOG->debug("Add Lab Contact $contact to $inspection");
                $inspectionDao->addRelatedItems(
                    $contact->getKey_id(),
                    $inspection->getKey_id(),
                    DataRelationship::fromArray(Inspection::$INSPECTION_LAB_PERSONNEL_RELATIONSHIP ));
            }
            else {
                $LOG->trace("Personnel $contact is not a lab contact");
            }
        }

        if($inspection->getRooms() != null){
            $LOG->debug("Remove old Rooms for $inspection");
            foreach($inspection->getRooms() as $room){
                //remove old room relationship
                $this->saveInspectionRoomRelation($room->getKey_id(),$inspection->getKey_id(),false);
            }
        }

        if( $decodedObject->getBuilding_rooms() != null ){
            $LOG->debug("Save new Rooms for $inspection");
            foreach($decodedObject->getBuilding_rooms() as $room){
                //save room relationships
                $this->saveInspectionRoomRelation($room["Key_id"],$inspection->getKey_id(),true);
            }
        }

        if($inspection->getInspectors() != null){
            $LOG->debug("Remove old Inspectors for $inspection");
            foreach($inspection->getInspectors() as $inspector){
                //remove old inspector relationships
                $LOG->debug("Remove $inspector");
                $inspectionDao->removeRelatedItems($inspector->getKey_id(),$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$INSPECTORS_RELATIONSHIP));
            }
            $inspection->setInspectors(null);
        }

        if( $decodedObject->getInspections()->getInspectors() != null ){
            $LOG->debug("Save new Inspectors for $inspection");
            foreach($decodedObject->getInspections()->getInspectors() as $inspector){
                $LOG->debug("Link inspector: " . $inspector["Key_id"]);
                //save inspector relationships
                $inspectionDao->addRelatedItems($inspector["Key_id"],$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$INSPECTORS_RELATIONSHIP ));
            }
        }

        //When an Inspection is scheduled, labs should complete a verification before the inspection takes place
        if($inspection->getSchedule_month() != null){
            $LOG->debug("Process lab Verifications for $inspection");

            //first, see if this verification has already been created
            $whereClauseGroup = new WhereClauseGroup(
                array(new WhereClause("inspection_id","=",$inspection->getKey_id()))
            );
            $verificationDao =  new GenericDAO(new Verification());

            $verifications = $verificationDao->getAllWhere($whereClauseGroup);

            $LOG->info( count($verifications) . " Existing Verifications for $inspection");
            if( $LOG->isTraceEnabled()) {
                $LOG->trace($verifications);
            }

            if(!empty($verifications)){
                $verification = $verifications[0];
                $LOG->info("Updating Verification $verification for $inspection");
            }else{
                $verification = new Verification();
                $LOG->info("Create new Verification for $inspection");
            }

            //the verification is due one month before the first day of the scheduled month of the inspection
            $inspectionDate = date_create($inspection->getSchedule_year() . "-" . $inspection->getSchedule_month() );
            $verification->setDue_date($inspectionDate->format("Y-m-d H:i:s"));
            $verification->setInspection_id($inspection->getKey_id());
            $verification->setPrincipal_investigator_id($inspection->getPrincipal_investigator_id());
            $verificationDao->save($verification);
        }

        EntityManager::with_entity_maps(Inspection::class, array(
            EntityMap::eager("getInspectors"),
            EntityMap::eager("getRooms"),
            EntityMap::eager("getResponses"),
            EntityMap::eager("getDeficiency_selections"),
            EntityMap::eager("getPrincipalInvestigator"),
            EntityMap::eager("getStatus"),
            EntityMap::lazy("getChecklists")
        ));

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

    public function submitCAP( $id = NULL ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $id = $this->getValueFromRequest('id', $id);
        if( $id == NULL ){
            return new ActionError("No CAP to submit", 404);
        }
        else{
            $dao = $this->getDao(new Inspection());

            $inspection = $dao->getById($id);
            if( !isset($inspection) ){
                return new ActionError("No such inspection $id", 404);
            }

            // Only thing that needs to change is to track the user & date of submission...
            $inspection->setCap_submitter_id( $this->getCurrentUser()->getKey_id() );
            $inspection->setCap_submitted_date( date("Y-m-d H:i:s") );

            $LOG->info("Submitting CAP for $inspection: user:" . $inspection->getCap_submitter_id() . " date:" . $inspection->getCap_submitted_date());

            // Save the Inspection
            $inspection = $dao->save($inspection);

            //RSMS-827
            HooksManager::hook('after_cap_submitted', $inspection);

            return $inspection;
        }
    }

    public function approveCAP( $id = NULL ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $id = $this->getValueFromRequest('id', $id);
        if( $id == NULL ){
            return new ActionError("No CAP to approve", 404);
        }

        $dao = $this->getDao(new Inspection());

        $inspection = $dao->getById($id);
        if( !isset($inspection) ){
            return new ActionError("No such inspection $id", 404);
        }

        $now = date("Y-m-d H:i:s");
        $inspection->setDate_closed( $now );
        $inspection->setCap_approver_id( $this->getCurrentUser()->getKey_id() );

        $LOG->info("Approving CAP for $inspection: approver:" . $inspection->getCap_approver_id() . " date:" . $inspection->getDate_closed());
        $inspection = $dao->save($inspection);

        HooksManager::hook('after_cap_approved', $inspection);

        return $inspection;
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $roomIdsCsv = $this->getValueFromRequest('roomIds', $roomIds);

        if( $roomIdsCsv !== NULL ){
            $LOG->debug("Retrieving Hazard-Room mappings for Rooms: " . implode(', ', $roomIdsCsv));
            $LOG->debug('Identified ' . count($roomIdsCsv) . ' Rooms');
            //Get all hazards
            if($hazard != null){
              $allHazards = $hazard;
            }else{
              $allHazards = $this->getAllHazardsAsTree();
            }

            EntityManager::with_entity_maps(Hazard::class, array(
                EntityMap::lazy("getSubHazards"),
                EntityMap::eager("getActiveSubHazards"),
                EntityMap::lazy("getChecklist"),
                EntityMap::lazy("getRooms"),
                EntityMap::eager("getInspectionRooms"),
                EntityMap::eager("getHasChildren"),
                EntityMap::lazy("getParentIds"),
                EntityMap::lazy("getPrincipalInvestigators")
            ));

            $rooms = array();
            $roomDao = new RoomDAO();

            // Create an array of Room Objects
            foreach($roomIdsCsv as $roomId) {
                array_push($rooms,$roomDao->getById($roomId));
            }
            $subs = $allHazards->getActiveSubHazards();

            // filter by room
            foreach ($subs as $subhazard){
                if($subhazard->getKey_id() != 9999){
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $LOG->debug($hazard->getName());
        $entityMaps = array();

        $hazard->setInspectionRooms($rooms);
        $hazard->filterRooms();

        if(stristr($hazard->getName(), 'general hazard') || $generalHazard){
                $generalHazard = true;
                if($hazard->getIsPresent() != true){
                    $this->saveHazardRoomRelations( $hazard, $rooms );
                }
        }

        if($hazard->getIsPresent() || $hazard->getParent_hazard_id() != 1000){
            $entityMaps[] = EntityMap::eager("getActiveSubHazards");
        }else{
            $entityMaps[] = EntityMap::lazy("getActiveSubHazards");
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
                //$entityMaps[] = EntityMap::eager("getActiveSubHazards");
                $entityMaps = array();
                $entityMaps[] = EntityMap::eager("getActiveSubHazards");
                $subhazard->setEntityMaps($entityMaps);

                $this->filterHazards($subhazard, $rooms, $generalHazard);
            }else{
                $entityMaps = array();
                $entityMaps[] = EntityMap::lazy("getActiveSubHazards");
                $subhazard->setEntityMaps($entityMaps);
            }

        }
    }

    //UTILITY public function FOR getHazardRoomMappingsAsTree
    public function getHazardRoomMappings($hazard, $rooms, $searchRoomIds, $parentIds = null){
        $searchRoomIds = $searchRoomIds;
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $roomId = $this->getValueFromRequest('roomId', $roomId);
        $subHazards = $this->getValueFromRequest('subHazards', $subHazards);
        $LOG->debug("subHazards is $subHazards, roomId is $roomId");


        if( $roomId !== NULL ){

            $dao = new RoomDAO();

            //get Room
            $room = $dao->getById($roomId);

            //get hazards
            $hazards = $room->getHazards();

            // if subhazards is false, change all hazard subentities to lazy loading
            if ($subHazards == "false"){
                EntityManager::with_entity_maps(Hazard::class, array(
                    EntityMap::lazy("getSubHazards"),
                    EntityMap::lazy("getActiveSubHazards"),
                    EntityMap::lazy("getChecklist"),
                    EntityMap::lazy("getRooms"),
                    EntityMap::lazy("getInspectionRooms"),
                    EntityMap::eager("getParentIds"),
                    EntityMap::lazy("getHasChildren"),
                    EntityMap::lazy("getPrincipalInvestigators")
                ));

                foreach ($hazards as &$hazard){
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

    public function getHazardsInRoomByPi( $roomId = NULL, $piId = NULL, $subHazards = NULL ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
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
            EntityManager::with_entity_maps(Hazard::class, array(
                EntityMap::lazy("getSubHazards"),
                EntityMap::lazy("getActiveSubHazards"),
                EntityMap::lazy("getChecklist"),
                EntityMap::lazy("getRooms"),
                EntityMap::lazy("getInspectionRooms"),
                EntityMap::eager("getParentIds"),
                EntityMap::lazy("getHasChildren"),
                EntityMap::lazy("getPrincipalInvestigators")
            ));

            $hazards = array();
            $hazardIds = array();

            foreach ($piHazRooms as $piHazardRoom){
                if(!in_array($piHazardRoom->getHazard_id(), $hazardIds)){
                    $hazard = $piHazardRoom->getHazard();
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $entityMaps[] = EntityMap::lazy("getHazards");
        $entityMaps[] = EntityMap::eager("getHazardRoomRelations");

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

            EntityManager::with_entity_maps(Hazard::class, array(
                EntityMap::lazy("getSubHazards"),
                EntityMap::eager("getActiveSubHazards"),
                EntityMap::lazy("getChecklist"),
                EntityMap::lazy("getRooms"),
                EntityMap::eager("getInspectionRooms"),
                EntityMap::eager("getHasChildren"),
                EntityMap::lazy("getParentIds"),
                EntityMap::lazy("getPrincipalInvestigators")
            ));

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

                    $subEntityMaps = array(
                        EntityMap::lazy("getActiveSubHazards")
                    );

                    foreach($hazard->getActiveSubHazards() as $subhazard){
                        $subhazard->setInspectionRooms($inspectionRooms);

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        if($roomId == null)$roomId = $this->getValueFromRequest('roomId', $roomId);
        if($hazardId == null)$hazardId = $this->getValueFromRequest('hazardId', $hazardId);
        if($add == null)$add = $this->getValueFromRequest('add', $add);
        if($recurse == null)$recurse = $this->getValueFromRequest('recurse', $recurse);

        if( $roomId !== NULL && $hazardId !== NULL && $add !== null ){
            // Get this room
            $dao = new RoomDAO();
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

            EntityManager::with_entity_maps(Hazard::class, array(
                EntityMap::lazy("getSubHazards"),
                EntityMap::eager("getActiveSubHazards"),
                EntityMap::lazy("getChecklist"),
                EntityMap::lazy("getRooms"),
                EntityMap::eager("getInspectionRooms"),
                EntityMap::eager("getHasChildren"),
                EntityMap::lazy("getParentIds"),
                EntityMap::lazy("getPrincipalInvestigators")
            ));

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
                if( !stristr( $decodedObject->getAnswer(),'no' ) ){
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
            $LOG->debug($decodedObject);
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

            // Retrieve a fresh copy of the modified object
            //  by first Evicting the cached entry
            GenericDAO::$_ENTITY_CACHE->evict($ds);
            $selection = $dao->getById($ds->getKey_id());

            EntityManager::with_entity_maps(DeficiencySelection::class, array(
                EntityMap::eager("getRooms"),
                EntityMap::lazy("getCorrectiveActions"),
                EntityMap::lazy("getResponse"),
                EntityMap::lazy("getDeficiency")
            ));

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
            $correctiveActions = $decodedObject->getCorrectiveActions();
            $action = end($correctiveActions);
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
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

            $LOG->debug($inspection->getPrincipal_investigator_id());
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ){
            $dao = $this->getDao(new Inspection());

            //get inspection
            $inspection = $dao->getById($id);

            if (empty($inspection) ) {return new ActionError("No Response with id $id exists");}

            EntityManager::with_entity_maps(Inspection::class, array(
                EntityMap::eager("getInspectors"),
                EntityMap::eager("getRooms"),
                EntityMap::lazy("getResponses"),
                EntityMap::eager("getPrincipalInvestigator"),
                EntityMap::eager("getChecklists")
            ));

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

    public function getInspectionReportEmail( $id = NULL ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $id = $this->getValueFromRequest('id', $id);

        if( $id !== NULL ){
            // get inspection
            $inspectionDao = new GenericDAO(new Inspection());
            $inspection = $inspectionDao->getById($id);

            $LOG->debug("Retrieve inspection report email for $inspection");

            //TODO: compute status details for email template identification instead of passing from view
            $inspectionState = $this->convertInputJson();

            // Get Inspection Email Template for requested inspection
            // Identify message type based on inspection state
            $messageType = InspectionEmailMessage_Processor::getMessageTypeName($inspectionState);
            $LOG->debug("Build preview for message type: $messageType");

            // Create a message to use to look up Template(s)
            $message = new Message();
            $message->setModule( LabInspectionModule::$NAME );
            $message->setMessage_type( $messageType );

            $messenger = new Messaging_ActionManager();
            $previews = $messenger->previewMessage( $message, array($inspection, $inspectionState) );

            if( count($previews) < 1 ){
                return new ActionError("Unable to preview inspection", 404);
            }

            // Should only be one, but just read the first
            $preview = $previews[0];

            $context = new InspectionReportMessageContext($id, $inspectionState, $preview);

            return $context;
        }
        else{
            //error
            return new ActionError("No request parameter 'id' was provided", 404);
        }

        return true;
    }

    public function getInspectionsByPIId( $piId = NULL ){
        //Get responses for Inspection
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $id = $this->getValueFromRequest('id', $id);
        $report = $this->getValueFromRequest('report', $report);

        if( $id === NULL ){
            //error
            return new ActionError("No request parameter 'id' was provided", 400);
        }

        $dao = $this->getDao(new Inspection());

        //get inspection
        $inspection = $dao->getById($id);

        if( !isset($inspection) || ($inspection instanceof ActionError) ){
            return new ActionError("No such Inspection $id", 404);
        }

        // check if this is an inspection we're just starting
        if( $inspection->getDate_started() == NULL ) {
            $inspection->setDate_started(date("Y-m-d H:i:s"));
            $dao->save($inspection);
        }

        // Force 'report' mode if requested OR if inspection is archived
        //   * Archived reports should never be modified!
        $REPORT_MODE = $report ?? $inspection->getIsArchived();

        // Log a warning if someone attempted to view an archived inspection in non-report mode
        if( $report == null && $REPORT_MODE){
            $LOG->warn("Requested non-report mode for Archived inspection $inspection. Report-mode will be forced.");
        }

        // Remove previous checklists (if any) and recalculate the required checklist.
        $oldChecklists = $inspection->getChecklists();

        // Calculate the Checklists needed according to hazards currently present in the rooms covered by this inspection
        if(!$REPORT_MODE){
            $LOG->info("Recalculating list of Checklists for $inspection");

            if (!empty($oldChecklists)) {
                $LOG->debug("Removing all old Checklists from $inspection");
                // remove the old checklists
                foreach ($oldChecklists as $oldChecklist) {
                    $dao->removeRelatedItems($oldChecklist->getKey_id(),
                                                $inspection->getKey_id(),
                                                DataRelationship::fromArray(Inspection::$CHECKLISTS_RELATIONSHIP));
                }
            }

            $LOG->debug("Generating new list of Checklists for $inspection");
            $checklists = $this->getChecklistsForInspection($inspection->getKey_id());

            // add the checklists to this inspection
            $LOG->debug("Assigning new Checklists to $inspection");
            foreach ($checklists as $checklist){
                $LOG->trace("Add $checklist to $inspection");

                $dao->addRelatedItems($checklist->getKey_id(),$inspection->getKey_id(),DataRelationship::fromArray(Inspection::$CHECKLISTS_RELATIONSHIP));
                $checklist->setInspectionId($inspection->getKey_id());
                $checklist->setRooms($inspection->getRooms());
                //filter the rooms, but only for hazards that aren't in the General branch, which should always have all the rooms for an inspection
                //9999 is the key_id for General Hazard
                if($checklist->getMaster_id() != 9999){
                    $checklist->filterRooms($inspection->getPrincipal_investigator_id());
                }
            }
        }
        //if we are loading a report instead of a list of checklists for an inspection, we don't update the list of checklists
        else {
            $checklists = $oldChecklists;
        }

        // Build array of Hazard IDs for each checklist
        $hazardIds = array_map(
            function($checklist){
                return $checklist->getHazard_id();
            },
            $checklists
        );

        //recurse down hazard tree.  look in checklists array for each hazard.  if checklist is found, push it into ordered array.
        $orderedChecklists = array();
        $orderedChecklists = $this->recurseHazardTreeForChecklists($checklists, $hazardIds, $orderedChecklists, $this->getHazardById(10000));
        $inspection->setChecklists( $orderedChecklists );

        //make sure we get the right rooms for our branch level checklists
        //ids of the branch level hazards, excluding general, which is always in every room
        $realBranchIds = array(1,10009,10010);
        $neededRoomIds = array();
        $neededRooms   = array();
        foreach($orderedChecklists as $list){

            if(in_array($list->getHazard_id(), $realBranchIds)){
                //if(!in_array(,$neededRoomIds))
                //evaluate what rooms we need.  any room a checklist for a child of this one has should be pushed
                $childLists =  $this->getChildLists($list, $orderedChecklists);
                foreach($childLists as $childList){
                    $childInspectionRooms = $childList->getInspectionRooms();
                    if( !empty($childInspectionRooms) ){
                        foreach($childList->getInspectionRooms() as $room){
                            if(!in_array($room->getKey_id(), $neededRoomIds)){
                                array_push($neededRoomIds, $room->getKey_id());
                                array_push($neededRooms, $room);
                            }
                        }
                    }
                }
                $list->setInspectionRooms($neededRooms);
            }
        }

        EntityManager::with_entity_maps(Checklist::class, array(
            EntityMap::lazy("getHazard"),
            EntityMap::lazy("getRooms"),
            EntityMap::eager("getInspectionRooms"),
            EntityMap::eager("getQuestions")
        ));

        EntityManager::with_entity_maps(Inspection::class, array(
            EntityMap::eager("getInspectors"),
            EntityMap::eager("getRooms"),
            EntityMap::lazy("getResponses"),
            EntityMap::eager("getPrincipalInvestigator"),
            EntityMap::eager("getChecklists"),
            EntityMap::eager("getInspection_wipe_tests")
        ));

        return $inspection;
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
    	$LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $username = $this->getValueFromRequest('username', $username);

        if( ApplicationConfiguration::get('server.auth.providers.ldap', false) ){
            // LDAP is enabled
            $LOG->info("Lookup user '$username' via LDAP");

            $ldap = new LDAP();

            $fieldsToFind = array("cn","sn","givenName","mail");
            if ($ldapData = $ldap->GetAttr($username, $fieldsToFind)){
                $user = new User();
                $user->setFirst_name(ucfirst(strtolower($ldapData["givenName"])));
                $user->setLast_name(ucfirst(strtolower($ldapData["sn"])));
                $user->setEmail(strtolower($ldapData["mail"]));
                $user->setUsername($ldapData["cn"]);

                return $user;
            } else {
                return false;
            }
        }
        else{
            // LDAP is disabled; look up user just from our database
            $LOG->info("Lookup user '$username' in local database");
            $dao = new UserDAO();
            $user = $dao->getUserByUsername($username);

            if( !$user ){
                $LOG->info("No user '$username' found in local database");
                $user = new User();
                $user->setUsername($username);
            }

            return $user;
        }
    }

    public function sendInspectionEmail(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $context = $this->convertInputJson();
        if( $context === NULL ){
            return new ActionError('Error converting input stream');
        }
        else if( $context instanceof ActionError){
            return $context;
        }
        else {
            // RSMS-739: Instead of directly sending an email here, queue a Message
            $dao = new GenericDAO(new Inspection());
            $inspection = $dao->getById($context->getInspection_id());

            // TODO: VALIDATE CONTEXT?

            // Enqueue this message to be sent
            $messenger = new Messaging_ActionManager();
            $messageType = InspectionEmailMessage_Processor::getMessageTypeName($context->getInspectionState());
            $queued = $messenger->enqueueMessages( LabInspectionModule::$NAME, $messageType, array($context) );

            if( count($queued) > 0 ){
                // Successfully queued message

                // Trigger immediate proccessing of this queued message
                // TODO: Filter processing to just these messages?
                HooksManager::hook('after_inspection_report_message_queued', null);

                // Set Notification Date, if not already set
                if($inspection->getNotification_date() == null){
                    $inspection->setNotification_date(date("Y-m-d H:i:s"));
                    $dao->save($inspection);
                }
            }
            // Else the message is already enqueued, so nothing is needed

            return true;
        }
    }

    public function makeFancyNames(){
        $users = $this->getAllUsers();
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $hazards = $this->getHazardTreeNode(10000);
        foreach($hazards as $hazard){
            $this->setOrderIndicesForSubHazards( $hazard );
        }
        return $this->getAllHazards();
    }

    public function setOrderIndicesForSubHazards( $hazard = NULL ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        // read the Year value from the request.
        $year = $this->getValueFromRequest('year', $year);

        // If the year is null, choose the current year.
        if ($year == null){
            $year = $this->getCurrentYear();
        }

        // Call the database
        $LOG->info('getting schedule for ' . $year);

        // Get the needed inspections
        $dao = new InspectionDAO();
        $inspectionSchedules = $dao->getNeededInspectionsByYear($year);

        $LOG->debug('Retrieved ' . count($inspectionSchedules) . " inspections for $year schedule");

        // Now fill in some extra DTO details (rooms)
        $piDao = new PrincipalInvestigatorDAO();

        foreach ($inspectionSchedules as &$is){
            $LOG->trace("Processing $is...");
            if ($is->getInspection_id() !== null){
                // LOAD INSPECTION
                $inspection = $dao->getById($is->getInspection_id());

                // GET LIST OF INSPECTION'S ROOMS, AND FILTER THEM
                //  SO THAT ONLY ROOMS OF THIS INSPECTION'S BUILDING
                //  ARE PRESENT
                $filteredRooms = array();
                $rooms = $inspection->getRooms();
                foreach( $rooms as $room ){
                	if( $room->getBuilding_id() == $is->getBuilding_key_id() ){
                		array_push($filteredRooms, $room);
                    }
                }
                $is->setInspection_rooms( DtoFactory::buildDtos($filteredRooms, 'DtoFactory::roomToDto') );
                $is->setInspections($inspection);
            }

            // Now get the PI's Rooms which are in the Inspection's Building
            $pi_bldg_rooms = $piDao->getRoomsInBuilding($is->getPi_key_id(), $is->getBuilding_key_id());
            $is->setBuilding_rooms( DtoFactory::buildDtos($pi_bldg_rooms, 'DtoFactory::roomToDto') );
        }

        EntityManager::with_entity_maps(Inspection::class, array(
            EntityMap::eager("getInspectors"),
            EntityMap::lazy("getRooms"),
            EntityMap::lazy("getResponses"),
            EntityMap::lazy("getDeficiency_selections"),
            EntityMap::lazy("getPrincipalInvestigator"),
            EntityMap::lazy("getChecklists"),
            EntityMap::lazy("getCap_submitter_name"),
            EntityMap::lazy("getCap_approver_name"),
            EntityMap::lazy("getLabPersonnel"),
            EntityMap::eager("getStatus"),

            EntityMap::lazy("getCap_approver_id"),
            EntityMap::lazy("getCap_submitter_id"),
            EntityMap::lazy("getCreated_user_id"),
            EntityMap::lazy("getDate_created"),
            EntityMap::lazy("getDate_last_modified"),
            EntityMap::eager("getHasDeficiencies")
        ));

        EntityManager::with_entity_maps(Room::class, array(
            EntityMap::lazy("getPrincipalInvestigators"),
            EntityMap::lazy("getHazards"),
            EntityMap::lazy("getHazard_room_relations"),
            EntityMap::lazy("getHas_hazards"),
            EntityMap::lazy("getBuilding"),
            EntityMap::lazy("getSolidsContainers"),
            EntityMap::lazy("getHazardTypesArePresent")
        ));

        EntityManager::with_entity_maps(User::class, array(
            EntityMap::lazy("getPrimary_department")
        ));

        $LOG->info("Retrieved and populated $year inspection schedule");
        return $inspectionSchedules;
    }

    public function getInspectionsByYear($year = NULL){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        // read the Year value from the request.
        $year = $this->getValueFromRequest('year', $year);

        // If the year is null, choose the current year.
        if ($year == null){
            $year = $this->getCurrentYear();
        }

        $dao = new InspectionDAO();
        $inspections = $dao->getInspectionsByYear($year);

        EntityManager::with_entity_maps(Inspection::class, array(
            EntityMap::eager( "getInspectors" ),
            EntityMap::lazy( "getRooms" ),
            EntityMap::lazy( "getResponses" ),
            EntityMap::lazy( "getDeficiency_selections" ),
            EntityMap::lazy( "getPrincipalInvestigator" ),
            EntityMap::lazy( "getChecklists" ),
            EntityMap::eager( "getStatus" )
        ));

        return $inspections;

    }

    public function getAllLabLocations(){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $dao = $this->getDao(new Inspection());
        $rooms = $dao->getAllLocations();
        //return $rooms;
        $packedLocations = array();
        $skip = false;
        $previousRoomID = 0;
        foreach ($rooms as &$roomDTO){
            if ( $roomDTO->getRoom_id() !== NULL && $roomDTO->getPi_key_id() !== null && $previousRoomID !== $roomDTO->getRoom_id() ){
                    $roomDao = new RoomDAO();
                    $room = $roomDao->getById($roomDTO->getRoom_id());
                    $pis = $room->getPrincipalInvestigators();

                    $roomDTO->setPrincipal_investigators($pis);
                    $previousRoomID == $roomDTO->getRoom_id();
                }
        }

        EntityManager::with_entity_maps(PrincipalInvestigator::class, array(
            EntityMap::lazy("getLabPersonnel"),
            EntityMap::lazy("getRooms"),
            EntityMap::eager("getDepartments"),
            EntityMap::eager("getUser"),
            EntityMap::lazy("getInspections"),
            EntityMap::lazy("getPi_authorization"),
            EntityMap::lazy("getActiveParcels"),
            EntityMap::lazy("getCarboyUseCycles"),
            EntityMap::lazy("getPurchaseOrders"),
            EntityMap::lazy("getSolidsContainers"),
            EntityMap::lazy("getPickups"),
            EntityMap::lazy("getScintVialCollections"),
            EntityMap::lazy("getCurrentScintVialCollections"),
            EntityMap::lazy("getOpenInspections"),
            EntityMap::lazy("getQuarterly_inventories"),
            EntityMap::lazy("getVerifications"),
            EntityMap::lazy("getBuidling"),
            EntityMap::lazy("getCurrentVerifications"),
            EntityMap::lazy("getWipeTests")
        ));

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

        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
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
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $roomDao = new RoomDAO();
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
        return '';
    }

    public function getMyLab($id = null){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        if($id==null)$id = $this->getValueFromRequest('id', $id);
        if($id==null)return new ActionError('No request param "id" provided.');
        $principalInvestigator = $this->getPIById($id);

        if( isset($principalInvestigator) ){
            // Filter inspections by year based on current user role
            $inspections = $principalInvestigator->getInspections();

            $minYear = $this->sessionHasRoles(array("Admin"))
                ? 2017
                : 2018;

            foreach($inspections as $key => $inspection){
                if( $inspection->getIsArchived() ){
                    $closedYear = date_create($inspection->getDate_closed())->format("Y");

                    if( $closedYear < $minYear ){
                        $LOG->debug("Omit $inspection (closed $closedYear) for MyLab");
                        unset($inspections[$key]);
                    }
                }
            }

            $principalInvestigator->setInspections($inspections);
        }

        EntityManager::with_entity_maps(PrincipalInvestigator::class, array(
            EntityMap::lazy("getLabPersonnel"),
            EntityMap::lazy("getRooms"),
            EntityMap::lazy("getDepartments"),
            EntityMap::eager("getUser"),
            EntityMap::eager("getInspections"),
            EntityMap::lazy("getPi_authorization"),
            EntityMap::lazy("getActiveParcels"),
            EntityMap::lazy("getCarboyUseCycles"),
            EntityMap::lazy("getPurchaseOrders"),
            EntityMap::lazy("getSolidsContainers"),
            EntityMap::lazy("getPickups"),
            EntityMap::lazy("getScintVialCollections"),
            EntityMap::lazy("getCurrentScintVialCollections"),
            EntityMap::lazy("getOpenInspections"),
            EntityMap::lazy("getQuarterly_inventories"),
            EntityMap::eager("getVerifications"),
            EntityMap::lazy("getBuidling"),
            EntityMap::lazy("getCurrentVerifications"),
            EntityMap::lazy("getWipeTests")
        ));

        EntityManager::with_entity_maps(Inspection::class, array(
            EntityMap::eager("getInspectors"),
            EntityMap::lazy("getRooms"),
            EntityMap::lazy("getResponses"),
            EntityMap::lazy("getDeficiency_selections"),
            EntityMap::lazy("getPrincipalInvestigator"),
            EntityMap::eager("getStatus"),
            EntityMap::lazy("getChecklists"),
            EntityMap::lazy("getInspection_wipe_tests")
        ));

        return $principalInvestigator;
    }

    public function getMyLabWidgets(){
        $widgets = array();

        // Get session-cached user details
        $sess_user = $this->getCurrentUser();

        // Get persisted user details via session user ID
        $user = $this->getUserById($sess_user->getKey_id());

        foreach( ModuleManager::getAllModules() as $module ){
            if( $module instanceof MyLabWidgetProvider ){
                $widgets = array_merge( $widgets, $module->getMyLabWidgets( $user ));
            }
        }

        return $widgets;
    }

    public function getMyProfile( $userId = null ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $userId = $this->getValueFromRequest('userId', $userId);
        if( !isset($userId) ){
            $LOG->debug("User retrieving own profile");
            $userId = $this->getCurrentUser()->getKey_id();
        }

        $user = $this->getUserById($userId);
        if( !isset($user) ){
            return new ActionError("No such user", 404);
        }

        $department = $this->getDepartmentForUser( $user );

        // Collect User Info
        // Notes:
        //   Phone number inclusion varies by Role:
        //     PI user – Office Phone and Emergency Phone.
        //     Lab Contact – Lab Phone and Emergency Phone.
        //     Lab Personnel – Lab Phone only
        $userData = array(
            'First_name' => $user->getFirst_name(),
            'Last_name' => $user->getLast_name(),
            'Name' => $user->getName(),
            'Position' => $user->getPosition(),
            'Department' => $department->getName() ?? null
        );

        if( CoreSecurity::userHasRoles($user, array('Principal Investigator')) ){
            $userData['Office_phone'] = $user->getOffice_phone() ?? '';
            $userData['Emergency_phone'] = $user->getEmergency_phone() ?? '';
        }
        else{

            // Add PI details
            $pi = $this->getPrincipalInvestigatorOrSupervisorForUser( $user );
            $userData['PI'] = array(
                'Name' => $pi->getUser()->getName(),
                'Position' => $pi->getUser()->getPosition()
            );

            if( CoreSecurity::userHasRoles($user, array('Lab Personnel')) ){
                $userData['Lab_phone'] = $user->getLab_phone() ?? '';
            }

            if( CoreSecurity::userHasRoles($user, array('Lab Contact')) ){
                $userData['Emergency_phone'] = $user->getEmergency_phone() ?? '';
            }
        }

        return new GenericDto($userData);
    }

    public function saveMyProfile( $userId = NULL, $profile = NULL ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        if( !isset($profile) ){
            $LOG->debug("Read profile data from input");
            $profile = $this->readRawInputJson();
        }

        $userId = $this->getValueFromRequest('userId', $userId);
        if( !isset($userId) ){
            $LOG->debug("User editing own profile");
            $userId = $this->getCurrentUser()->getKey_id();
        }

        $user = $this->getUserById($userId);
        if( !isset($user) ){
            return new ActionError("No such user", 404);
        }

        $LOG->info("Checking profile changes for $user");
        $LOG->debug($profile);

        $fields = array(
            'First_name', 'Last_name', 'Position',
            'Office_phone', 'Emergency_phone', 'Lab_phone'
        );

        $updated = false;
        foreach( $fields as $field ){
            // TODO: Should we allow nulls? By using isset, we prevent the ability to empty a value
            if( isset($profile[$field]) ){
                $setter = "set$field";
                $getter = "get$field";
                $prev = $user->$getter();

                if( $prev != $profile[$field] ){
                    $LOG->debug("Updating user $field");
                    $user->$setter($profile[$field]);
                    $updated = true;
                }
            }
        }

        if( $updated ){
            $LOG->info("Saving changes made to $user profile...");
            $userDao = new UserDAO();
            $userDao->save($user);
        }
        else{
            $LOG->info("No changes made to $user profile");
        }

        return $this->getMyProfile( $user->getKey_id() );
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

    public function getRelationships( $class1 = NULL, $class2 = NULL, $override = NULL ){
    	if($class1==NULL)$class1 = $this->getValueFromRequest('class1', $class1);
    	if($class2==NULL)$class2 = $this->getValueFromRequest('class2', $class2);
        if($override==NULL)$override = $this->getValueFromRequest('override', $override);

    	// make sure first letter of class name is capitalized.
    	$class1 = ucfirst($class1);
    	$class2 = ucfirst($class2);

    	$relationshipFactory = new RelationshipMappingFactory();
    	// get the relationship mapping for the relevant classes
    	$relationship = $relationshipFactory->getRelationship($class1, $class2, $override);

    	if( $relationship instanceof ActionError ) {
    		return $relationship;
    	}

        $dao = new RelationshipDAO();
        $relationships = $dao->getRelationships($relationship);

    	return $relationships;
    }

    public function setMasterHazardIds(){
    	$l = Logger::getLogger("hazardthingus");
    	$dao = new GenericDAO(new Hazard());
    	$hazards = $dao->getLeafLevelHazards();

    	EntityManager::with_entity_maps(Hazard::class, array(
    	    EntityMap::lazy("getSubHazards"),
    	    EntityMap::lazy("getActiveSubHazards"),
    	    EntityMap::lazy("getChecklist"),
    	    EntityMap::lazy("getRooms"),
    	    EntityMap::lazy("getInspectionRooms"),
    	    EntityMap::lazy("getHasChildren"),
    	    EntityMap::lazy("getParentIds"),
            EntityMap::lazy("getPrincipalInvestigators")
        ));

    	foreach($hazards as $hazard){
    		$this->setMasterHazardId($hazard);
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

    public function setHazardTypes(){
        $l = Logger::getLogger("yo");
        $rooms = $this->getAllRooms(true);
        foreach($rooms as $key=>$room){
            $room->getHazardTypesArePresent();
            $room = $this->saveRoom($room);
        }
        return $rooms;
    }

	public function getPropertyByName( $type = null, $id = null, $property = null){
		$l = Logger::getLogger(__FUNCTION__);

		if($id == null)$id = $this->getValueFromRequest('id', $id);
		if($type == null)$type = $this->getValueFromRequest('type', $type);
		if($property==null)$property = $this->getValueFromRequest('property', $property);
		if($property==null || $id == null || $type == null)return new ActionError('You forgot a param, yo.');

		$pr = strtolower($property);
		foreach(get_class_methods($type) as $method ){
			if( preg_match_all('(get|'.$pr.')', strtolower($method)) > 1 ) {
				$methodName = $method;
			}
		}

		//prevent injection hacks by instantiating a new object of the type provided, clearing any gunk

		if(!$type){
			$l->fatal("somebody tried calling $methodName something that doesn't exist ");
			return "no such method";
		}
		$objOfType = new $type();
		if(!$objOfType){
			$l->fatal("somebody tried calling $methodName on $type");
			return "no such thing";
		}
		$dao = new GenericDAO($objOfType);
		$objOfType = $dao->getById($id);
		if(!$objOfType){
			$l->fatal("somebody tried calling $methodName on $type, which is a valid type, but the id, $obj->getKey_id(),  wasn't valid");
			return "no such $type";
		}

		//only call the method if it exists in our defined class matching the passed object's type
		if(method_exists ( $objOfType , $methodName )){
			return $objOfType->$methodName();
		}
		$l->fatal("somebody tried calling $methodName on $type");
		return "no such method";
	}

    public function getRoomHasHazards($id = null, $piIds = null){
        $id = $this->getValueFromRequest("id", $id);
        //if($id == null)return new ActionError("No room id provided");
        if($piIds == null)$piIds = $this->getValueFromRequest("piIds", $piIds);
        $l = Logger::getLogger(__FUNCTION__);
        if($id == null || $piIds == null)return new ActionError("No id provided");

        //$this->LOG->trace("$this->logprefix Looking up all entities" . ($sortColumn == NULL ? '' : ", sorted by $sortColumn"));

		// Get the db connection
		$db = DBConnection::get();
        $inQuery = implode(',', array_fill(0, count($piIds), '?'));
        $l->debug($inQuery);
		//Prepare to query all from the table
		$sql = "SELECT COUNT(key_id) FROM principal_investigator_hazard_room where room_id = ?
                             AND principal_investigator_id IN($inQuery)";
		// Query the db and return an array of $this type of object
        $stmt = DBConnection::prepareStatement($sql);

        $stmt->bindValue( 1, $id );
        $i = 2;
        foreach($piIds as $val){
            $stmt->bindValue( $i, $val );
            $i++;
        }

		if ($stmt->execute() ) {
            $result = $stmt->fetch(PDO::FETCH_NUM);
            return $result[0] != "0";
		} else{
			$error = $stmt->errorInfo();
			$result = new QueryError($error);
			$l->fatal('Returning QueryError with message: ' . $result->getMessage());
            return new ActionError("query error");
		}


    }

    public function getPisAndRoomsByHazard($id = null){
        if($id == null)$id = $this->getValueFromRequest('id', $id);
        $l = Logger::getLogger(__FUNCTION__);

        $l->debug('PASSED ID IS: ' . $id);

        $dao = new GenericDAO(new PrincipalInvestigatorHazardRoomRelation());
        $group =  new WhereClauseGroup(array(new WhereClause("hazard_id","=",$id)));
        $rels = $dao->getAllWhere($group);

        EntityManager::with_entity_maps(PrincipalInvestigatorHazardRoomRelation::class, array(
		    EntityMap::eager("getRoom_id"),
		    EntityMap::eager("getPrincipal_investigator_id"),
            EntityMap::lazy("getHazard")
        ));

        $rDao = new RoomDAO();
        /**
           * @var $relation PrincipalInvestigatorHazardRoomRelation
           */

        $relations = array();
        foreach($rels as $relation){

            if($relation->getRoom_id() != null){
                /**
                 * @var $room Room
                 */
                $room = $rDao->getById($relation->getRoom_id());
                if($room != null){
                    $relation->setRoomName($room->getName());
                    $relation->setBuildingName($room->getBuilding_name());
                }

            }
            $pi = $this->getPIById($relation->getPrincipal_investigator_id());
            if($pi != null && $pi->getIs_active() && $relation->getRoomName() != null)$relations[] = $relation;
        }

        return $relations;
    }

}
?>

<?php
class Auth_ActionManager {

    ///////////////////////////////
    // Core Login/Logout actions //
    ///////////////////////////////

    public function loginAction( $username, $password, $destination = NULL ) {
        $LOG = Logger::getLogger( __CLASS__ . '.' . __function__ );

        if($destination != NULL) {
            $_SESSION['DESTINATION'] = $destination;
        }

        $auth_manager = new AuthManager();

        ///////////////////////////////////////
        // Authenticate provided credentials
        $LOG->debug("Authenticating '$username'...");
        $authentication = $auth_manager->authenticate( $username, $password );

        if( !$authentication->success() ){
            // Credentials are invalid
            $LOG->info("Failed login attempt for '$username'");
            // otherwise, return false to indicate failure
            $_SESSION['ERROR'] = "The username or password you entered was incorrect.";
            return false;
        }

        $LOG->debug("Successfully authenticated '$username'");

        ///////////////////////////////////////
        // Credentials are authentic
        // Authorize provided name
        $LOG->debug("Authorizing '$username'...");
        $authorization = $auth_manager->authorize( $authentication );

        if( !$authorization->success() ){
            // Authorization failed

            if( $authorization->getAuthorization() == null ){
                throw new Exception("No authorization object provided for authenticated user!!");
            }
            else {
                // User exists, but is inactive
                $LOG->info("User '$username' is inactive");
                $_SESSION['ERROR'] = "Your account has been disabled. If you believe this is in error, please contact your administrator.";
            }

            return false;
        }

        ///////////////////////////////////////
        // Authorization successful
        // Apply user to session
        $this->applySessionAuthorization($authorization);

        $LOG->info("Successful login attempt for '$username'");
        return true;
    }

    public function logoutAction(){
        session_destroy();
        $_SESSION['CANDIDATE'] = null;
        $_SESSION['USER'] = null;
        $_SESSION['ROLE'] = null;
        return true;
    }

    ///////////////////////////
    // Impersonation Actions //
    ///////////////////////////

    public function impersonateUserAction($impersonateUsername = NULL, $currentPassword = NULL) {
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $current_user = AuthManager::getCurrentUser();
        $LOG->info("User " . $current_user->getUsername() . " attempting to impersonate $impersonateUsername");

        if( $impersonateUsername == $current_user->getUsername() ){
            return new ActionError("Cannot impersonate yourself", 400);
        }

        if( isset($_SESSION['IMPERSONATOR']) ){
            return new ActionError("Cannot impersonate another user while impersonation session is active", 400);
        }

        // TODO: Verify current user's password
        $auth_manager = new AuthManager();
        $authentication = new AuthenticationResult( true, $impersonateUsername );

        // copy current-user info into session
        $_SESSION['IMPERSONATOR'] = array(
            'USER' => $_SESSION['USER'],
            'ROLE' => $_SESSION['ROLE']
        );

        $authorization = $auth_manager->authorize( $authentication );
        return $this->applySessionAuthorization($authorization);
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

    //////////////////////////////////////
    // New-User Access-Request Actions  //
    //////////////////////////////////////

    /**
     * Retrieve department listing available for new-user requests
     */
    public function getNewUserDepartmentListing(){
        $dao = new GenericDAO(new Department());
        $depts = $dao->getAll();

        $dtos = DtoFactory::buildDtos($depts, function($d){
            $d_pis = DtoFactory::buildDtos( $d->getPrincipalInvestigators(), function($pi){
                return new GenericDto([
                    'Key_id' => $pi->getKey_id(),
                    'Name' => $pi->getName()
                ]);
            });

            return new GenericDto([
                'Key_id' => $d->getKey_id(),
                'Name' => $d->getName(),
                'PrincipalInvestigators' => $d_pis
            ]);
        });

        return $dtos;
    }

    public function submitAccessRequest( int $pi_id ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        if( !isset($pi_id) ){
            return new ActionError("Missing data", 400);
        }

        // Retrieve & Validate PI selection
        $piDao = new PrincipalInvestigatorDAO();
        $pi = $piDao->getById($pi_id);

        if( !isset($pi) ){
            return new ActionError("Invalid PI", 404);
        }

        // Prepare access request
        $candidate = AuthManager::getCandidateUser();
        $LOG->info("Submitting access request for $candidate. PI:$pi");

        // Create a new PENDING request for the username and selected PI
        $accessRequest = new UserAccessRequest();
        $accessRequest->setNetwork_username($candidate->getUsername());
        $accessRequest->setPrincipal_investigator_id($pi->getKey_id());
        $accessRequest->setStatus( UserAccessRequest::STATUS_PENDING );

        $requestDao = new GenericDAO( $accessRequest );
        $accessRequest = $requestDao->save($accessRequest);
        $LOG->info("Saved $accessRequest");

        // TODO: Notify PI
        $LOG->info("TODO: Notify $pi of new request");

        return $accessRequest;
    }

    //////////////////////////////////////
    // Action-related utility functions //
    //////////////////////////////////////

    /**
     * Applies the authorization User details to this Session
     * It is assumed that the requestor has already been authenticated
     */
    private function applySessionAuthorization( AuthorizationResult &$authorization ){
        if( $authorization->success() ){
            $user = $authorization->getAuthorization();

            $_SESSION['AUTH_TYPE'] = $authorization->getType();

            $coreActionManager = ModuleManager::getModuleByName( CoreModule::$NAME )->getActionManager();

            /////////////////
            // Save user details to session, based on auth type

            // Normal, Active, User
            if( $authorization->getType() == AuthModule::AUTH_TYPE_ACTIVE_USER ){
                $_SESSION['ROLE'] = $coreActionManager->getCurrentUserRoles($user);
                $_SESSION['USER'] = $user;
                $_SESSION['DESTINATION'] = $coreActionManager->getUserDefaultPage();
            }

            // Candidate user
            else if( $authorization->getType() == AuthModule::AUTH_TYPE_CANDIDATE_USER ){
                $_SESSION['CANDIDATE'] = $user;
                unset($_SESSION['DESTINATION']);// = LOGIN_PAGE;
            }

            // Unknown
            else {
                Logger::getRootLogger()->error("Invalid authorization type '" . $authorization->getType() . "'");
            }
        }

        return $authorization->success();
    }
}
?>

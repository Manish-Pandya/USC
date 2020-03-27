<?php
// TODO: Divorce this from ActionManager
class Auth_ActionManager extends ActionManager {

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

    /**
     * Applies the authorization User details to this Session
     * It is assumed that the requestor has already been authenticated
     */
    private function applySessionAuthorization( AuthorizationResult &$authorization ){
        if( $authorization->success() ){
            $user = $authorization->getAuthorization();

            $_SESSION['AUTH_TYPE'] = $authorization->getType();

            if( $authorization->getType() == AuthModule::AUTH_TYPE_ACTIVE_USER ){
                $_SESSION['ROLE'] = $this->getCurrentUserRoles($user);
                $_SESSION['USER'] = $user;
                $_SESSION['DESTINATION'] = $this->getUserDefaultPage();
            }
            else if( $authorization->getType() == AuthModule::AUTH_TYPE_CANDIDATE_USER ){
                $_SESSION['CANDIDATE'] = $user;
                unset($_SESSION['DESTINATION']);// = LOGIN_PAGE;
            }
            else {
                Logger::getRootLogger()->error("Invalid authorization type '" . $authorization->getType() . "'");
            }
        }

        return $authorization->success();
    }
}
?>

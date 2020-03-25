<?php

/**
 * Authorization Handler which checks that the authenticated subject
 * is an Active User in the database
 *
 * @see User
 */
class ActiveUserAuthorizationHandler implements I_AuthorizationHandler {
    public function is_enabled(){ return true; }

    public function authorize( AuthenticationResult &$authentication ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        if( !$authentication->success() ){
            $LOG->error("Provided AuthenticationResult is unsuccessful");
            return new AuthorizationResult(false, null);
        }

        $username = $authentication->getSubject();

        // Make sure they're an Erasmus user by username lookup
        $dao = new UserDAO();
        $user = $dao->getUserByUsername($username);

        if ($user == null) {
            // User does not exist
            $LOG->info("No such user '$username'");
            return new AuthorizationResult(false, null);
        }
        else if( !$user->getIs_active() ){
            // User is not active
            $LOG->info("Local authentication succeeded, but the user is inactive: $user");

            // successful login, but not an enabled Erasmus user, return false
            return new AuthorizationResult(false, $user);
        }
        else {
            // return true to indicate success
            return new AuthorizationResult(true, $user);
        }
    }
}
?>

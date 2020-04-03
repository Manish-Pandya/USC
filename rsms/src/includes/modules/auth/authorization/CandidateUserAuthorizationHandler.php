<?php

/**
 * Authorization Handler which checks that the authenticated subject
 * has no matching User in the database
 *
 * @see User
 */
class CandidateUserAuthorizationHandler implements I_AuthorizationHandler {
    public function __toString(){ return get_class($this); }
    public function is_enabled(){ return true; }
    public function type(){ return AuthModule::AUTH_TYPE_CANDIDATE_USER; }

    public function authorize( AuthenticationResult &$authentication ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        if( !$authentication->success() ){
            $LOG->error("Provided AuthenticationResult is unsuccessful");
            return false;
        }

        $username = $authentication->getSubject();

        // Make sure they're not Erasmus user by username lookup
        $dao = new UserDAO();
        $user = $dao->getUserByUsername($username);

        if( $user != null ){
            // User exists, so username is not a new-user candidate
            return false;
        }

        // User does not exist, and is therefore a candidate
        // Look up and include user name, email, etc
        $user_details = AuthManager::getUserDetails($username);

        $candidate = new CandidateUser(
            $username,
            $user_details->getFirst_name() ?? '',
            $user_details->getLast_name() ?? '',
            $user_details->getEmail() ?? ''
        );

        return $candidate;
    }
}
?>

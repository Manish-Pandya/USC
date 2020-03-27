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

        if ($user == null) {
            // User does not exist
            $LOG->info("User '$username' does not exist, and is a new-user candidate");

            // TODO: Look up user name, email, etc
            $candidate = new CandidateUser($username);
            return $candidate;
        }
        else {
            // User exists, so username is not a new-user candidate
            return false;
        }
    }
}
?>

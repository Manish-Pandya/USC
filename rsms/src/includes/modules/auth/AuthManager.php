<?php

/**
 * Manager class responsible for handling of Authentication/Authorization
 * of a user
 */
class AuthManager {
    public function __toString(){ return get_class($this); }

    public static function hasCurrentUser(){
        return isset($_SESSION) && isset($_SESSION['USER']);
    }

    public static function getCurrentUser(){
        if( self::hasCurrentUser() ){
            $id = $_SESSION['USER']->getKey_id();
            $dao = new UserDAO();
            $user = $dao->getById($id);
            return $user;
        }

        return null;
    }

    function getAuthenticationHandlers(){
        // TODO: Externalize list of handlers to app config?
        $handlers = [
            new EmergencyAuthenticationHandler(),
            new LDAPAuthenticationHandler(),
            new DevAuthenticationHandler()
        ];

        return array_filter($handlers, function($h){ return $h->is_enabled(); });
    }

    function getAuthorizationHandlers(){
        return [
            new ActiveUserAuthorizationHandler(),
            new CandidateUserAuthorizationHandler()
        ];
    }

    /**
     * 
     */
    public function authenticate( string $username, string $password ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        // Get all configured authentication handlers
        $handlers = $this->getAuthenticationHandlers();

        // Try each handler until one works
        $result = null;
        foreach ($handlers as $handler) {
            $LOG->debug("Authenticating via $handler");
            $result = $handler->do_auth($username, $password);

            if( $result ){
                $LOG->info("Successfully authenticated '$username' via $handler");
                break;
            }
            else {
                $LOG->info("Failed authentication of '$username' via $handler");
            }
        }

        return new AuthenticationResult( $result, $username );
    }

    public function authorize( AuthenticationResult $authentication ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);
        $handlers = $this->getAuthorizationHandlers();

        // Try each handler until one works
        $subject = null;
        $authtype = null;
        foreach ($handlers as $handler) {
            $LOG->debug("Authorizing via $handler");
            $subject = $handler->authorize($authentication);
            $LOG->debug("Result: $subject");

            if( $subject !== false ){
                $authtype = $handler->type();
                break;
            }
        }

        return new AuthorizationResult($subject !== false, $subject, $authtype);
    }

}

?>

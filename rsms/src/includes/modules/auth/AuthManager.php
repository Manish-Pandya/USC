<?php

/**
 * Manager class responsible for handling of Authentication/Authorization
 * of a user
 */
class AuthManager {
    public function __toString(){ return get_class($this); }

    public static function hasCurrentUser(){
        return isset($_SESSION) && isset($_SESSION['USER']) && $_SESSION['USER'] != null;
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

    public static function hasCandidateUser(){
        return isset($_SESSION) && isset($_SESSION['CANDIDATE']) && $_SESSION['CANDIDATE'] != null;
    }

    public static function getCandidateUser(){
        if( self::hasCandidateUser() ){
            return $_SESSION['CANDIDATE'];
        }

        return null;
    }

    static function getAuthenticationHandlers(){
        // TODO: Externalize list of handlers to app config?
        $handlers = [
            new EmergencyAuthenticationHandler(),
            new LDAPAuthenticationHandler(),
            new DevAuthenticationHandler()
        ];

        return array_filter($handlers, function($h){ return $h->is_enabled(); });
    }

    static function getAuthorizationHandlers(){
        $handlers = [
            new ActiveUserAuthorizationHandler(),
            new CandidateUserAuthorizationHandler()
        ];

        return array_filter($handlers, function($h){ return $h->is_enabled(); });
    }

    static function getUserDetailProviders(){
        $providers = [
            new LDAPUserDetailProvider(),
            new ExistingUserDetailProvider()
        ];

        return array_filter($providers, function($p){ return $p->is_enabled(); });
    }

    /**
     * 
     */
    public static function authenticate( string $username, string $password ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        // Get all configured authentication handlers
        $handlers = self::getAuthenticationHandlers();

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

    public static function authorize( AuthenticationResult $authentication ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);
        $handlers = self::getAuthorizationHandlers();

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

    public static function getUserDetails( string $username ){
        $providers = self::getUserDetailProviders();

        $details = null;
        foreach( $providers as $provider ){
            $details = $provider->getUserDetails($username);

            if( $details ){
                break;
            }
        }

        return $details;
    }

    public static function prepareUserFromAccessRequest( UserAccessRequest &$request ){
        if( !$request ){
            return null;
        }

        // Prepare a new User based on incoming request
        $user = new User();
        $user->setUsername($request->getNetwork_username());
        $user->setFirst_name($request->getFirst_name());
        $user->setLast_name($request->getLast_name());
        $user->setEmail($request->getEmail());

        return $user;
    }
}

?>

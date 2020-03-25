<?php

/**
 * Manager class responsible for handling of Authentication/Authorization
 * of a user
 */
class AuthManager {
    public function __toString(){ return get_class($this); }

    function getAuthenticationHandlers(){
        // TODO: Externalize list of handlers to app config?
        $handlers = [
            new EmergencyAuthenticationHandler(),
            new LDAPAuthenticationHandler(),
            new DevAuthenticationHandler()
        ];

        return array_filter($handlers, function($h){ return $h->is_enabled(); });
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
            $LOG->debug("Result: $result");

            if( $result ){
                break;
            }
        }

        return new AuthenticationResult( $result, $username );
    }

    public function authorize( AuthenticationResult $authentication ){
        // Only case for authorization is that the authenticated subject is an Active User
        $authHandler = new ActiveUserAuthorizationHandler();
        return $authHandler->authorize( $authentication );
    }

}

?>

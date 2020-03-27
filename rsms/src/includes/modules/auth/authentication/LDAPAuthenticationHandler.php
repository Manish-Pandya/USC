<?php

/**
 * Authenticate via LDAP
 */
class LDAPAuthenticationHandler implements I_AuthenticationHandler {
    public function __toString(){ return get_class($this); }

    public function is_enabled(){
        return ApplicationConfiguration::get(
            ApplicationBootstrapper::CONFIG_SERVER_AUTH_PROVIDE_LDAP,
            false);
    }

    public function do_auth( string $name, string $secret ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);
        $LOG->debug("Attempt LDAP authentication for $name");

        // LDAP may not be loaded
        if( !class_exists('LDAP') ){
            $LOG->error("Attempting LDAP authentication, but no LDAP provider has been defined");
            return false;
        }

        $ldap = new LDAP();

        // if successfully authenticates by LDAP:
        try{
            if ($ldap->IsAuthenticated($name, $secret)) {
                return true;
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
}
?>

<?php

/**
 * Wrapper for non-production login.
 * Because RSMS does not track passwords, this is a less-secure path
 * than LDAP.
 */
class DevAuthenticationHandler implements I_AuthenticationHandler {
    public function __toString(){ return get_class($this); }

    public function is_enabled(){
        return ApplicationConfiguration::get(
            ApplicationBootstrapper::CONFIG_SERVER_AUTH_PROVIDE_DEV_IMPERSONATE,
            false);
    }

    public function do_auth( string $name, string $secret ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);
        $LOG->warn("Attempt DEV-IMPERSONATE authentication for '$name'");
        if( $secret == ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_AUTH_PROVIDE_DEV_IMPERSONATE_PASSWORD) ){
            return true;
        }

        return false;
    }
}

?>

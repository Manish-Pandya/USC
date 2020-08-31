<?php

/**
 * Login to hard-coded 'emergency' user
 */
class EmergencyAuthenticationHandler implements I_AuthenticationHandler {
    private const EMERGENCY_USERNAME = 'EmergencyUser';

    public function __toString(){ return get_class($this); }

    public function is_enabled(){
        return ApplicationConfiguration::get(
            ApplicationBootstrapper::CONFIG_SERVER_AUTH_PROVIDE_EMERGENCY,
            true);
    }

    public function do_auth( string $name, string $secret ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        $LOG->info("Attempte emergency-user authentication for '$name'");
        $_match_name = $name === self::EMERGENCY_USERNAME;
        $_match_pass = $secret === ApplicationConfiguration::get(ApplicationBootstrapper::CONFIG_SERVER_AUTH_PROVIDE_EMERGENCY_PASSWORD);

        if( $_match_name && $_match_pass ) {
            return true;
        }

        return false;
    }
}

?>

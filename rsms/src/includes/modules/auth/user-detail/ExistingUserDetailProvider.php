<?php

/**
 * Retrieve empty/existing user details
 */
class ExistingUserDetailProvider implements I_UserDetailProvider {
    public function __toString(){ return get_class($this); }

    public function is_enabled(){
        return !ApplicationConfiguration::get(
            ApplicationBootstrapper::CONFIG_SERVER_AUTH_PROVIDE_LDAP,
            false);
    }

    public function getUserDetails( string $username ){
        //Get responses for Inspection
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        // LDAP is disabled; look up user just from our database
        $LOG->info("Lookup user '$username' in local database");
        $dao = new UserDAO();
        $user = $dao->getUserByUsername($username);

        if( !$user ){
            $LOG->info("No user '$username' found in local database");
            $user = new User();
            $user->setUsername($username);
        }
        else {
            $LOG->warn("Found user with name '$username': $user");
        }

        return $user;
    }
}
?>

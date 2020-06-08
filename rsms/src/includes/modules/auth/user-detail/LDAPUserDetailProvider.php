<?php

/**
 * Retrieve user details via LDAP
 */
class LDAPUserDetailProvider implements I_UserDetailProvider {
    public function __toString(){ return get_class($this); }

    public function is_enabled(){
        return ApplicationConfiguration::get(
            ApplicationBootstrapper::CONFIG_SERVER_AUTH_PROVIDE_LDAP,
            false);
    }

    public function getUserDetails( string $username ){
        //Get responses for Inspection
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        $LOG->info("Lookup user '$username' via LDAP");

        $ldap = new LDAP();

        $fieldsToFind = array("cn","sn","givenName","mail");
        if ($ldapData = $ldap->GetAttr($username, $fieldsToFind)){
            $user = new User();
            $user->setFirst_name(ucfirst(strtolower($ldapData["givenName"])));
            $user->setLast_name(ucfirst(strtolower($ldapData["sn"])));
            $user->setEmail(strtolower($ldapData["mail"]));
            $user->setUsername($ldapData["cn"]);

            return $user;
        } else {
            return false;
        }
    }
}
?>

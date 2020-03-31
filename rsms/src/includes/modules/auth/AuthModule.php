<?php
class AuthModule implements RSMS_Module {
    public const NAME = 'Auth';

    public const AUTH_TYPE_ACTIVE_USER = 'ACTIVE_USER';
    public const AUTH_TYPE_CANDIDATE_USER = 'CANDIDATE_USER';

    public function getModuleName(){ return self::NAME; }
    public function getUiRoot(){ return '/auth'; }
    public function isEnabled(){ return true; }

    public function getActionManager(){
        return new Auth_ActionManager();
    }

    public function getActionConfig(){
        $mappings = [
            // Login/Logout
            "loginAction"   => new ActionMapping("loginAction", "views/RSMSCenter.php", LOGIN_PAGE, array(), false),
            "logoutAction"  => new ActionMapping("logoutAction",LOGIN_PAGE, LOGIN_PAGE, array(), false),

            // new-user requests
            "getNewUserDepartmentListing" => new SecuredActionMapping("getNewUserDepartmentListing", [], 'AuthSecurity::userIsCandidate'),
        ];

        // Only include Impersonation mappings if the feature is enabled
        if( ApplicationConfiguration::get( CoreModule::CONFIG_FEATURE_IMPERSONATION, false) ){
            $mappings["impersonateUserAction"] = new ActionMapping("impersonateUserAction", "", "", array("Admin"));
            $mappings["getImpersonatableUsernames"] = new ActionMapping("getImpersonatableUsernames", "", "", array("Admin"));
            $mappings["stopImpersonating"] = new ActionMapping("stopImpersonating", LOGIN_PAGE, LOGIN_PAGE, array(), false);
        }

        return $mappings;
    }

}
?>

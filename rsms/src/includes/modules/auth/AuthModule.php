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
        $r_any = [];
        $r_admin = ['Admin'];
        $r_mgmt = array_merge( $r_admin, [LabInspectionModule::ROLE_PI] );

        $mappings = [
            // Login/Logout
            "loginAction"   => new ActionMapping("loginAction", "views/RSMSCenter.php", LOGIN_PAGE, $r_any, false),
            "logoutAction"  => new ActionMapping("logoutAction",LOGIN_PAGE, LOGIN_PAGE, $r_any, false),

            // new-user requests
            "getNewUserDepartmentListing" => new SecuredActionMapping("getNewUserDepartmentListing", $r_any, 'AuthSecurity::userIsCandidate'),
            "submitAccessRequest" => new SecuredActionMapping("submitAccessRequest", $r_any, 'AuthSecurity::candidateCanSubmitNewRequest'),

            "getAllAccessRequests" => new SecuredActionMapping("getAllAccessRequests", $r_mgmt),
            "resolveAccessRequest" => new SecuredActionMapping("resolveAccessRequest", $r_mgmt),
        ];

        // Only include Impersonation mappings if the feature is enabled
        if( ApplicationConfiguration::get( CoreModule::CONFIG_FEATURE_IMPERSONATION, false) ){
            $mappings["impersonateUserAction"] = new ActionMapping("impersonateUserAction", "", "", $r_admin);
            $mappings["getImpersonatableUsernames"] = new ActionMapping("getImpersonatableUsernames", "", "", $r_admin);
            $mappings["stopImpersonating"] = new ActionMapping("stopImpersonating", LOGIN_PAGE, LOGIN_PAGE, $r_any, false);
        }

        return $mappings;
    }

}
?>

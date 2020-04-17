<?php
class AuthModule implements RSMS_Module, MessageTypeProvider, MyLabWidgetProvider {
    public const NAME = 'Auth';

    public const AUTH_TYPE_ACTIVE_USER = 'ACTIVE_USER';
    public const AUTH_TYPE_CANDIDATE_USER = 'CANDIDATE_USER';

    //////////////////
    // Message Types
    public const MTYPE_ACCESS_REQUEST_SUBMITTED = 'AccessRequestSubmitted'; // Personnel requests access from PI (to:pi)
    public const MTYPE_ACCESS_REQUEST_DENIED    = 'AccessRequestDenied';    // PI denies acceess (to:user)
    public const MTYPE_ACCESS_REQUEST_APPROVED  = 'AccessRequestApproved';  // PI approves acceess (to:user)
    //////////////////

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

    public function getMyLabWidgets( User $user ){
        $widgets = [];

        $piDao = new PrincipalInvestigatorDAO();
        $pi = $piDao->getByUserId( $user->getKey_id());

        // For Principal Investigators
        if( isset($pi) && $pi != null ){
            // List any PENDING requests
            $requestDao = new UserAccessRequestDAO();
            $pendingRequests = $requestDao->getByPrincipalInvestigator( $pi->getKey_id(), UserAccessRequest::STATUS_PENDING );

            if( !empty($pendingRequests) ){
                $pendingRequestsWidget = new MyLabWidgetDto();
                $pendingRequestsWidget->title = "Lab Member Access Request" . (count($pendingRequests) != 1 ? 's' : '');
                $pendingRequestsWidget->icon = "icon-contact";
                $pendingRequestsWidget->group = '000_access_requests';
                $pendingRequestsWidget->template = 'user-access-requests';
                $pendingRequestsWidget->fullWidth = 1;
                $pendingRequestsWidget->toolbar = 0;
                $pendingRequestsWidget->data = $pendingRequests;

                $widgets[] = $pendingRequestsWidget;
            }
            // otherwise do nothing
        }
        // otherwise do nothing

        return $widgets;
    }

    public function getMessageTypes(){
        $mtypes = [
            new MessageTypeDto(self::NAME, self::MTYPE_ACCESS_REQUEST_SUBMITTED,
                'Automatic email is sent when a new user requests access to RSMS.',
                PrincipalInvestigator_UARMessageProcessor::class,
                [UserAccessRequest::class]),

            new MessageTypeDto(self::NAME, self::MTYPE_ACCESS_REQUEST_APPROVED,
                'Automatic email is sent when an access request is Approved.',
                Requester_UARMessageProcessor::class,
                [UserAccessRequest::class]),

            new MessageTypeDto(self::NAME, self::MTYPE_ACCESS_REQUEST_DENIED,
                'Automatic email is sent when an access request is Denied.',
                Requester_UARMessageProcessor::class,
                [UserAccessRequest::class]),
        ];

        return $mtypes;
    }

    public function getMacroResolvers(){
        return [];
    }
}
?>

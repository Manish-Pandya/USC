<?php

class Auth_Hooks {

    public static function after_access_request_submitted ( UserAccessRequest $request ){
        // Verify that the request status is pending
        if( $request->getStatus() != UserAccessRequest::STATUS_PENDING ){
            return;
        }

        // New request has been submitted; notify PI
        self::enqueueUserAccessRequestMessage( $request->getKey_id(), AuthModule::MTYPE_ACCESS_REQUEST_SUBMITTED);

        ///////////////////////
        // Asses potential duplicate PI (is this PI trying to request access to themselves?)
        if( $request->getIs_potential_duplicate() ){
            $LOG = LogUtil::get_logger( __CLASS__ , __FUNCTION__ );
            $LOG->warn("Candidate user (" . $request->getNetwork_username() . ") first/last name matches selected PI (" . $pi_user->getUsername() . ")");

            // First/Last name match; potentially same user
            // Send email to sysadmin for possible intervention
            $LOG->info("Send message to system admin");

            $ctx = new SystemAdminMessageContext([
                'message' => "User Access Request subbmited with user name which potentially duplicates existing Principal Investigator",

                'request_name' => $request->getFirst_name() . ' ' . $request->getLast_name(),
                'request_username' => $request->getNetwork_username(),
                'request_email'    => $request->getEmail(),

                'pi_name'        => $pi_user->getName(),
                'pi_username'    => $pi_user->getUsername(),
                'pi_email'       => $pi_user->getEmail()
            ]);

            $messenger = new Messaging_ActionManager();
            $messenger->enqueueMessages(
                SysadminModule::NAME,
                SysadminModule::MTYPE_NOTIFY_SYSTEM_ADMIN,
                [ $ctx ]
            );
        }
    }

    public static function after_access_request_resolved ( UserAccessRequest $request ){
        // Verify that the request status is not pending
        if( $request->getStatus() == UserAccessRequest::STATUS_PENDING ){
            return;
        }

        $mtype = $request->getStatus() == UserAccessRequest::STATUS_APPROVED
               ? AuthModule::MTYPE_ACCESS_REQUEST_APPROVED
               : AuthModule::MTYPE_ACCESS_REQUEST_DENIED;

        // Request has been resolved; notify requester
        self::enqueueUserAccessRequestMessage( $request->getKey_id(), $mtype );
    }

    private static function enqueueUserAccessRequestMessage( $request_id, $mtype ){
        $messenger = new Messaging_ActionManager();
        $messenger->enqueueMessages(
            AuthModule::NAME,
            $mtype,
            [
                new UserAccessRequestMessageContext($request_id)
            ]
        );

    }
}

?>
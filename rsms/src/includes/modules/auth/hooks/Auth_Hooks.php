<?php

class Auth_Hooks {

    public static function after_access_request_submitted ( UserAccessRequest $request ){
        // Verify that the request status is pending
        if( $request->getStatus() != UserAccessRequest::STATUS_PENDING ){
            return;
        }

        // New request has been submitted; notify PI
        self::enqueueUserAccessRequestMessage( $request->getKey_id(), AuthModule::MTYPE_ACCESS_REQUEST_SUBMITTED);
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
<?php
class Requester_UARMessageProcessor extends A_UserAccessRequest_MessageProcessor {
    public function getRecipientsDescription(){ return "Requester"; }

    protected function getRecipientEmails( UserAccessRequest &$request ){
        // Get email address of the request's user/requester
        return [
            $request->getEmail()
        ];
    }
}
?>

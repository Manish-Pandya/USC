<?php
class PrincipalInvestigator_UARMessageProcessor extends A_UserAccessRequest_MessageProcessor {
    public function getRecipientsDescription(){ return "PI"; }

    protected function getRecipientEmails( UserAccessRequest &$request ){
        // Get email address of the request's PI
        $pi = $request->getPrincipalInvestigator();
        return [
            $pi->getUser()->getEmail()
        ];
    }
}
?>

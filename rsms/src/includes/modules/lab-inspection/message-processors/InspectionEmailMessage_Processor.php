<?php

class InspectionEmailMessage_Processor implements MessageTypeProcessor {

    public function getRecipientsDescription(){ return "PI, Contact, Inspector(s)"; }

    public static function getMessageTypeName( LabInspectionStateDto $inspectionState ){
        $messageType = null;
        if( $inspectionState->getTotals() == 0){
            $messageType = LabInspectionModule::$MTYPE_NO_DEFICIENCIES;
        }
        else if( $inspectionState->getTotals() > $inspectionState->getCorrecteds()){
            $messageType = LabInspectionModule::$MTYPE_DEFICIENCIES_FOUND;
        }
        else {
            $messageType = LabInspectionModule::$MTYPE_DEFICIENCIES_CORRECTED;
        }

        return $messageType;
    }

    public function process(Message $message, $macroResolverProvider){
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->debug("Processing context for $message");

        //  Look up details from desscriptor
        $messenger = new Messaging_ActionManager();
        $context = $messenger->getContextFromMessage($message, new InspectionReportMessageContext(null, null, null));

        // Look up Inspection
        $actionManager = new ActionManager();
        $inspection = $actionManager->getInspectionById($context->inspection_id);
        $LOG->debug("Inspection: $inspection");

        //////////////////////////////////////////////////////////////////////
        // ** Copied from deprecated ActionManager#sendInspectionEmail() ** //

        // Init an array of recipient Email addresses and another of inspector email addresses
        $recipientEmails = array();
        $inspectorEmails = array();

        // We'll need a user Dao to get Users and find their email addresses
        $userDao = new GenericDAO(new User());

        // TODO: Get state details from the already-decoded object
        // Decode the context value AGAIN as an associative array since our JsonManager expects
        //   object classes to be specified in content
        $inspectionState = JsonManager::assembleObjectFromDecodedArray($context->getInspectionState(), new LabInspectionStateDto());

        // Iterate the recipients list and add their email addresses to our array
        // TODO: Get the email details from the already-decoded object
        // Decode the context value AGAIN as an associative array since our JsonManager expects
        //   object classes to be specified in content
        $ctx_array = json_decode($message->getContext_descriptor(), true);

        $email = $ctx_array['email'];

        foreach ($email['recipient_ids'] as $id){
            $user = $userDao->getById($id);
            $recipientEmails[] = $user->getEmail();
        }

        $otherEmails = $email['other_emails'];

        if (!empty($otherEmails)) {
            $recipientEmails = array_merge($recipientEmails,$otherEmails);
        }

        // Iterate the inspectors and add their email addresses to our array
        foreach ($inspection->getInspectors() as $inspector){
            $user = $inspector->getUser();
            $inspectorEmails[] = $user->getEmail();
        }

        // ** Copied from deprecated ActionManager#sendInspectionEmail() ** //
        //////////////////////////////////////////////////////////////////////

        //  Construct macromap
        $macromap = $macroResolverProvider->resolve( array($inspection, $inspectionState, $context) );

        // prepare email details
        $details = array(
            'recipients' => implode(",", $recipientEmails),
            'cc' => implode(",", $inspectorEmails),
            'macromap' => $macromap
        );

        // Override the email body, if specified
        if( $email['text'] != null ){
            $details['body'] = $email['text'];
        }

        return array(
            $details
        );
    }
}

?>
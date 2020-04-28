<?php

abstract class A_UserAccessRequest_MessageProcessor implements MessageTypeProcessor {

    protected abstract function getRecipientEmails( UserAccessRequest &$request );

    public function process(Message $message, $macroResolverProvider){

        // Processor should...
        //  Look up details from desscriptor
        $messenger = new Messaging_ActionManager();
        $context = $messenger->getContextFromMessage($message, new UserAccessRequestMessageContext(null));

        // Look up Request
        $requestDao = new UserAccessRequestDAO();
        $request = $requestDao->getById( $context->getRequest_id() );

        // Construct macromap
        $macromap = $macroResolverProvider->resolve( [$request, $context] );

        // Determine recipients
        $recipient_emails = $this->getRecipientEmails( $request );

        // Prepare email details
        $details = [
            'recipients' => implode(',', $recipient_emails),
            'macromap' => $macromap
        ];

        // Return single-item array
        return [$details];
    }
}
?>

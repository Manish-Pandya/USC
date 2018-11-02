<?php

/**
 * Processor for Lab Inspection reminder emails.
 * Message context is a Department, User, and Year.
 */
class LabInspectionReminder_Processor implements MessageTypeProcessor {

    public function process(Message $message, $macroResolverProvider){
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->debug("Processing context for $message");

        // Processor should...
        //  Look up details from desscriptor
        $messenger = new Messaging_ActionManager();
        $context = $messenger->getContextFromMessage($message, new LabInspectionReminderContext());

        // Look up Inspection
        $actionManager = new ActionManager();
        $inspection = $actionManager->getInspectionById($context->inspection_id);

        $LOG->debug("Inspection: $inspection");

        //  Construct macromap
        $macromap = $macroResolverProvider->resolve( $context );

        if( $LOG->isTraceEnabled() ){
            $LOG->trace($macromap);
        }

        // TODO: Who to send to?
        //   "the PI and any other individuals sent the original inspection report email"
        // Problem: RSMS postInspection allows the inspector to selectively send email to PI, Contacts, and arbitrary addresses
        $recipients = array(
            $inspection->getPrincipalInvestigator()->getUser()->getEmail()
        );

        // prepare email details
        $details = array(
            'recipients' => $recipients,
            // TODO: Who to send from?
            'from' => 'LabInspectionReports@ehs.sc.edu<RSMS Portal>',
            'macromap' => $macromap
        );

        $LOG->info("Done processing details for $message");
        if( $LOG->isTraceEnabled() ){
            $LOG->trace($details);
        }

        // Must return an array.
        // This message type doesn't fan out, so no more is needed here
        return array(
            $details
        );
    }

    public static function getInspectionReportLink($inspection_id){
        $urlBase = ApplicationConfiguration::get('server.web.url');
        $webRoot = WEB_ROOT;
        return "$urlBase$webRoot" . "views/inspection/InspectionConfirmation.php#/report?inspection=$inspection_id";
    }
}

?>
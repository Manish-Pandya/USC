<?php

/**
 * Processor for Lab Inspection update emails.
 * Message context is a Department, User, and Year.
 *
 * Inspection Updates should trigger messages to all parties involved
 */
class LabInspectionUpdatedMessage_Processor implements MessageTypeProcessor {

    public function getRecipientsDescription(){ return "PI, Contact, Inspector(s)"; }

    public function process(Message $message, $macroResolverProvider){
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->debug("Processing context for $message");

        // Processor should...
        //  Look up details from desscriptor
        $messenger = new Messaging_ActionManager();
        $context = $messenger->getContextFromMessage($message, new LabInspectionReminderContext(null, null));

        // Look up Inspection
        $actionManager = new ActionManager();
        $inspection = $actionManager->getInspectionById($context->inspection_id);

        $LOG->debug("Inspection: $inspection");

        //  Construct macromap
        $macromap = $macroResolverProvider->resolve( array($context, $inspection) );

        if( $LOG->isTraceEnabled() ){
            $LOG->trace($macromap);
        }

        // Determine who to send to
        $computed_recipients = $this->computeEmailRecipients($inspection, $context);
        $cc = $computed_recipients['cc'];
        $recipients = $computed_recipients['to'];

        $LOG->info("Recipients($recipients) CC($cc)");

        // prepare email details
        $details = array(
            'recipients' => $recipients,
            'cc' => $cc,
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

    public function prepareRecipientsArray( $labstaff, $inspectors ){
        return array(
            'to' => $labstaff,
            'cc' => $inspectors
        );
    }

    /**
     * Extract recipients (to and cc) from old inspection report email
     */
    private function getRecipientsFromReportEmail( $match ){
        return $this->prepareRecipientsArray( $match['recipients'], $match['cc_recipients'] );
    }

    public function getRecipientEmailAddressesFromInspection( $inspection ){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        $pi = $inspection->getPrincipalInvestigator();
        $inspectors = $inspection->getInspectors();
        $lab_contacts = $inspection->getLabPersonnel();

        if( $LOG->isDebugEnabled() ){
            $LOG->debug( "$pi assigned to $inspection");
            $LOG->debug( count($inspectors) . " Inspector(s) assigned to $inspection");
            $LOG->debug( count($lab_contacts) . " Personnel assigned to $inspection");
        }

        // Merge to a single array of users
        $users = array();
        $users[] = $pi->getUser();

        $users = array_merge( $users, $lab_contacts );

        // Build array of user emails
        $recipients = array();
        foreach($users as $user){
            $recipients[] = $user->getEmail();
        }

        $inspector_emails = array();
        foreach( $inspectors as $inspector ){
            $inspector_emails[] = $inspector->getUser()->getEmail();
        }

        return [
            'lab_emails' => $recipients,
            'inspector_emails' => $inspector_emails
        ];
    }

    private function getRecipientsFromInspection( $inspection ){
        $emails = $this->getRecipientEmailAddressesFromInspection($inspection);

        // Implode arrays to csv
        return $this->prepareRecipientsArray(
            implode(',', $emails['lab_emails']),
            implode(',', $emails['inspector_emails'])
        );
    }

    private function union( Array &$a1, Array &$a2 ){
        return array_filter(            // Empty filter function will remove falsy (empty-string) values
            array_unique(               // Remove duplicate values
                array_merge($a1, $a2)   // Merge the two arrays
            )
        );
    }

    public function computeEmailRecipients( $inspection, $context ){
        $LOG = Logger::getLogger(__CLASS__);

        // Always include standard recipients
        // Send to:
        //   PI
        //   Lab Contacts
        //   Inspectors
        $recipient_arrays = $this->getRecipientEmailAddressesFromInspection( $inspection );
        $to_addresses = $recipient_arrays['lab_emails'];
        $cc_addresses = $recipient_arrays['inspector_emails'];

        //   "the PI and any other individuals sent the original inspection report email"
        // Problem: RSMS postInspection allows the inspector to selectively send email to PI, Contacts, and arbitrary addresses

        /* Find the latest inspection report email sent for this inspection, and use the same To/Cc settings
            PostInspection* message which references this inspection
        */
        $matches = $this->findLatestInspectionReportEmail($context);

        $LOG->info("Found " . count($matches) . " previous email(s) for $context.");
        if( count($matches) > 0 ){
            $LOG->info("Copying recipients from latest email");

            // Email(s) have been sent for this inspection before. Get the SAME recipients from the latest
            $old_recipients = $this->getRecipientsFromReportEmail($matches[0]);

            // Merge standard and old_recipients
            $old_to = explode(',', $old_recipients['to']);
            $to_addresses = $this->union($old_to, $to_addresses);

            $old_cc = explode(',', $old_recipients['cc']);
            $cc_addresses = $this->union($old_cc, $cc_addresses);
        }

        // Implode arrays to csv
        return $this->prepareRecipientsArray(
            implode(',', $to_addresses),
            implode(',', $cc_addresses)
        );
    }

    private function findLatestInspectionReportEmail( $context ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        $LOG->debug("Find latest inspection email for $context");

        $wc = '%"inspection_id":"' . $context->inspection_id . '"%';
        $mtypes = array(
            LabInspectionModule::$MTYPE_NO_DEFICIENCIES,
            LabInspectionModule::$MTYPE_DEFICIENCIES_FOUND,
            LabInspectionModule::$MTYPE_DEFICIENCIES_CORRECTED
        );

        $sql = "SELECT
                mq.key_id as message_id,
                mq.context_descriptor as context_descriptor,
                mq.sent_date as sent_date,
                eq.key_id as email_id,
                eq.recipients as recipients,
                eq.cc_recipients as cc_recipients
            FROM message_queue mq
            JOIN email_queue eq ON eq.message_id = mq.key_id
            WHERE mq.message_type IN (:m1, :m2, :m3)
                AND mq.context_descriptor LIKE :wildcard
            ORDER BY mq.sent_date DESC";

        $stmt = DBConnection::prepareStatement($sql);

        $stmt->bindValue(':m1', LabInspectionModule::$MTYPE_NO_DEFICIENCIES, PDO::PARAM_STR);
        $stmt->bindValue(':m2', LabInspectionModule::$MTYPE_DEFICIENCIES_FOUND, PDO::PARAM_STR);
        $stmt->bindValue(':m3', LabInspectionModule::$MTYPE_DEFICIENCIES_CORRECTED, PDO::PARAM_STR);

        $stmt->bindValue(':wildcard', $wc, PDO::PARAM_STR);

        if( $LOG->isTraceEnabled() ){
            $LOG->trace($sql);
        }

        if( $stmt->execute() ){
            $result = $stmt->fetchAll();
        }
        else{
            $LOG->error("Error matching previous email for $context");
            $LOG->error($stmt->errorInfo());
            $result = null;
        }

        $stmt = null;

        return $result;
    }

    public static function getInspectionReportLink($inspection_id){
        $urlBase = ApplicationConfiguration::get( ApplicationBootstrapper::CONFIG_SERVER_WEB_URL );
        $webRoot = WEB_ROOT;
        return "$urlBase$webRoot" . "views/inspection/InspectionConfirmation.php#/report?inspection=$inspection_id";
    }
}

?>
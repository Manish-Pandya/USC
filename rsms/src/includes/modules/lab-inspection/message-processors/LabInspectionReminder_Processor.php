<?php

/**
 * Processor for Lab Inspection reminder emails.
 * Message context is a Department, User, and Year.
 *
 * Inspection Reminders should trigger messages to only Lab staff (PI, Contact, etc)
 */
class LabInspectionReminder_Processor extends LabInspectionUpdatedMessage_Processor {

    public function getRecipientsDescription(){ return "PI, Contact"; }

    /**
     * Override email inference to omit inspector emails
     */
    protected function prepareRecipientsArray( $labstaff, $inspectors ){
        return array(
            'to' => $labstaff,
            'cc' => null
        );
    }
}

?>
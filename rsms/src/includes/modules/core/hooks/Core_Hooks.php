<?php

class Core_Hooks {

    /**
     * $params should contain 2 values:
     * [0] => saved inspection
     * [1] => Null if inspection is new; Previous value if updated
     */
    public static function after_inspection_saved( $params ){
        if( $params[1] != NULL ){
            $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

            // Inspection was updated
            $beforeSaved = $params[1];
            $afterSaved = $params[0];

            $LOG->trace("Firing hook after Inspection was updated: $afterSaved");

            // Detect if Inspection CAP was just Approved
            self::detectInspectionPlanApproval($beforeSaved, $afterSaved);
        }
    }

    /**
     * RSMS-752: Trigger email when EHS approves a CAP
     */
    private static function detectInspectionPlanApproval($beforeSaved, $afterSaved){
        // was unapproved if: CAP was submitted but inspection is NOT closed
        $previouslyUnapproved = $beforeSaved->getCap_submitted_date() != null && $beforeSaved->getDate_closed() == null;

        // is approved if closed
        $approvedAfterSave = $afterSaved->getDate_closed() != null;

        if( $previouslyUnapproved && $approvedAfterSave ){
            // This inspection was just approved
            $LOG->info("Inspection CAP was approved " . $afterSaved->getDate_closed());

            // Enqueue message
            $messenger = new Messaging_ActionManager();
            $messenger->enqueueMessages(
                CoreModule::$NAME,
                'LabInspectionApprovedCAP',
                array(
                    new LabInspectionReminderContext($afterSaved->getKey_id(), date('Y-m-d'))
                )
            );
        }
    }
}

?>
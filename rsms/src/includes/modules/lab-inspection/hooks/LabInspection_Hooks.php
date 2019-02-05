<?php

class LabInspection_Hooks {

    public static function after_inspection_report_message_queued($params){
        Scheduler::run( MessagingModule::$NAME );
    }

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

    public static function after_cap_submitted( &$inspection ) {
        if( isset($inspection) ){
            $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
            $LOG->debug("Post-submit hook for $inspection");

            // Verify that all corrective-actions have been completed
            $LOG->debug("Check status of all Corrective Actions");
            $allCapStatuses = $inspection->collectAllCorrectiveActionStatuses();

            if( count($allCapStatuses) == 1 && in_array(CorrectiveAction::$STATUS_COMPLETE, $allCapStatuses) ){
                // All CAPs are completed
                $LOG->info("All corrective actions in $inspection have been Completed");

                // Enqueue message
                self::enqueueLabInspectionReminderMessage($inspection->getKey_id(), LabInspectionModule::$MTYPE_CAP_SUBMITTED_ALL_COMPLETE);
            }

            if( in_array(CorrectiveAction::$STATUS_PENDING, $allCapStatuses) ){
                // At least one CAP is Pending
                $LOG->info("At least one corrective action in $inspection is Pending");

                // Enqueue message
                self::enqueueLabInspectionReminderMessage($inspection->getKey_id(), LabInspectionModule::$MTYPE_CAP_SUBMITTED_PENDING);
            }
        }
    }

    public static function after_cap_approved( &$inspection ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        if( isset($inspection) && $inspection->getDate_closed() != null ){
            $LOG->info("Inspection CAP was approved " . $inspection->getDate_closed());

            // Enqueue message
            self::enqueueLabInspectionReminderMessage($inspection->getKey_id(), LabInspectionModule::$MTYPE_CAP_APPROVED);
        }
    }

    /**
     * RSMS-752: Trigger email when EHS approves a CAP
     */
    private static function detectInspectionPlanApproval($beforeSaved, $afterSaved){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // was unapproved if: CAP was submitted but inspection is NOT closed
        $previouslyUnapproved = $beforeSaved->getCap_submitted_date() != null && $beforeSaved->getDate_closed() == null;

        // is approved if closed
        $approvedAfterSave = $afterSaved->getDate_closed() != null;

        if( $previouslyUnapproved && $approvedAfterSave ){
            // This inspection was just approved; trigger after_cap_approved hook
            self::after_cap_approved($afterSaved);
        }
    }

    private static function enqueueLabInspectionReminderMessage( $inspection_id, $mtype ){
        $messenger = new Messaging_ActionManager();
        $messenger->enqueueMessages(
            LabInspectionModule::$NAME,
            $mtype,
            array(
                new LabInspectionReminderContext($inspection_id, date('Y-m-d'))
            )
        );

    }
}

?>
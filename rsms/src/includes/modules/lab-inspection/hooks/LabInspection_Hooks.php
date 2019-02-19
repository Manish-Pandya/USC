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

    public static function after_save_lab_contact( &$user ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        $LOG->debug("Checking $user to ensure they are assigned to their Supervisor's Open Inspections");

        $supervisor = $user->getSupervisor();
        if( !$supervisor ){
            $LOG->error("User $user has no Supervisor!");
            return;
        }

        // Check supervisor's open inspections
        $inspections = $supervisor->getOpenInspections();
        $inspectionDao = new GenericDAO(new Inspection());
        foreach($inspections as $inspection){
            $isContact = false;
            foreach($inspection->getLabPersonnel() as $contact){
                if( $contact->getKey_id() == $user->getKey_id() ){
                    $isContact = true;
                    break;
                }
            }

            if( !$isContact ){
                // User is not assigned to this inspection; assign them
                $LOG->info("Assigning $user as Lab Contact to $inspection");
                $inspectionDao->addRelatedItems(
                    $user->getKey_id(),
                    $inspection->getKey_id(),
                    DataRelationship::fromArray(Inspection::$INSPECTION_LAB_PERSONNEL_RELATIONSHIP ));
            }
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
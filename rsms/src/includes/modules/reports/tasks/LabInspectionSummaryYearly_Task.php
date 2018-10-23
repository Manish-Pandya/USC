<?php

/**
 * Scheduled Task responsible for enqueuing messages to be sent
 * to Department Chair users notifying them that their Laboratory
 * Inspections Summary Report is nearly complete.
 */
class LabInspectionSummaryYearly_Task implements ScheduledTask {

    public static $MESSAGE_TYPE_NAME = 'LabInspectionSummaryYearly';

    public function getPriority(){
        return 0;
    }

    public function run(){
        $reportsManager = new Reports_ActionManager();

        // Get current year
        $currentYear = $reportsManager->getCurrentYear();

        // Send on Dec 1 of this year
        $sendOnDate = date('Y-m-d H:i:s', strtotime("12/01/$currentYear"));

        // Build context for each deparment
        $contexts = array();
        $departments = $reportsManager->getAllDepartmentInfo();
        foreach($departments as $dept){
            $contexts[] = new LabInspectionSummaryContext(
                $currentYear,
                $dept->getKey_id()
            );
        }

        // Prepare messages for delayed send
        $messenger = new Messaging_ActionManager();
        $enqueued = $messenger->enqueueMessages(
            ReportsModule::$NAME,
            self::$MESSAGE_TYPE_NAME,
            $contexts
        );

        return count($enqueued) . ' ' . self::$MESSAGE_TYPE_NAME . " messages have been enqueued";
    }
}

?>
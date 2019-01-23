<?php

/**
 * Scheduled Task responsible for enqueuing messages to be sent
 * to Department Chair users notifying them that their Laboratory
 * Inspections Summary Report is nearly complete.
 */
class LabInspectionSummaryReady_Task implements ScheduledTask {

    public static $COMPLETION_THRESHOLD = 80;

    public function getPriority(){
        return 0;
    }

    public function run(){
        // Determine which department chairs need notifying
        $contexts = $this->getContexts();

        // Prepare messages
        $messenger = new Messaging_ActionManager();
        $enqueued = $messenger->enqueueMessages(
            ChairReportModule::$NAME,
            ChairReportModule::$MTYPE_INSPECTION_SUMMARY_READY,
            $contexts
        );

        return count($enqueued) . " messages have been enqueued";
    }

    public function getContexts(){
        $LOG = Logger::getLogger(__CLASS__);

        /*
            Get all Department summary reports for Current Year,
            Calculate % completeness by PI
            Contexts are those which are 80% complete or more
        */

        $contexts = array();

        $reportsManager = new Reports_ActionManager();

        // Get current year
        $currentYear = $reportsManager->getCurrentYear();

        $LOG->info("Calculating department inspection rate for $currentYear");

        // Look up all departments (this includes Chair information)
        $departments = $reportsManager->getAllDepartmentInfo();
        $deptsWithChair = 0;
        $deptsWithoutChair = 0;

        // Get this year's report for each department
        foreach($departments as $dept){
            if( $dept->getChair_id() == null ){
                $deptsWithoutChair++;
                $LOG->debug("Department has no chair: " . $dept->getName());
                continue;
            }

            // Increment with-chair count (for logging)
            $deptsWithChair++;

            $inspectionSummaries = $reportsManager->getInspectionsSummaryReport($currentYear, $dept->getKey_id());

            // Calculate completion by PI

            // Reduce summaries to the status(es) per-PI
            $pi_summary = array();
            $pis_completed = 0;
            foreach( $inspectionSummaries as $insp ){
                $piId = $insp->getPrincipal_investigator_id();

                $prev = NULL;
                if( array_key_exists($piId, $pi_summary) ){
                    $prev = $pi_summary[$piId];
                }

                if( $prev ){
                    // This PI has a previous inspection which is completed,
                    //  so we can ignore this one;
                    continue;
                }

                // Determine if this inspection is done
                $isDone = false;

                // Based on Status
                switch( $insp->getInspection_status() ){
                    case "INCOMPLETE CAP":
                    case "OVERDUE CAP":
                    case "SUBMITTED CAP":
                    case "CLOSED OUT":
                        $isDone = true;
                        break;
                }

                $pi_summary[$piId] = $isDone;

                if( $isDone ){
                    $pis_completed++;
                }
            }

            $totalPis = count($pi_summary);

            $completion = ($pis_completed / $totalPis) * 100;

            $LOG->info($dept->getName() . " completion is $completion%");
            if( $completion >= self::$COMPLETION_THRESHOLD ){

                // Prepare message context object
                $contexts[] = new LabInspectionSummaryContext(
                    $currentYear,
                    $dept->getKey_id()
                );
            }
        }

        if( $deptsWithoutChair > 0 ){
            $LOG->warn("$deptsWithoutChair Departments have no assigned Department Chair user");
        }

        $LOG->info( count($contexts) . '/' . $deptsWithChair . ' departments with assigned Chair have inspected at least ' . self::$COMPLETION_THRESHOLD . "% PIs for $currentYear");

        return $contexts;
    }
}

?>
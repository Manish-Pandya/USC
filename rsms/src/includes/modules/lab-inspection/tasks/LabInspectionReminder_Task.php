<?php

class LabInspectionReminder_Task implements ScheduledTask {

    private $messenger;

    public function getPriority(){
        return 0;
    }

    public function run(){
        $this->messenger = new Messaging_ActionManager();

        $reminders = $this->getReminderInspections();
        $overdue   = $this->getOverdueCapInspections();
        $pending   = $this->getPendingCapInspections();

        // Enqueue Up-coming and Overdue reminders
        $msg = '';
        $msg .= $this->queueMessages($reminders, LabInspectionModule::$MTYPE_CAP_REMINDER_DUE);
        $msg .= $this->queueMessages($overdue, LabInspectionModule::$MTYPE_CAP_REMINDER_OVERDUE);
        $msg .= $this->queueMessages($pending, LabInspectionModule::$MTYPE_CAP_REMINDER_PENDING);

        return $msg;
    }

    private function queueMessages($contexts, $typeName){
        $enqueued = $this->messenger->enqueueMessages(
            LabInspectionModule::$NAME,
            $typeName,
            $contexts
        );

        return count($enqueued) . " $typeName messages have been enqueued. ";
    }

    public function getReminderInspections(){
        // TODO: SELECT * FROM inspection_status WHERE status = 'INCOMPLETE CAP'
        // Join to inspection to check due date (notification_date + 14)

        $sql = "SELECT
                insp_status.inspection_id,
                insp_status.inspection_status,
                DATE(insp.notification_date) as notification_date,
                DATE_ADD(DATE(insp.notification_date), INTERVAL 7 DAY) as reminder_date

            FROM inspection_status insp_status
            JOIN inspection insp ON insp.key_id = insp_status.inspection_id

            -- Constrain to unsubmitted CAPs (incomplete cap) with a reminder date (one week after notification) of today
            WHERE insp_status.inspection_status = 'INCOMPLETE CAP'
                AND CURDATE() = DATE_ADD(DATE(insp.notification_date), INTERVAL 7 DAY)";

        return $this->getContextObjects($sql);
    }

    public function getOverdueCapInspections(){
        // TODO: SELECT * FROM inspection_status WHERE status = 'OVERDUE CAP'
        $sql = "SELECT
                insp_status.inspection_id,
                insp_status.inspection_status,
                DATE(insp.notification_date) as notification_date,
                CURDATE() as reminder_date
            FROM inspection_status insp_status
            JOIN inspection insp ON insp.key_id = insp_status.inspection_id

            WHERE insp.schedule_year = YEAR(CURDATE())
                AND insp_status.inspection_status = 'OVERDUE CAP'
                AND (
                    -- Constrain to overdue CAPs with a reminder date (two weeks + one day after notification) of today
                    CURDATE() = DATE_ADD(DATE(insp.notification_date), INTERVAL 15 DAY)
                    OR
                    -- OR every week following the initial two week period
                    DATEDIFF(CURDATE(), DATE_ADD(DATE(insp.notification_date), INTERVAL 15 DAY)) % 7 = 0
                )";

        return $this->getContextObjects($sql);
    }

    public function getPendingCapInspections(){
        $STATUS_PENDING = CorrectiveAction::$STATUS_PENDING;
        $sql = "SELECT
                inspection.key_id as inspection_id,
                CURDATE() as reminder_date
            FROM inspection inspection
            JOIN inspection_status inspection_status ON inspection_status.inspection_id = inspection.key_id
            JOIN response response ON response.inspection_id = inspection.key_id
            LEFT OUTER JOIN deficiency_selection defsel ON defsel.response_id = response.key_id
            LEFT OUTER JOIN supplemental_deficiency supdef ON supdef.response_id = response.key_id
            JOIN corrective_action cap ON (
                cap.deficiency_selection_id IS NOT NULL AND cap.deficiency_selection_id = defsel.key_id
                OR
                cap.supplemental_deficiency_id IS NOT NULL AND cap.supplemental_deficiency_id = supdef.key_id
            )

            WHERE inspection.schedule_year = YEAR(CURDATE())
                AND inspection_status.inspection_status != 'CLOSED OUT'
                AND cap.status = '$STATUS_PENDING'
                AND CURDATE() > DATE(inspection.cap_submitted_date)
                AND DATEDIFF(CURDATE(), DATE(inspection.cap_submitted_date)) % 14 = 0

            GROUP BY inspection.key_id";

        return $this->getContextObjects($sql);
    }

    private function getContextObjects($sql){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        $stmt = DBConnection::prepareStatement($sql);
        $contexts = null;

        if( $stmt->execute() ){
            // Fetch as context objects
            // NOTE: FETCH_PROPS_LATE is required here because our target type sets properties via constructor
            //   Because PDO by default sets properties BEFORE executing constructor... we need to tell it to do it after
            $contexts = $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, 'LabInspectionReminderContext');
        }
        else{
            // TODO: HANDLE ERROR
            $LOG->error($stmt->errorInfo());
            throw new Exception('Error retrieving context objects');
        }

        // 'Close' the statement
        $stmt = null;

        if( $LOG->isTraceEnabled() ){
            $LOG->trace('Contexts:');
            $LOG->trace($contexts);
        }
        // Return context objects
        return $contexts;
    }
}

?>
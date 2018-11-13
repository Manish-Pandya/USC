<?php

class LabInspectionReminder_Task implements ScheduledTask {

    private $messenger;

    public function getPriority(){
        return 0;
    }

    public function run(){
        // 'LabInspectionReminderCAPDue' - one week before CAP due date, if not submitted
        $reminders = $this->getReminderInspections();

        $this->messenger = new Messaging_ActionManager();

        $reminders = $this->getReminderInspections();
        $overdue   = $this->getOverdueCapInspections();

        // Enqueue Up-coming and Overdue reminders
        $msg = '';
        $msg .= $this->queueMessages($reminders, CoreModule::$MTYPE_CAP_REMINDER_DUE);
        $msg .= $this->queueMessages($overdue, CoreModule::$MTYPE_CAP_REMINDER_OVERDUE);

        return $msg;
    }

    private function queueMessages($contexts, $typeName){
        $enqueued = $this->messenger->enqueueMessages(
            CoreModule::$NAME,
            $typeName,
            $contexts
        );

        return count($enqueued) . " $typeName messages have been enqueued. ";
    }

    private function getReminderInspections(){
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

    private function getOverdueCapInspections(){
        // TODO: SELECT * FROM inspection_status WHERE status = 'OVERDUE CAP'
        $sql = "SELECT
                insp_status.inspection_id,
                insp_status.inspection_status,
                DATE(insp.notification_date) as notification_date,
                DATE_ADD(DATE(insp.notification_date), INTERVAL 15 DAY) as reminder_date
            FROM inspection_status insp_status
            JOIN inspection insp ON insp.key_id = insp_status.inspection_id

            -- Constrain to overdue CAPs with a reminder date (two weeks + one day after notification) of today
            WHERE insp_status.inspection_status = 'OVERDUE CAP'
                AND CURDATE() = DATE_ADD(DATE(insp.notification_date), INTERVAL 15 DAY)";

        return $this->getContextObjects($sql);
    }

    private function getContextObjects($sql){
        $stmt = DBConnection::prepareStatement($sql);
        $contexts = null;

        if( $stmt->execute() ){
            // Fetch as context objects
            $contexts = $stmt->fetchAll(PDO::FETCH_CLASS, 'LabInspectionReminderContext');
        }
        else{
            // TODO: HANDLE ERROR
            throw new Exception('Error retrieving context objects');
        }

        // 'Close' the statement
        $stmt = null;

        // Return context objects
        return $contexts;
    }
}

?>
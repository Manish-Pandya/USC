<?php

class LabInspectionReminderContext implements MessageContext {
    public $inspection_id;
    public $reminder_date;

    public function __construct($id, $reminder_date){
        $this->inspection_id = $id;
        $this->reminder_date = $reminder_date;
    }

    public function setInspection_id($id){ $this->inspection_id = $id; }
    public function setReminder_date($reminder_date){ $this->reminder_date = $reminder_date; }
}

?>
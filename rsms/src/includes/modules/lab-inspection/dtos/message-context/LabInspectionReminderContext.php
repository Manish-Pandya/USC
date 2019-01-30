<?php

class LabInspectionReminderContext implements MessageContext {
    public $inspection_id;
    public $reminder_date;

    public function __construct($id = null, $reminder_date = null){
        $this->inspection_id = $id;
        $this->reminder_date = $reminder_date;
    }

    /** Magic Setter to prevent setting non-declared properties via PDO */
    public function __set($name, $val){}

    public function setInspection_id($id){ $this->inspection_id = $id; }
    public function setReminder_date($reminder_date){ $this->reminder_date = $reminder_date; }

    public function __toString(){
        return "[" . get_class($this) . " inspection_id:$this->inspection_id reminder_date:$this->reminder_date]";
    }
}

?>
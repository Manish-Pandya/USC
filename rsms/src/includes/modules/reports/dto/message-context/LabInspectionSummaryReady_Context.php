<?php

class LabInspectionSummaryReady_Context implements MessageContext {
    public $user_id;
    public $department_id;
    public $report_year;

    public function __construct($year, $user, $dept){
        $this->user_id = $user;
        $this->department_id = $dept;
        $this->report_year = $year;
    }
}

?>
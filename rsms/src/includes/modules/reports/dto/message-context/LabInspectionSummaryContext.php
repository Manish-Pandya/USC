<?php

class LabInspectionSummaryContext implements MessageContext {
    public $department_id;
    public $report_year;

    public function __construct($year, $dept){
        $this->department_id = $dept;
        $this->report_year = $year;
    }

    public function setDepartment_id($id){ $this->department_id = $id; }
    public function setReport_year($year){ $this->report_year = $year; }
}

?>
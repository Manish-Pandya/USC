<?php

class LabInspectionSummaryContext implements MessageContext {
    public $department_id;
    public $report_year;

    public function __construct($year, $dept){
        $this->department_id = $dept;
        $this->report_year = $year;
    }
}

?>
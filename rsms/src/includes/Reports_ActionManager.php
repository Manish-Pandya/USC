
<?php

class Reports_ActionManager extends ActionManager {

    /**
     * "Laboratory Inspections Summary Report" endpoint with required constraints
     * for Year and Department
     */
    public function getInspectionsSummaryReport($year, $department_id){
		$LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $LOG->debug("y:$year, d:$department_id");

        $dao = new LabInspectionSummaryReportDAO();
        return $dao->getInspections($year, $department_id);
    }
}
?>
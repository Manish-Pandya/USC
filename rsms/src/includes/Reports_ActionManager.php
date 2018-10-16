
<?php

class Reports_ActionManager extends ActionManager {

    /**
     * "Laboratory Inspections Summary Report" endpoint with required constraints
     * for Year and Department
     */
    public function getInspectionsSummaryReport($year, $department_id){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $year = $this->getValueFromRequest('year', $year);
        $department_id = $this->getValueFromRequest('department_id', $department_id);

        $LOG->debug("y:$year, d:$department_id");

        $dao = new LabInspectionSummaryReportDAO();
        return $dao->getInspections($year, $department_id);
    }

    /**
     * Get basic reporting details about all Departments
     */
    public function getAllDepartmentInfo(){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $dao = new LabInspectionSummaryReportDAO();
        $departments = $dao->getDepartmentDetails();

        return $departments;
    }

    /**
     * Get basic reporting details about a Department
     */
    public function getDepartmentInfo($department_id = NULL){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $department_id = $this->getValueFromRequest('department_id', $department_id);

        if( $department_id == NULL ){
            // Get department for current user
            $user = $this->getCurrentUser();
            $department_id = $user->getPrimary_department_id();
        }

        if( $department_id == NULL ){
            return new ActionError("No department was provided or mapped to this user", 400);
        }

        $dao = new LabInspectionSummaryReportDAO();
        $departments = $dao->getDepartmentDetails($department_id);

        return $departments[0];
    }
}
?>
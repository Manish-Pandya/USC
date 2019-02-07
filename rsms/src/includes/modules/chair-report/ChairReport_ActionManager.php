<?php

class ChairReport_ActionManager extends ActionManager {

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
        return $dao->getInspectionsReport($year, $department_id);
    }

    /**
     * Retrieves all departments which the user can review
     */
    public function getAllAvailableDepartments(){
        if( in_array('Admin', $this->getCurrentRoles()) ){
            // User is admin; get all Departments
            return $this->getAllDepartmentInfo();
        }
        else{
            // Non-admin user; just get their primary department
            return array(
                $this->getDepartmentInfo()
            );
        }
    }

    /**
     * Get basic reporting details about all Departments
     */
    public function getAllDepartmentInfo(){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $dao = new LabInspectionSummaryReportDAO();
        $min_year = $this->getMinimumReportYear();
        $departments = $dao->getDepartmentDetails(null, $min_year);

        return $departments;
    }

    /**
     * Get basic reporting details about a single Department
     */
    public function getDepartmentInfo($department_id = NULL){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $department_id = $this->getValueFromRequest('department_id', $department_id);

        if( $department_id == NULL ){
            // Get department for current user
            $user = $this->getCurrentUser();
            $department_id = $user->getPrimary_department_id();

            if( $department_id == NULL && $user->getPrincipalInvestigator() != null ){
                try{
                    // user is a PI and may not have a 'primary department' assigned
                    $department_id = $user->getPrincipalInvestigator()->getDepartments()[0]->getKey_id();
                }
                catch( Exception $err ){
                    return new ActionError("Unable to determine Department for this PI user", 400);
                }
            }
        }

        if( $department_id == NULL ){
            return new ActionError("No department was provided or mapped to this user", 400);
        }

        $min_year = $this->getMinimumReportYear();

        $dao = new LabInspectionSummaryReportDAO();
        $departments = $dao->getDepartmentDetails($department_id, $min_year);

        return $departments[0];
    }

    protected function getMinimumReportYear(){
        // RSMS-837: Allow Admins to view back to 2017; otherwise 2018
        if( in_array('Admin', $this->getCurrentRoles()) ){
            return 2017;
        }
        else{
            return 2018;
        }
    }
}
?>
<?php
class ChairReportSecurity {

    public static function userCanViewSummaryReport($year, $department_id){
        return ChairReportSecurity::userIsChairOfDepartment($department_id);
    }

    public static function userIsChairOfDepartment($department_id){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        if( CoreSecurity::userHasRoles($_SESSION['USER'], array('Admin')) ){
            $LOG->debug("User is Admin");
            return true;
        }

        if( !CoreSecurity::userHasRoles($_SESSION['USER'], array('Department Chair')) ){
            // User is not a department chair or admin
            $LOG->debug("User is not a Department Chair");
            return false;
        }

        if( $_SESSION['USER']->getPrimary_department_id() == $department_id ){
            // current user's primary department is deptarment_id
            $LOG->debug("Department Chair Users's primary department is $department_id");
            return true;
        }

        // current user is PI whose departments include: department_id
        $userPi = $_SESSION['USER']->getPrincipalInvestigator();
        if( isset($userPi) ){
            // Map user's PI's department IDs
            $user_dept_ids = array_map(
                function($d){ return $d->getKey_id(); },
                $userPi->getDepartments()
            );

            // Allow if requested dept ID is in users's list
            return in_array($department_id, $user_dept_ids);
        }

        return false;
    }
}
?>

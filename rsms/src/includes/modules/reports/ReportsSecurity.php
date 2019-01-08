<?php
class ReportsSecurity {

    public static function userIsChairOfDepartment($department_id){
        if( !CoreSecurity::userHasAnyRole($_SESSION['USER'], array('Admin', 'Department Chair')) ){
            // User is not a department chair or admin
            return false;
        }

        if( $_SESSION['USER']->getPrimary_department_id() == $department_id ){
            // current user's primary department is deptarment_id
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

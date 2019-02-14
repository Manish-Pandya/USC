<?php
class LabInspectionSecurity {

    public static function userCanViewInspection($id){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        // Get current user

        if( !isset($_SESSION['USER']) ){
            // No user
            return false;
        }

        $user = $_SESSION['USER'];

        // Is user admin or an Inspector?
        // FIXME: Centralize role names
        if( CoreSecurity::userHasAnyRole($user, array('Admin', 'Safety Inspector'))){
            // User is Admin or Inspector
            $LOG->debug("User is administrator or inspector");
            return true;
        }

        if( !isset( $id ) ){
            // Inspection doesn't exist
            // Check if user has non-admin roles
            return CoreSecurity::userHasAnyRole($user, array('Principal Investigator', 'Lab Contact'));
        }

        // Look up Inspection
        $dao = new GenericDAO(new Inspection());
        $inspection = $dao->getById($id);

        // Is the user the Department Chair over the inspection's PI's department?
        if( CoreSecurity::userHasRoles($user, array('Department Chair'))){
            // User is a dept. chair; do they preside over the related PI's dept?
            $dept_ids = array_map(
                function($d){ return $d->getKey_id(); },
                $inspection->getPrincipalInvestigator()->getDepartments()
            );

            // Defer dept check to ChairReportSecurity
            foreach($dept_ids as $dept){
                if(ChairReportSecurity::userIsChairOfDepartment($dept)){
                    $LOG->debug("User is Department Chair over PI's department(s): Matched dept #$dept");
                    return true;
                }
            }

            if( $LOG->isTraceEnabled() ){
                $LOG->trace("User is not the chair for any departments in: " . implode(', ', $dept_ids));
            }
        }

        // Is user the assigned PI?
        if( $inspection->getPrincipalInvestigator()->getUser()->getKey_id() == $user->getKey_id()){
            $LOG->debug("User is assigned PI");
            return true;
        }

        // Is user assigned personnel?
        $personnel_ids = array_map( function($u){ return $u->getKey_id(); }, $inspection->getLabPersonnel());
        if( in_array( $user->getKey_id(), $personnel_ids) ){
            $LOG->debug("User is assigned Personnel");
            return true;
        }

        return false;
    }

    public static function userCanViewPI( $piId ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // TODO: Is user this PI, or a subordinate?
        if( !isset($_SESSION['USER']) ){
            // No user
            return false;
        }

        if( CoreSecurity::userHasAnyRole($_SESSION['USER'], array('Admin', 'Safety Inspector'))){
            // User is Admin or Inspector
            $LOG->debug("User is administrator or inspector");
            return true;
        }

        if( $_SESSION['USER']->getSupervisor_id() == $piId ){
            // User is subordinate of requested PI
            return true;
        }

        $userPi = $_SESSION['USER']->getPrincipalInvestigator();
        if( isset($userPi) ){
            if( $userPi->getKey_id() == $piId ){
                // Current user is the requested PI
                return true;
            }
        }

        return false;
    }

    private static function _userCanSaveInspectionById($inspection_id){
        return LabInspectionSecurity::userCanViewInspection( $inspection_id );
    }

    public static function userCanSaveInspection($id){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        if( !isset($id) ){
            $LOG->debug("No ID parameter was provided; attempt to inspect request entity");

            // Is request body an inspection which this user can save?
            $inspectionToSave = JsonManager::decodeInputStream();
            if( isset($inspectionToSave) ){
                $id = $inspectionToSave->getKey_id();
                $LOG->debug("Request entity provided; extracted inspection key_id: $id");
            }
            else{
                $LOG->warn("No request entity was provided");
            }
        }

        if( isset($id) ){
            $LOG->debug("Checking Inspection key_id: $id");
            $LOG->trace("Deferring security precondition to _userCanSaveInspectionById");
            return LabInspectionSecurity::_userCanSaveInspectionById( $id );
        }

        // Nothing to save...
        $LOG->error("Unable to validate security precondition; no Inspection ID or Entity was provided");
        return false;
    }

    public static function userCanSaveCorrectiveAction(){
        // Is request body a corrective action which this user can save?
        $correctiveActionToSave = JsonManager::decodeInputStream();
        if( isset($correctiveActionToSave) ){
            // Get the deficiency (either Selection or Supplemental)
            $def = $correctiveActionToSave->getDeficiencySelection();
            if( !isset($def) ){
                $def = $correctiveActionToSave->getSupplementalDeficiency();
            }

            // Map defeciency to response to get the Inspection ID
            $inspection_id = $def->getResponse()->getInspection_id();

            // ensure they have access to the inspection
            return LabInspectionSecurity::_userCanSaveInspectionById($inspection_id);
        }

        // Nothing to save...
        return false;
    }

    public static function userCanDeleteCorrectiveAction(){
        // Is request body a DeficiencySelection which this user can save?
        $deficiencySelection = JsonManager::decodeInputStream();
        if( isset($deficiencySelection) ){
            // Get the deficiency (either Selection or Supplemental)

            // Map defeciency to response to get the Inspection ID
            $inspection_id = $deficiencySelection->getResponse()->getInspection_id();

            // ensure they have access to the inspection
            return LabInspectionSecurity::_userCanSaveInspectionById($inspection_id);
        }

        // Nothing to delete...
        return false;
    }

}
?>

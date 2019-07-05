<?php
class LabInspectionSecurity {

    private static function _get_inspection( &$id ){
        $dao = new GenericDAO(new Inspection());
        return $dao->getById($id);
    }

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
        $inspection = LabInspectionSecurity::_get_inspection( $id );

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
        $inspectionPi = $inspection->getPrincipalInvestigator();
        if( $inspectionPi->getUser()->getKey_id() == $user->getKey_id()){
            $LOG->debug("User is assigned PI");
            return true;
        }

        // Is user assigned personnel?
        $personnel_ids = array_map( function($u){ return $u->getKey_id(); }, $inspection->getLabPersonnel());
        if( in_array( $user->getKey_id(), $personnel_ids) ){
            $LOG->debug("User is assigned Personnel");
            return true;
        }

        // Is user PI's Personnel?
        $pi_personnel_ids = array_map( function($u){ return $u->getKey_id(); }, $inspectionPi->getLabPersonnel());
        if( in_array( $user->getKey_id(), $pi_personnel_ids) ){
            $LOG->debug("User is one of the Inspection's PI's Lab Personnel");
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

    public static function userCanSaveInspectionById($inspection_id){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        $user = $_SESSION['USER'];

        if( CoreSecurity::userIsAdmin($user) ){
            $LOG->debug("User is administrator");
            return true;
        }

        else if ( LabInspectionSecurity::userCanViewInspection( $inspection_id ) ){
            // get the inspection
            $inspection = LabInspectionSecurity::_get_inspection( $inspection_id );

            if( !$inspection->getIsArchived() ){
                // Verify that the both:
                //   this inspection is for the year prior
                //   current year's inspection has NOT yet been started

                // ==> A User May edit a non-archived Inspection until it is two years old OR another Inspection in Started in the following year (or later)

                $currentYear = date("Y");
                $lastYear = $currentYear - 1;

                // #1: Inspection's schedule_year can be no older than 1 year
                if( $inspection->getSchedule_year() < $lastYear ){
                    $LOG->debug("$inspection is older than $lastYear; too old to be edited");
                    return false;
                }

                // get all inspections from the years following this inspection (if any)
                $following_year = (int)$inspection->getSchedule_year() + 1;

                $pi = $inspection->getPrincipalInvestigator();
                $dao = new InspectionDAO();
                $inspectionsInNextYear = $dao->getPiInspectionsSince($pi->getKey_id(), $following_year);

                // #2: No other Inspection may be started after the parameter Inspection's year
                $startedInspectionId = null;
                foreach($inspectionsInNextYear as $insp ){
                    if( $insp->getDate_started() != null ){
                        // Found an inspection which has been started
                        $startedInspectionId = $insp->getKey_id();
                        break;
                    }
                }

                // Only allow if there is no other started Inspection, or the matched Started inspection is the parameter
                if( !isset($startedInspectionId) ){
                    $LOG->debug("No inspection for this PI has been started after $following_year");
                    return true;
                }
                else if( $startedInspectionId == $inspection_id ){
                    $LOG->debug("$inspection is the latest started inspection");
                    return true;
                }
                else {
                    $LOG->debug("At least one other inspection has been started after $following_year");
                    return false;
                }
            }
            else{
                // Inspection is still open
                $LOG->debug("$inspection is archived; deny user edits");
                return false;
            }
        }

        return false;
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
            $LOG->trace("Deferring security precondition to userCanSaveInspectionById");
            return LabInspectionSecurity::userCanSaveInspectionById( $id );
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
            return LabInspectionSecurity::userCanSaveInspectionById($inspection_id);
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
            return LabInspectionSecurity::userCanSaveInspectionById($inspection_id);
        }

        // Nothing to delete...
        return false;
    }

    public static function userCanEditProfile( $userId ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $user = $_SESSION['USER'];

        if( CoreSecurity::userHasAnyRole($user, array('Admin'))){
            // User is Admin
            $LOG->debug("User is administrator");
            return true;
        }

        if( !isset($userId) ){
            $LOG->debug("User is editing their own profile");
            return true;
        }

        if( $user->getKey_id() == $userId ){
            $LOG->debug("User is the profile being edited");
            return true;
        }

        return false;
    }

    public static function userCanRemoveDeficiencySelection( $deficiencyId = null, $inspectionId = null ){
        return self::inspectionIsNotArchived( $inspectionId );
    }

    /**
     * Determine if user can modify items related to an inspection
     */
    public static function userCanEditInspectionItem( $obj = null ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        $obj = $obj ?? JsonManager::decodeInputStream();

        // Get the Inspection ID from obj
        $inspectionId = null;

        // DeficiencySelection
        // SupplementalObservation, SupplementalRecommendation, SupplementalDeficiency
        if ( $obj instanceof SupplementalRecommendation
            || $obj instanceof SupplementalObservation
            || $obj instanceof SupplementalDeficiency
            || $obj instanceof DeficiencySelection )
        {
            $LOG->trace("Retrieving Inspection from " . get_class($obj));
            $inspectionId = $obj->getResponse()->getInspection_id();
        }

        // RecommendationRelation, ObservationRelation
        else if ( $obj instanceof RelationshipDto){
            $LOG->trace("Retrieving Inspection from RelationshipDto");
            $responseId = $obj->getMaster_id();
            $responseDao = new GenericDAO(new Response());
            $response = $responseDao->getById( $responseId );

            $inspectionId = $response->getInspection_id();
        }

        return self::inspectionIsNotArchived( $inspectionId );
    }

    public static function inspectionIsNotArchived( $inspectionId ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $inspectionDao = new InspectionDAO();
        $inspection = $inspectionDao->getById($inspectionId);

        if( !isset($inspection) ){
            // Cannot find associated Inspection!
            $LOG->error("Unable to find related Inspection");
            return false;
        }
        else if( $inspection->getIsArchived() ){
            $LOG->warn("Related Inspection is archived");
            return false;
        }

        // Allow only if inspection is not Archived
        $LOG->debug("Related Inspection is not archived");
        return true;
    }
}
?>

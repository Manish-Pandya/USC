<?php

/**
 * Processes a MergeAction to combine several Hazards into one.
 * This process involves the following steps:
 *
 * - Update PIHR and Hazard/Room relations of constituent hazards to reference the target hazard
 * - Delete the constituent hazards
 */
class MergeActionProcessor extends A_ActionProcessor {
    const STAT_HAZARD = 'Deleted Hazards';
    const STAT_HR_UPDATE     = 'Updated Hazard/Room Relations';
    const STAT_HR_DELETE     = 'Deleted Hazard/Room Relations';
    const STAT_PIHR_UPDATE   = 'Updated PI/Hazard/Room Relations';
    const STAT_PIHR_DELETE   = 'Deleted PI/Hazard/Room Relations';

    const SQL_DELETE_HAZARD = "DELETE FROM hazard WHERE key_id = :constituent_id;";

    const SQL_UPDATE_PIHR   = "UPDATE principal_investigator_hazard_room SET hazard_id = :target_id WHERE hazard_id = :constituent_id;";
    const SQL_DELETE_PIHR_IF_EXIST =
        "DELETE constituent_assignment
        FROM principal_investigator_hazard_room constituent_assignment
        JOIN principal_investigator_hazard_room target_assignment ON target_assignment.hazard_id = :target_id
        WHERE constituent_assignment.hazard_id = :constituent_id
          AND constituent_assignment.room_id = target_assignment.room_id
          AND constituent_assignment.principal_investigator_id = target_assignment.principal_investigator_id";

    const SQL_UPDATE_HR     = "UPDATE hazard_room SET hazard_id = :target_id WHERE hazard_id = :constituent_id;";
    const SQL_DELETE_HR_IF_EXIST =
        "DELETE constituent_assignment
        FROM hazard_room constituent_assignment
        JOIN hazard_room target_assignment ON target_assignment.hazard_id = :target_id
        WHERE constituent_assignment.hazard_id = :constituent_id
          AND constituent_assignment.room_id = target_assignment.room_id";


    function validate( A_HazardChangeAction &$action ): ActionProcessorResult {
        // Verify that Target hazard exists
        $targetHazard = $this->_get_hazard_by_id($action->hazard_id);
        if( !isset($targetHazard) ){
            return new ActionProcessorResult(false, "Target hazard #$action->hazard_id does not exist");
        }

        // Verify that Constituent hazards exist
        $constituent_ids = $this->_get_constituent_ids($action);
        $constituents = [];
        foreach($constituent_ids as $id){
            $result = $this->get_and_validate_constituent($id);
            if( $result instanceof Hazard ){
                $constituents[] = $result;
            }
            else if( $result instanceof ActionProcessorResult) {
                // Else this is an error and we should return it
                return $result;
            }
            else {
                return new ActionProcessorResult(false, "Unable to validate constituent hazard #$id");
            }
        }

        return new ActionProcessorResult(true);
    }

    private function get_and_validate_constituent( int $id ){
        $hazard = $this->_get_hazard_by_id($id);

        if( !isset($hazard) ){
            return new ActionProcessorResult(false, "Constituent hazard #$id does not exist");
        }

        // Ensure hazard has no children
        $children = $hazard->getSubHazards();
        if( !empty( $children ) ){
            // Hazard has children. Allow reattempt in case children are deleted by later processes
            $describe_children = implode(', ', $children);
            return new ActionProcessorResult(false, "Hazard $hazard has children (" . count($children) . ") which must be deleted first: $describe_children", true);
        }

        // Ensure hazard has no other references
        // Disallow reattempt on errors, as the following are beyond the scope of HazardChangeManagement
        if( $hazard->getChecklist() != null ){
            return new ActionProcessorResult(false, "Hazard $hazard has associated Checklist: " . $hazard->getChecklist(), false);
        }

        if( !empty($hazard->getInspectionRooms()) ){
            $describe_inpection_rooms = implode(', ', $hazard->getInspectionRooms());
            return new ActionProcessorResult(false, "Hazard $hazard has associated Inspection-Rooms: $describe_inpection_rooms", false);
        }

        if( !empty($hazard->getPrincipalInvestigators()) ){
            $describe_pis = implode(', ', $hazard->getPrincipalInvestigators());
            return new ActionProcessorResult(false, "Hazard $hazard has associated Principal Investigators: $describe_pis", false);
        }

        // OK
        return $hazard;
    }

    function perform( A_HazardChangeAction &$action ): ActionProcessorResult {
        $targetHazard = $this->_get_hazard_by_id($action->hazard_id);
        $constituent_ids = $this->_get_constituent_ids($action);
        $constituents = [];
        foreach($constituent_ids as $id ){
            $constituents[] = $this->get_and_validate_constituent($id);
        }

        foreach( $constituents as $constituent ){
            // Update Hazard/Room assignments
            $assignments = $this->_get_constituent_hazard_room_assignments( $action );
            if( !empty($assignments) ){
                // Delete HR items which will collide upon update
                $deleted_hr_count = $this->execute_sql( self::SQL_DELETE_HR_IF_EXIST, $constituent->getKey_id(), $targetHazard->getKey_id() );
                $this->stat( self::STAT_HR_DELETE, $deleted_hr_count);

                // Update remaining HR items
                $updated_hr_count = $this->execute_sql( self::SQL_UPDATE_HR, $constituent->getKey_id(), $targetHazard->getKey_id() );
                $this->stat( self::STAT_HR_UPDATE, $updated_hr_count);
            }

            // Update PI/Hazard/Room assignments
            $assignments = $this->_get_constituent_pi_hazard_room_assignments( $action );
            if( !empty($assignments) ){
                // Delete PIHR items which will collide upon update
                $deleted_pihr_count = $this->execute_sql( self::SQL_DELETE_PIHR_IF_EXIST, $constituent->getKey_id(), $targetHazard->getKey_id() );
                $this->stat( self::STAT_PIHR_DELETE, $deleted_pihr_count);

                // Update remaining PIHR items
                $updated_pihr_count = $this->execute_sql( self::SQL_UPDATE_PIHR, $constituent->getKey_id(), $targetHazard->getKey_id() );
                $this->stat( self::STAT_PIHR_UPDATE, $updated_pihr_count);
            }

            // Delete hazard
            $deleted_hazard_count = $this->execute_sql( self::SQL_DELETE_HAZARD, $constituent->getKey_id(), NULL );
            $this->stat( self::STAT_HAZARD, $deleted_hazard_count);
        }

        return new ActionProcessorResult(true);
    }

    private function execute_sql( $sql, $constituent_id, $target_id ){
        $LOG = LogUtil::get_logger(TASK_NUM, __CLASS__, __FUNCTION__);
        $LOG->trace("\t$sql");

        $stmt = DBConnection::prepareStatement($sql);
        $stmt->bindValue('constituent_id', $constituent_id);

        if( isset($target_id) ){
            $stmt->bindValue('target_id', $target_id);
        }

        if( $stmt->execute() ){
            return $stmt->rowCount();
        }
        else {
			$error = $stmt->errorInfo();
			$resultError = new QueryError($error);
            throw new Exception("Could not execute \"$sql\" on [constituent_id:$constituent_id | target_id:$target_id]: " . $resultError->getMessage());
        }
    }

    function verify( A_HazardChangeAction &$action ): bool {
        // Verify that no pi/hazard/room relations exist for constituent IDs
        $assignments = $this->_get_constituent_hazard_room_assignments( $action );
        if( !empty($assignments) ){
            throw new Exception("Action did not result in updating of related hazard/room assignments");
        }

        // Verify that no hazard/room relations exist for constituent IDs
        $assignments = $this->_get_constituent_pi_hazard_room_assignments( $action );
        if( !empty($assignments) ){
            throw new Exception("Action did not result in updating of related pi/hazard/room assignments");
        }

        // Verify that constituent hazards do not exist
        $ids = $this->_get_constituent_ids($action);
        foreach($ids as $id){
            $h = $this->_get_hazard_by_id($id);
            if( isset($h) && $h != NULL ){
                LogUtil::get_logger(TASK_NUM, __CLASS__, __FUNCTION__)->error($h);
                throw new Exception("Action did not result in the deletion of constituent: $h");
            }
        }

        return true;
    }

    private function _get_hazard_by_id( int $id ){
        $hazard = QueryUtil::selectFrom($this->meta->hazard)
            ->where($this->meta->f_id, '=', $id)
            ->getOne();

        return $hazard;
    }

    private function _get_constituent_ids( MergeAction &$action ){
        // filter target ID from constituent list
        return array_filter( $action->constituent_ids, function($id) use ($action){
            return $id !== $action->hazard_id;
        });
    }

    private function _get_constituent_pi_hazard_room_assignments( MergeAction &$action ){
        $ids = $this->_get_constituent_ids($action);

        return QueryUtil::selectFrom( new PrincipalInvestigatorHazardRoomRelation() )
            ->where(Field::create('hazard_id', 'principal_investigator_hazard_room'), 'IN', $ids)
            ->getAll();
    }

    private function _get_constituent_hazard_room_assignments( MergeAction &$action ){
        $ids = $this->_get_constituent_ids($action);

        return QueryUtil::select( '*', 'hazard_room', stdClass::class )
            ->where(Field::create('hazard_id', 'hazard_room'), 'IN', $ids)
            ->getAll();
    }
}
?>

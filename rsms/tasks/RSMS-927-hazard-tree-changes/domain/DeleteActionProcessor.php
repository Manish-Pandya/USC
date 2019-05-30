<?php

class DeleteActionProcessor extends A_ActionProcessor {
    const STAT_HAZARD = 'Deleted Hazards';
    const STAT_HR     = 'Deleted Hazard/Room Relations';
    const STAT_PIHR   = 'Deleted PI/Hazard/Room Relations';

    const SQL_DELETE_HAZARD = "DELETE FROM hazard WHERE key_id = :hazard_id;";
    const SQL_DELETE_PIHR   = "DELETE FROM principal_investigator_hazard_room WHERE hazard_id = :hazard_id;";
    const SQL_DELETE_HR     = "DELETE FROM hazard_room WHERE hazard_id = :hazard_id;";

    function validate( Action &$action ): ActionProcessorResult {
        // Get the hazard to delete, by both ID AND NAME
        // This redundant requirement acts as a half-baked confirmation
        $hazard = $this->_get_hazard($action);

        if( !$hazard ){
            // Check if hazard exists with ID
            $hazard_by_id = $this->_get_hazard_by_id($action);
            $msg = null;
            if( $hazard ){
                // Hazard does exist, but the expected name is different.
                $msg = "Hazard with ID #$action->hazard_id does not have expected name. [Expected='$action->hazard_name' | Actual='" . $hazard->getName() . "'";
            }
            else {
                // Hazard does not exist
                $msg = "Hazard with ID #$action->hazard_id and name '$action->hazard_name' does not exist";
            }

            return new ActionProcessorResult(false, $msg, false);
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

        return new ActionProcessorResult(true);
    }

    function perform( Action &$action ): ActionProcessorResult {
        $LOG = LogUtil::get_logger(TASK_NUM, __CLASS__, __FUNCTION__);

        // Get the hazard
        $hazard = $this->_get_hazard($action);
        $hazard_id = $hazard->getKey_id();
        $statements = array();

        try {
            if( !empty($hazard->getRooms()) ){
                // Delete Room assignments
                $deleted_hr_count = $this->execute_sql( self::SQL_DELETE_HR, $hazard_id );
                $this->stat( self::STAT_HR, $deleted_hr_count);
            }

            $assignments = $this->_get_pi_hazard_room_assignments( $action );
            if( !empty($assignments) ){
                // Delete PI/Hazard/Room assignments
                $deleted_pihr_count = $this->execute_sql( self::SQL_DELETE_PIHR, $hazard_id );
                $this->stat( self::STAT_PIHR, $deleted_pihr_count);
            }

            // Delete Hazard
            $deleted_hazard_count = $this->execute_sql(self::SQL_DELETE_HAZARD, $hazard_id);
            $this->stat( self::STAT_HAZARD, $deleted_hazard_count);

            return new ActionProcessorResult(true, "Deleted $hazard and room relations");
        }
        catch( Exception $e ){
            return new ActionProcessorResult(false, "Failed to delete $hazard: " . $e->getMessage());
        }
    }

    private function execute_sql( $sql, &$hazard_id ){
        $LOG = LogUtil::get_logger(TASK_NUM, __CLASS__, __FUNCTION__);
        $LOG->trace("\t$sql");

        $stmt = DBConnection::prepareStatement($sql);
        $stmt->bindValue('hazard_id', $hazard_id);

        if( $stmt->execute() ){
            return $stmt->rowCount();
        }
        else {
			$error = $stmt->errorInfo();
			$resultError = new QueryError($error);
            throw new Exception("Could not execute \"$sql\" on hazard_id:$hazard_id: " . $resultError->getMessage());
        }
    }

    function verify( Action &$action ): bool {
        $hazard = $this->_get_hazard($action);
        if( $hazard != null ){
            throw new Exception("Action did not result in deletion of $hazard");
        }

        $assignments = $this->_get_pi_hazard_room_assignments($action);
        if( !empty($assignments) ){
            throw new Exception("Action did not result in deletion of related pi/hazard/room assignments");
        }

        $assignments = $this->_get_hazard_room_assignments($action);
        if( !empty($assignments) ){
            throw new Exception("Action did not result in deletion of related hazard/room assignments");
        }

        return true;
    }

    private function _get_hazard( DeleteAction &$action ){
        $hazard = QueryUtil::selectFrom($this->meta->hazard)
            ->where($this->meta->f_id, '=', $action->hazard_id)
            ->where($this->meta->f_name, '=', $action->hazard_name)
            ->getOne();

        return $hazard;
    }

    private function _get_hazard_by_id( DeleteAction &$action ){
        $hazard = QueryUtil::selectFrom($this->meta->hazard)
            ->where($this->meta->f_id, '=', $action->hazard_id)
            ->getOne();

        return $hazard;
    }

    private function _get_pi_hazard_room_assignments( DeleteAction &$action ){
        return QueryUtil::selectFrom( new PrincipalInvestigatorHazardRoomRelation() )
            ->where(Field::create('hazard_id', 'principal_investigator_hazard_room'), '=', $action->hazard_id)
            ->getAll();
    }

    private function _get_hazard_room_assignments( DeleteAction &$action ){
        return QueryUtil::select( '*', 'hazard_room', stdClass::class )
            ->where(Field::create('hazard_id', 'hazard_room'), '=', $action->hazard_id)
            ->getAll();
    }
}
?>

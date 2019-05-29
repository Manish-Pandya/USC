<?php

class DeleteActionProcessor extends A_ActionProcessor {

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
        $statements = array();

        if( !empty($hazard->getRooms()) ){
            // Delete Room assignments
            $statements[] = "DELETE FROM hazard_room WHERE hazard_id = :hazard_id;";
        }

        $assignments = $this->_get_pi_hazard_room_assignments( $action );
        if( !empty($assignments) ){
            // Delete PI/Hazard/Room assignments
            $statements[] = "DELETE FROM principal_investigator_hazard_room WHERE hazard_id = :hazard_id;";
        }

        // Delete Hazard
        $statements[] = "DELETE FROM hazard WHERE key_id = :hazard_id;";

        $sql = implode("\n\t", $statements);
        $LOG->debug("Executing SQL for $action:\n\t$sql");

        $stmt = DBConnection::prepareStatement($sql);
        $stmt->bindValue('hazard_id', $hazard->getKey_id());

        if( $stmt->execute() ){
            // Success
            return new ActionProcessorResult(true, "Deleted $hazard and room relations");
        }
        else {
            // Error
			$error = $stmt->errorInfo();
			$resultError = new QueryError($error);
            return new ActionProcessorResult(false, "Failed to delete $hazard: " . $resultError->getMessage());
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

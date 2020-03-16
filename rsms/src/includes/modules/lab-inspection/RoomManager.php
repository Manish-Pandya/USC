<?php
class RoomManager {
    private static $INSTANCE;

    public static function get(){
        if( !self::$INSTANCE ){
            self::$INSTANCE = new RoomManager();
        }

        return self::$INSTANCE;
    }

    private $roomDao;

    private function __construct(){
        $this->roomDao = new RoomDAO();
        $this->assignmentDao = new GenericDAO(new UserRoomAssignment());
        $this->roleDao = new RoleDAO();
        $this->userDao = new UserDAO();
    }

    /**
     * Retrieve all Users which are assigned the Role defined by the specified RoomType.
     */
    public function getAssignableUsers( RoomType $type ) {
        $role_name = $type->getAssignable_to();

        // If type is not assignable, then return an empty list
        if( !isset($role_name) ){
            return [];
        }

        $role = $this->roleDao->getByName( $role_name );

        // Find all users with the applicable role
        return $this->userDao->getUsersWithRole( $role );
    }

    public function getRoomAssignments( Room &$room ){
        // Find assignment type
        $roomType = RoomType::of($room->getRoom_type() );

        // Assignment model depends on room type
        switch( $roomType->getAssignable_to() ){
            // Principal Investigators
            case LabInspectionModule::ROLE_PI:{
                // Return PI/Rooms (including inactive PIs)
                return $this->roomDao->getRoomPIs($room->getKey_id());
            }

            // User / Teaching Lab Contact
            case LabInspectionModule::ROLE_TEACHING_LAB_CONTACT: {
                // Return User assignments
                // TODO: Limit to assignable_to?
                return $this->roomDao->getRoomAssignedUsers($room->getKey_id(), $roomType->getAssignable_to());
            }

            // Non-assignable rooms
            case null: {
                return [];
            }

            default: {
                throw new Exception("Invalid assignment model for $target_type" );
            }
        }
    }

    public function saveRoom(Room $roomChanges){
        $LOG = LogUtil::get_logger( __CLASS__, __FUNCTION__ );

        if( $roomChanges === NULL ){
            throw new Exception("No room changes specified");
        }

        $room = null;
        if( $roomChanges->hasPrimaryKeyValue() ){
            // Update existing room
            $room = $this->roomDao->getById($roomChanges->getKey_id());
            if( $room == null ){
                throw new NotFoundException("No such room " . $roomChanges->getKey_id());
            }

            $LOG->info("Update existing room $room");
        }
        else{
            // Create new room
            $room = new Room();
            $room->setRoom_type( $roomChanges->getRoom_type());
            $LOG->info("Create new Room");
        }

        ////////////////////////////
        // Validate Room Type
        if( $roomChanges->getRoom_type() == null ){
            // No room type specified; default to Research Lab
            $roomChanges->setRoom_type( RoomType::RESEARCH_LAB );
            $LOG->warn("No RoomType specified; defaulting to " . $roomChanges->getRoom_type());
        }

        if( $roomChanges->getRoom_type() != $room->getRoom_type() ){
            $LOG->info("Validating change of Room Type: " . $room->getRoom_type() . ' => ' . $roomChanges->getRoom_type());

            // Attempting change of Room type
            // Room Types may change freely as long as it is assigned to no one
            $new_assignments = $this->getRoomAssignments($roomChanges); // Get assignments for the Incoming Room
            $old_assignments = $this->getRoomAssignments($room);        // Get assignments for the saved Room

            if( !empty($new_assignments) || !empty($old_assignments) ){
                $existing_type = RoomType::of($room->getRoom_type());
                $target_type = RoomType::of($roomChanges->getRoom_type());

                // Assigned Room may change type only if the target type supports the same type of Assignment
                // TODO: We may want to consider more explicitly defining the assignment model to check compatibility
                if( $existing_type->getAssignable_to() != $target_type->getAssignable_to() ){
                    // Cannot change this Room's type because the target type conflicts with existing assignemnt(s)
                    throw new IncompatibleRoomTypeException(
                        'Room ' . $room->getName()
                        . " has existing assignments which are incompatible with the '" . $target_type->getLabel() . "' room type");
                }
                // else OK to change type
                $LOG->info("Changing RoomType - existing assignments are compatible");
            }
            // else OK to change type
            $LOG->info("Changing RoomType - no assignments to this room exist");
        }
        // else room type isn't changing

        ////////////////////////////
        // Update Assignments
        $updatedAssignments;
        $target_type = RoomType::of($roomChanges->getRoom_type());

        // Assignment model depends on room type
        switch( $target_type->getAssignable_to() ){
            // Principal Investigators
            case LabInspectionModule::ROLE_PI:{
                $updatedAssignments = $this->updateAssignedPrincipalInvestigators($roomChanges, $room);
                break;
            }

            // User / Teaching Lab Contact
            case LabInspectionModule::ROLE_TEACHING_LAB_CONTACT: {
                $updatedAssignments = $this->updateAssignedUsers($roomChanges, $room, LabInspectionModule::ROLE_TEACHING_LAB_CONTACT);
                break;
            }

            // Non-assignable rooms
            case null: {
                $updatedAssignments = [];
                break;
            }

            default: {
                throw new Exception("Invalid assignment model for $target_type" );
            }
        }

        $LOG->info("Saving... $roomChanges");
        $room = $this->roomDao->save($roomChanges);

        // Retrieve active+inactive PIs now assigned to this room
        // Set PIs into $room (as DTO), because the lazy-loaded accessor will only include active
        $room->setPrincipalInvestigators(
            $this->roomDao->getRoomPIs($room->getKey_id())
        );

        $LOG->info("Saved $room");
        return $room;
    }

    protected function updateAssignedUsers( Room &$roomChanges, Room &$existingRoom, string $rolename ){
        $LOG = LogUtil::get_logger( __CLASS__, __FUNCTION__ );

        if( !is_array( $roomChanges->getUserAssignments() ) ){
            // Room is not assigned
            return false;
        }

        $LOG->info("Update '$rolename' assignments to $roomChanges");

        // First, validate any user removals for an existing Room
        $currentRoomAssignments = [];
        if( $existingRoom->hasPrimaryKeyValue() ){
            $currentRoomAssignments = $this->getRoomAssignments($existingRoom);
        }

        $validation = $this->_before_save_room_check_room_assignments(
            $existingRoom,
            $currentRoomAssignments ?? [],
            $roomChanges->getUserAssignments()
        );

        if( !$validation->valid ){
            throw new Exception("Cannot modify assignments to Room $existingRoom:" . implode(', ', $validation->errors));
        }

        if( $validation->remove_ids ){
            // Remove assignments which are not referenced
            // They have keys, so:
            $toRemove = QueryUtil::selectFrom( new UserRoomAssignment() )
                ->where(Field::create('user_id', UserRoomAssignment::TABLE_NAME), 'IN', $validation->remove_ids)
                ->where(Field::create('role_name', UserRoomAssignment::TABLE_NAME), '=', $rolename)
                ->where(Field::create('room_id', UserRoomAssignment::TABLE_NAME), '=', $roomChanges->getKey_id())
                ->getAll();

            foreach($toRemove as $removeAssignment) {
                $LOG->info("Deleting $removeAssignment");
                $this->assignmentDao->deleteById($removeAssignment->getKey_id());
            }
        }

        // Add any new
        foreach($validation->add_ids as $user_id){
            $assignment = new UserRoomAssignment();
            $assignment->setUser_id( $user_id );
            $assignment->setRoom_id( $roomChanges->getKey_id() );
            $assignment->setRole_name( $rolename );

            $assignment = $this->assignmentDao->save($assignment);
            $LOG->info("Saved $assignment");
        }

        return true;
    }

    protected function updateAssignedPrincipalInvestigators( Room &$roomChanges, Room &$existingRoom ){
        $LOG = LogUtil::get_logger( __CLASS__, __FUNCTION__ );

        if( !is_array( $roomChanges->getPrincipalInvestigators() ) ){
            // PIs are not specified; nothing to do
            return false;
        }

        $LOG->debug($roomChanges);

        // First, validate any PI removals for an existing Room
        $currentRoomPIs = [];
        if( $existingRoom->hasPrimaryKeyValue() ){
            $currentRoomPIs = $this->roomDao->getRoomPIs($existingRoom->getKey_id());    // Retrieve active+inactive PI assignments
        }

        $canSaveRoom = $this->_before_save_room_check_room_pis(
            $existingRoom,
            $currentRoomPIs ?? [],
            $roomChanges->getPrincipalInvestigators()
        );

        if( !$canSaveRoom ){
            throw new HazardsInRoomException("One or more PIs have Hazards assigned to room " . $existingRoom->getKey_id() );
        }

        if( $existingRoom->getPrincipalInvestigators() != null ){
            // Remove any existing
            foreach ($existingRoom->getPrincipalInvestigators() as $child){
                $this->roomDao->removeRelatedItems(
                    $child->getKey_id(),
                    $existingRoom->getKey_id(),
                    DataRelationship::fromArray(Room::$PIS_RELATIONSHIP)
                );
            }
        }

        // Add any new
        foreach($roomChanges->getPrincipalInvestigators() as $pi){
            if(gettype($pi) == "array"){
                $piId = $pi["Key_id"];
            }else{
                $piId = $pi->getKey_id();
            }

            $this->roomDao->addRelatedItems(
                $piId,
                $existingRoom->getKey_id(),
                DataRelationship::fromArray(Room::$PIS_RELATIONSHIP)
            );
        }

        return true;
    }

    public function validateRoomPIUnassignments($room, Array $removePiIds = null){
        $LOG = LogUtil::get_logger( __CLASS__, __FUNCTION__ );
        $LOG->debug("Validating room unassignment: $room");

        // Remove this room assignment
        // First, check if this room has any hazards in it
        $hazardRoomRelations = $room->getHazard_room_relations();

        if( empty($hazardRoomRelations) ){
            // No hazards in this room; nothing more to check
            $LOG->info("Room has no hazards");
            return true;
        }
        else {
            // This room has hazards assigned to it
            $LOG->info("Room has hazard assignments");

            // Map relations to their PI IDs
            $piAssignments = array_unique(
                array_map(function($rel){
                    return $rel->getPrincipal_investigator_id();
                }, $hazardRoomRelations)
            );

            // Verify that the specified PIs have no hazards in this room
            if( isset($removePiIds) && !empty($removePiIds) ) {
                $LOG->info("Checking hazard assignments for PIs: " . implode(', ', $removePiIds));

                $piAssignments = array_filter($piAssignments, function($assignedPiId) use ($removePiIds){
                    return in_array($assignedPiId, $removePiIds);
                });

                if( empty($piAssignments) ){
                    // The specified PIs have no hazards assigned to this room
                    $LOG->info("PIs have no assignments in room: " . implode(', ', $removePiIds));
                    return true;
                }
            }

            $LOG->error("Cannot remove Room assignment: PIs (" . implode(', ', $piAssignments) . ") have Hazards assigned to $room");
            return false;
        }
    }

    protected function _before_save_room_check_room_pis(Room &$room, Array $oldPIs, Array $newPIs){
        $LOG = LogUtil::get_logger( __CLASS__, __FUNCTION__ );

        $getIds = function($pi){
            if(is_array($pi) )
                return $pi['Key_id'];
            return $pi->getKey_id();
        };

        $LOG->info("Verifying room changes...");
        $existingPiIds = array_map($getIds, $oldPIs);
        $LOG->debug("Existing PIs: " . implode(', ', $existingPiIds));

        $incomingPiIds = array_map($getIds, $newPIs);
        $LOG->debug("Incoming PIs: " . implode(', ', $incomingPiIds));

        $diff = $this->diff( $existingPiIds, $incomingPiIds );
        $removingPiIds = $diff->remove_ids;
        if( !empty($removingPiIds) ){
            $LOG->info("Validate unassignment of PIs: " . implode(', ', $removingPiIds));
            return $this->validateRoomPIUnassignments($room, $removingPiIds);
        }

        $LOG->info("No PIs are being unassigned");
        return true;
    }

    protected function _before_save_room_check_room_assignments(Room $room, Array $oldAssignments, Array $newAssignments){
        $LOG = LogUtil::get_logger( __CLASS__, __FUNCTION__ );

        // map to user IDs
        $getIds = function($assignment){
            if(is_array($assignment) )
                return $assignment['User_id'];
            return $assignment->getUser_id();
        };

        $existingIds = array_map($getIds, $oldAssignments);
        $incomingIds = array_map($getIds, $newAssignments);
        $diff = $this->diff( $existingIds, $incomingIds );

        // No validation for removals necessary
        $diff->valid = true;

        // Validate that target users have the proper Role
        $rtype = RoomType::of($room->getRoom_type());
        $role = $this->roleDao->getByName( $rtype->getAssignable_to() );
        foreach( $diff->add_ids as $userId ){
            if( !$this->userDao->userHasRole($userId, $role->getKey_id()) ){
                // User does not have the required role and cannot be assigned
                $diff->valid = false;
                $diff->errors = $diff->errors ?? [];
                $diff->errors[] = "User $userId does not have required role: " . $role->getName();
            }
        }

        return $diff;
    }

    protected function diff(Array $oldIds, Array $newIds){
        $diff = new stdClass();
        $diff->remove_ids = array_diff($oldIds, $newIds);
        $diff->add_ids    = array_diff($newIds, $oldIds);
        return $diff;
    }
}
?>

<?php
class TestPrincipalInvestigatorHazardRoomRelationDAO implements I_Test {
    const IS_SHARED = true;
    const IS_NOT_SHARED = false;
    const IS_OTHER = true;
    const IS_NOT_OTHER = false;
    const IS_STORED = true;
    const IS_NOT_STORED = false;

    public function setup(){
        $this->pihrDao = new PrincipalInvestigatorHazardRoomRelationDAO();
        $this->hazardDao = new GenericDAO(new Hazard());
    }

    public function before__createTestData(){
        $actionmanager = new ActionManager();

        // Create Users
        $this->test_user = ReferenceData::create_user( $actionmanager,
            "TestUserFirstName", "TestUserLastName", "test@email.com");

        $this->test_shared_user_1 = ReferenceData::create_user( $actionmanager,
            "SharedUser1FirstName", "SharedUser1LastName", "shared-user-1@email.com");
        $this->test_shared_user_2 = ReferenceData::create_user( $actionmanager,
            "SharedUser2FirstName", "SharedUser2LastName", "shared-user-2@email.com");

        // Create PIs
        $piDao = new PrincipalInvestigatorDAO();
        $this->test_pi = ReferenceData::create_pi( $piDao, $this->test_user->getKey_id() );
        $this->test_shared_pi_1 = ReferenceData::create_pi( $piDao, $this->test_shared_user_1->getKey_id() );
        $this->test_shared_pi_2 = ReferenceData::create_pi( $piDao, $this->test_shared_user_2->getKey_id() );

        // Create a Room
        $this->test_room = ReferenceData::create_room( $actionmanager, "Test Room", true );

        // Create a Shared Room
        $this->test_shared_room = ReferenceData::create_room( $actionmanager, "Test Shared Room", true );

        // Create an unused Room
        $this->test_room_unused = ReferenceData::create_room( $actionmanager, "Unused Room", true );

        // Create a Hazard
        $this->test_hazard = ReferenceData::create_hazard(
            $actionmanager,
            "Test Hazard",
            1, // biological hazards
            true
        );

        // Assign the PI a Room
        ReferenceData::assign_room( $actionmanager, $this->test_pi, $this->test_room );

        // Assign shared room
        ReferenceData::assign_room( $actionmanager, $this->test_shared_pi_1, $this->test_shared_room );
        ReferenceData::assign_room( $actionmanager, $this->test_shared_pi_2, $this->test_shared_room );

        // Assign the PI Hazards in the Room
        ReferenceData::assign_hazard( $this->pihrDao,
            $this->test_pi, $this->test_hazard, $this->test_room );

        // Assign shared hazard/rooms
        ReferenceData::assign_hazard( $this->pihrDao,
            $this->test_shared_pi_1, $this->test_hazard, $this->test_shared_room );

        ReferenceData::assign_hazard( $this->pihrDao,
            $this->test_shared_pi_2, $this->test_hazard, $this->test_shared_room );
    }

    public function test__getHazardRoomDtosByPIId_pi_vs_roomIds(){
        $pi_id = $this->test_pi->getKey_id();
        $room_id = $this->test_room->getKey_id();

        // Get inventory by PI
        $pihrDtos_by_rooms = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, [$room_id]);

        // Get Inventory by PI
        $pihrDtos_by_pi = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, null);

        // Find object for test room from both sets
        $find_fn = function(Array $arr, $room_id){
            foreach($arr as $pihr){
                if( $pihr->getRoom_id() == $room_id ){
                    return $pihr;
                }
                return null;
            }
        };

        $room_by_room = $find_fn($pihrDtos_by_rooms, $room_id);
        $room_by_pi = $find_fn($pihrDtos_by_pi, $room_id);

        // Compare results; All accessors should return equal values
        $fns = [
            'getPrincipal_investigator_id',
            'getHazard_id',
            'getMasterHazardId',
            'getRoom_name',
            'getBuilding_name',
            'getBuilding_id',
            'getRoom_id',
            'getPrincipal_investigator_hazard_room_relation_id',
            'getContainsHazard',
            'getStatus',
            'getStored',
            'getHasMultiplePis',
            'getOtherLab',
        ];

        foreach($fns as $getter){
            Assert::eq( $room_by_pi->$getter(), $room_by_room->$getter(), "$getter");
        }
    }

    public function test__getHazardRoomDtosByPIId_roomIds(){
        $pi_id = $this->test_pi->getKey_id();
        $room_id = $this->test_room->getKey_id();

        // Get PI's inventory in room
        $pihrDtos = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, [$room_id]);

        Assert::eq( count($pihrDtos), 1, 'One PIHR DTO was returned');

        $dto = $pihrDtos[0];
        Assert::eq( $dto->getRoom_id(), $room_id, "Room ID matches");
        Assert::null( $dto->getPrincipal_investigator_id(), 'PI ID is not set');
    }

    public function test__getHazardRoomDtosByPIId_piId(){
        $pi_id = $this->test_pi->getKey_id();
        $room_id = $this->test_room->getKey_id();

        // Get PI's inventory in all rooms
        $pihrDtos = $this->pihrDao->getHazardRoomDtosByPIId($pi_id);

        Assert::eq( count($pihrDtos), 1, 'One PIHR DTO was returned');

        foreach($pihrDtos as $dto){
            Assert::null( $dto->getPrincipal_investigator_id(), 'PI ID is not set');
        }
    }

    public function test__getAllHazardDtos(){
        // Get DTO for each Hazard
        $hazardDtos = $this->pihrDao->getAllHazardDtos();

        // Get all Active hazards
        $allHazards = $this->hazardDao->getAll(null, false, true);

        Assert::eq(
            count($hazardDtos),
            count($allHazards),
            'A DTO for each active Hazard was created'
        );
    }

    public function test__getPIsAssignedInRooms_assignedOneRoom(){
        $room_id = $this->test_shared_room->getKey_id();

        $piIds = $this->pihrDao->getPIsAssignedInRooms( [$room_id] );

        Assert::eq( count($piIds), 1, 'One Room list is returned');
        $key = array_keys($piIds)[0];
        Assert::eq( $key, $room_id, 'Returned list is keyed to test Room');

        $vals = $piIds[$key];
        Assert::eq( count($vals), 2, '2 PIs are assigned to test room');

        Assert::true( in_array($this->test_shared_pi_1->getKey_id(), $vals), 'Shared PI 1 is assigned');
        Assert::true( in_array($this->test_shared_pi_2->getKey_id(), $vals), 'Shared PI 2 is assigned');
    }

    public function test__getPIsAssignedInRooms_assignedMultipleRooms(){

        // Assign another room to test_shared_pi_1
        $actionmanager = new ActionManager();
        ReferenceData::assign_room( $actionmanager, $this->test_shared_pi_1, $this->test_room );

        $room_id = $this->test_shared_room->getKey_id();
        $extra_room_id = $this->test_room->getKey_id();

        $piIds = $this->pihrDao->getPIsAssignedInRooms( [$room_id, $extra_room_id] );

        Assert::eq( count($piIds), 2, 'Two Room lists are returned');

        // One for test_room, one for test_shared_room
        $shared_room_1 = null;
        $shared_room_extra = null;
        foreach($piIds as $r_id => $room_pi_ids ){
            switch( $r_id ){
                case $this->test_room->getKey_id():
                    $shared_room_extra = $room_pi_ids;
                    continue;
                case $this->test_shared_room->getKey_id():
                    $shared_room_1 = $room_pi_ids;
                    continue;
            }
        }

        Assert::not_null($shared_room_extra, 'Extra Room is included');
        Assert::not_null($shared_room_1, 'Shared Room is included');

        Assert::eq( count($shared_room_extra), 2, 'Extra room has 2 PIs');
        Assert::true( in_array($this->test_shared_pi_1->getKey_id(), $shared_room_extra), 'Shared PI is included in Extra Room');

        Assert::eq( count($shared_room_1), 2, 'Shared room has 2 PIs');
        Assert::true( in_array($this->test_shared_pi_1->getKey_id(), $shared_room_1), 'Shared PI is included in Shared Room');
    }

    public function test__getPIHazardRoomRelations(){
        // Given a Room and Hazard assigned to two PIs
        $pi_id = $this->test_shared_pi_1->getKey_id();
        $hazard_id = $this->test_hazard->getKey_id();
        $room_id = $this->test_shared_room->getKey_id();
        $roomIds = [$room_id];

        // When we query for PI/Hazard/Room relations
        $pihrs = $this->pihrDao->getPIHazardRoomRelations( $pi_id, $hazard_id, $roomIds );

        // Then:

        // 2 entries are returned
        Assert::eq( count($pihrs), 2, 'Two PIHR Relations found');

        // Assert that one exists for each shared PI
        Assert::eq( $pihrs[0]->getPrincipal_investigator_id(), $this->test_shared_pi_1->getKey_id(), 'Shared PI 1 is matched');
        Assert::eq( $pihrs[0]->getRoom_id(), $room_id, 'Shared PI 1 is matched in Room');
        Assert::eq( $pihrs[0]->getHazard_id(), $hazard_id, 'Shared PI 1 is matched in Room with Hazard');

        Assert::eq( $pihrs[1]->getPrincipal_investigator_id(), $this->test_shared_pi_2->getKey_id(), 'Shared PI 2 is matched');
        Assert::eq( $pihrs[1]->getRoom_id(), $room_id, 'Shared PI 2 is matched in Room');
        Assert::eq( $pihrs[1]->getHazard_id(), $hazard_id, 'Shared PI 2 is matched in Room with Hazard');
    }

    public function test__pihrReferencesOtherPI_shared(){
        // Given a Room and Hazard assigned to two PIs
        $pi_id = $this->test_shared_pi_1->getKey_id();
        $hazard_id = $this->test_hazard->getKey_id();
        $room_id = $this->test_shared_room->getKey_id();
        $roomIds = [$room_id];

        // and a map of room-to-pi IDs
        $piIds = $this->pihrDao->getPIsAssignedInRooms( [$room_id] );

        // and a relation to a PI other than our subject (shared PI 1)
        $pihrs = $this->pihrDao->getPIHazardRoomRelations( $pi_id, $hazard_id, $roomIds );
        Assert::eq( count($pihrs), 2, 'Two PIHR Relations found');

        $relation = $pihrs[1];
        Assert::eq( $relation->getPrincipal_investigator_id(), $this->test_shared_pi_2->getKey_id(), 'Relation exists for non-subject PI');

        // When we check if that relation is for someone else
        $referencesOther = $this->pihrDao->pihrReferencesOtherPI( $relation, $piIds, $pi_id );

        // Then we find that it is true
        Assert::true($referencesOther, 'Relation references PI other than the subject');
    }

    public function test__determineHazardStatus_shared(){
        $pi_id = $this->test_shared_pi_1->getKey_id();
        $room_id = $this->test_shared_room->getKey_id();
        $hazard_id = $this->test_hazard->getKey_id();

        // Given an inventory configured for the PI and Hazard
        $pihrDtos = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, [$room_id]);
        foreach($pihrDtos as $pihr){
            $pihr->setPrincipal_investigator_id( $pi_id );
            $pihr->setHazard_id( $hazard_id );
        }

        // ...and a Hazard DTO configured for our PI and Room...
        $hazardDto = $this->_getHazardDto($hazard_id);
        $hazardDto->setRoomIds( [$room_id] );
        $hazardDto->setPrincipal_investigator_id( $pi_id );

        // When we determine the status
        $this->pihrDao->determineHazardStatus( $hazardDto, $pihrDtos );

        // Then...

        // Hazard is assigned to multiple PIs
        Assert::eq( $hazardDto->getHasMultiplePis(), self::IS_SHARED, 'Hazard is assigned to multiple PIs');

        // Hazard is not assigned to only other PIs
        Assert::eq( $hazardDto->getBelongsToOtherPI(), self::IS_NOT_OTHER, 'Hazard is not assigned to only Other PIs');

        // Hazard is not stored-only
        Assert::eq( $hazardDto->getStored_only(), self::IS_NOT_STORED, 'Hazard is not stored-only');

        // one room is included
        $pihrs = $hazardDto->getInspectionRooms();
        Assert::eq( count($pihrs), 1, 'One Inspection/Room is included');

        $pihr = $pihrs[0];
        Assert::true($pihr->getContainsHazard(), 'Room contains test hazard');
        Assert::true($pihr->getHasMultiplePis(), 'Room is Shared');
        Assert::false($pihr->getOtherLab(), 'Room is NOT assigned to only Other PIs');
        Assert::false($pihr->getStored(), 'Hazard is NOT stored-only in this room');
    }

    public function test__mergeHazardRoomDtos_soloRoom_SpecifyRooms(){
        $pi_id = $this->test_pi->getKey_id();
        $room_id = $this->test_room->getKey_id();
        $hazard_id = $this->test_hazard->getKey_id();

        // Get inventory in room
        $pihrDtos = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, [$room_id]);

        $this->_assert_mergeHazardRoomDtos($pihrDtos, $pi_id, $room_id, $hazard_id, self::IS_NOT_SHARED, self::IS_NOT_OTHER, self::IS_NOT_STORED);
    }

    public function test__mergeHazardRoomDtos_soloRoom_SpecifyRooms_withExtra(){
        $pi_id = $this->test_pi->getKey_id();
        $room_id = $this->test_room->getKey_id();
        $extra_room_id = $this->test_room_unused->getKey_id();
        $hazard_id = $this->test_hazard->getKey_id();

        // Get inventory in room
        $pihrDtos = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, [$room_id, $extra_room_id]);

        $this->_assert_mergeHazardRoomDtos($pihrDtos, $pi_id, $room_id, $hazard_id, self::IS_NOT_SHARED, self::IS_NOT_OTHER, self::IS_NOT_STORED);
    }

    public function test__mergeHazardRoomDtos_soloRoom_AllRooms(){
        $pi_id = $this->test_pi->getKey_id();
        $room_id = $this->test_room->getKey_id();
        $hazard_id = $this->test_hazard->getKey_id();

        // Get inventory in all rooms
        $pihrDtos = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, null);

        $this->_assert_mergeHazardRoomDtos($pihrDtos, $pi_id, $room_id, $hazard_id, self::IS_NOT_SHARED, self::IS_NOT_OTHER, self::IS_NOT_STORED);
    }

    public function test__mergeHazardRoomDtos_sharedRoom_SpecifyRooms(){
        // Given a PI assigned a Shared Room
        $pi_id = $this->test_shared_pi_1->getKey_id();
        $room_id = $this->test_shared_room->getKey_id();
        $hazard_id = $this->test_hazard->getKey_id();

        // Get inventory in room
        $pihrDtos = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, [$room_id]);

        $this->_assert_mergeHazardRoomDtos($pihrDtos, $pi_id, $room_id, $hazard_id, self::IS_SHARED, self::IS_NOT_OTHER, self::IS_NOT_STORED);
    }

    public function test__mergeHazardRoomDtos_sharedRoom_SpecifyRooms_withExtra(){
        // Given a PI assigned a Shared Room
        $pi_id = $this->test_shared_pi_1->getKey_id();
        $room_id = $this->test_shared_room->getKey_id();
        $extra_room_id = $this->test_room_unused->getKey_id();
        $hazard_id = $this->test_hazard->getKey_id();

        // Get inventory in room
        $pihrDtos = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, [$room_id, $extra_room_id]);

        $this->_assert_mergeHazardRoomDtos($pihrDtos, $pi_id, $room_id, $hazard_id, self::IS_SHARED, self::IS_NOT_OTHER, self::IS_NOT_STORED);
    }

    public function test__mergeHazardRoomDtos_sharedRoom_AllRooms(){
        // Given a PI assigned a Shared Room
        $pi_id = $this->test_shared_pi_1->getKey_id();
        $room_id = $this->test_shared_room->getKey_id();
        $hazard_id = $this->test_hazard->getKey_id();

        // Get inventory in all rooms
        $pihrDtos = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, null);

        $this->_assert_mergeHazardRoomDtos($pihrDtos, $pi_id, $room_id, $hazard_id, self::IS_SHARED, self::IS_NOT_OTHER, self::IS_NOT_STORED);
    }

    /////////////////////////////////
    // Utility functions

    private function _getHazardDto( &$hazard_id ){
        $hazardDtos = $this->pihrDao->getAllHazardDtos();

        // Trim hazards down to our test hazard
        foreach ($hazardDtos as $dto) {
            if( $dto->getKey_id() == $hazard_id ){
                return $dto;
            }
        }

        return null;
    }

    /**
     * Tests the given PIHR DTOs
     */
    private function _assert_mergeHazardRoomDtos( &$pihrDtos, &$pi_id, &$room_id, &$hazard_id, $is_shared, $is_other, $is_stored) {
        // Grab IDs of returned rooms
        $roomIds = array_map( function($dto){ return $dto->getRoom_id(); }, $pihrDtos);
        Assert::not_empty($roomIds, 'Room IDs extracted');
        Assert::true( in_array($room_id, $roomIds), 'Test Room ID is included in data');

        // Get all hazard DTOs
        $hazardDto = $this->_getHazardDto($hazard_id);
        Assert::not_null($hazardDto, 'Hazard was matched');
        $hazardDtos = array($hazardDto);

        // Merge them
        $mergedDtos = $this->pihrDao->mergeHazardRoomDtos($pi_id, $roomIds, $hazardDtos, $pihrDtos);

        // Find DTOs for our test room
        $roomDtos = array_filter( $mergedDtos, function($dto) use ($hazard_id) {
            return $dto->getHazard_id() == $hazard_id;
        });

        Assert::eq( count($roomDtos), 1, "One PIHR DTO was matched for the test hazard ($hazard_id)");
        $dto = array_values($roomDtos)[0];

        $inspectionRooms = array_filter($dto->getInspectionRooms(), function($ir) use ($room_id) {
            return $ir->getRoom_id() == $room_id;
        });

        Assert::eq( count($inspectionRooms), 1, "One PIHR was matched for the test Room ($room_id)");
        $pihr = array_values($inspectionRooms)[0];

        Assert::true($pihr->getContainsHazard(), 'Room contains the Hazard');
        Assert::eq($pihr->getHasMultiplePis(), $is_shared, ($is_shared ? 'Room is shared' : 'Room is not shared') );
        Assert::eq($pihr->getOtherLab(), $is_other, ($is_other ? 'Room is assigned only to Other PIs' : 'Room is not assigned only to Other PI'));
        Assert::eq($pihr->getStored(), $is_stored, ($is_stored ? 'Hazard is stored-only' : 'Hazard is not stored-only'));
    }
}
?>

<?php
class TestPrincipalInvestigatorHazardRoomRelationDAO implements I_Test {
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

    public function test__mergeHazardRoomDtos_soloRoom_SpecifyRooms(){
        $pi_id = $this->test_pi->getKey_id();
        $room_id = $this->test_room->getKey_id();

        $roomIds = [$room_id];

        // Get inventory in room
        $pihrDtos = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, $roomIds);

        // Get all hazard DTOs
        $hazardDtos = $this->pihrDao->getAllHazardDtos();

        // Merge them
        $mergedDtos = $this->pihrDao->mergeHazardRoomDtos($pi_id, $roomIds, $hazardDtos, $pihrDtos);

        // Find DTOs for our test room
        $roomDtos = array_filter( $mergedDtos, function($dto) {
            return $dto->getIsPresent();
        });

        Assert::eq( count($roomDtos), 1, "One PIHR DTO was returned");
        $dto = array_values($roomDtos)[0];

        $inspectionRooms = $dto->getInspectionRooms();
        Assert::eq( count($inspectionRooms), 1, "One PIHR is inspectable");
        $pihr = array_values($inspectionRooms)[0];

        Assert::true($pihr->getContainsHazard(), 'Room contains the Hazard');
        Assert::false($pihr->getHasMultiplePis(), 'Room is not shared');
        Assert::false($pihr->getOtherLab(), 'Room is not assigned only to Other PI');
        Assert::false($pihr->getStored(), 'Hazard is not stored-only');
    }

    public function test__mergeHazardRoomDtos_soloRoom_AllRooms(){
        $pi_id = $this->test_pi->getKey_id();
        $room_id = $this->test_room->getKey_id();

        // Get inventory in all rooms
        $pihrDtos = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, null);

        // Grab IDs of returned rooms
        $roomIds = array_map( function($dto){ return $dto->getRoom_id(); }, $pihrDtos);
        Assert::not_empty($roomIds, 'Room IDs extracted');

        // Get all hazard DTOs
        $hazardDtos = $this->pihrDao->getAllHazardDtos();

        // Merge them
        $mergedDtos = $this->pihrDao->mergeHazardRoomDtos($pi_id, $roomIds, $hazardDtos, $pihrDtos);

        // Find DTOs for our test room
        $roomDtos = array_filter( $mergedDtos, function($dto) {
            return $dto->getIsPresent();
        });

        Assert::eq( count($roomDtos), 1, "One PIHR DTO was returned");
        $dto = array_values($roomDtos)[0];

        // Verify hazard
        Assert::true($dto->getIsPresent(), 'Hazard is present');
        Assert::false($dto->getHasMultiplePis(), 'Hazard has only the one PI');
        Assert::false($dto->getBelongsToOtherPI(), 'Hazard is not assigned ONLY to other PIs');
        Assert::false($dto->getStored_only(), 'Hazard is not stored-only');

        // Verify InspectionRooms for our Test Room
        $inspectionRooms = array_filter($dto->getInspectionRooms(), function($ir) use ($room_id) {
            return $ir->getRoom_id() == $room_id;
        });

        Assert::eq( count($inspectionRooms), 1, "One PIHR is inspectable");
        $pihr = array_values($inspectionRooms)[0];

        Assert::true($pihr->getContainsHazard(), 'Room contains the Hazard');
        Assert::false($pihr->getHasMultiplePis(), 'Room is not shared');
        Assert::false($pihr->getOtherLab(), 'Room is not assigned only to Other PI');
        Assert::false($pihr->getStored(), 'Hazard is not stored-only');
    }

    public function test__mergeHazardRoomDtos_sharedRoom_SpecifyRooms(){
        // Given a PI assigned a Shared Room
        $pi_id = $this->test_shared_pi_1->getKey_id();
        $room_id = $this->test_shared_room->getKey_id();

        $roomIds = [$room_id];

        // Get inventory in room
        $pihrDtos = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, $roomIds);

        // Get all hazard DTOs
        $hazardDtos = $this->pihrDao->getAllHazardDtos();

        // Merge them
        $mergedDtos = $this->pihrDao->mergeHazardRoomDtos($pi_id, $roomIds, $hazardDtos, $pihrDtos);

        // Find DTOs for our test room
        $roomDtos = array_filter( $mergedDtos, function($dto) {
            return $dto->getIsPresent();
        });

        Assert::eq( count($roomDtos), 1, "One PIHR DTO was returned");
        $dto = array_values($roomDtos)[0];

        $inspectionRooms = $dto->getInspectionRooms();
        Assert::eq( count($inspectionRooms), 1, "One PIHR is inspectable");
        $pihr = array_values($inspectionRooms)[0];

        Assert::true($pihr->getContainsHazard(), 'Room contains the Hazard');
        Assert::true($pihr->getHasMultiplePis(), 'Room is shared');
        Assert::false($pihr->getOtherLab(), 'Room is not assigned only to Other PI');
        Assert::false($pihr->getStored(), 'Hazard is not stored-only');
    }

    public function test__mergeHazardRoomDtos_sharedRoom_AllRooms(){
        // Given a PI assigned a Shared Room
        $pi_id = $this->test_shared_pi_1->getKey_id();
        $room_id = $this->test_shared_room->getKey_id();

        // Get inventory in all rooms
        $pihrDtos = $this->pihrDao->getHazardRoomDtosByPIId($pi_id, null);

        // Grab IDs of returned rooms
        $roomIds = array_map( function($dto){ return $dto->getRoom_id(); }, $pihrDtos);
        Assert::not_empty($roomIds, 'Room IDs extracted');

        // Get all hazard DTOs
        $hazardDtos = $this->pihrDao->getAllHazardDtos();

        // Merge them
        $mergedDtos = $this->pihrDao->mergeHazardRoomDtos($pi_id, $roomIds, $hazardDtos, $pihrDtos);

        // Find DTOs for our test room
        $roomDtos = array_filter( $mergedDtos, function($dto) {
            return $dto->getIsPresent();
        });

        Assert::eq( count($roomDtos), 1, "One PIHR DTO was returned");
        $dto = array_values($roomDtos)[0];

        // Verify hazard
        Assert::true($dto->getIsPresent(), 'Hazard is present');
        Assert::false($dto->getHasMultiplePis(), 'Hazard has only the one PI');
        Assert::false($dto->getBelongsToOtherPI(), 'Hazard is not assigned ONLY to other PIs');
        Assert::false($dto->getStored_only(), 'Hazard is not stored-only');

        // Verify InspectionRooms for our Test Room
        $inspectionRooms = array_filter($dto->getInspectionRooms(), function($ir) use ($room_id) {
            return $ir->getRoom_id() == $room_id;
        });

        Assert::eq( count($inspectionRooms), 1, "One PIHR is inspectable");
        $pihr = array_values($inspectionRooms)[0];

        Assert::true($pihr->getContainsHazard(), 'Room contains the Hazard');
        Assert::true($pihr->getHasMultiplePis(), 'Room is shared');
        Assert::false($pihr->getOtherLab(), 'Room is not assigned only to Other PI');
        Assert::false($pihr->getStored(), 'Hazard is not stored-only');
    }
}
?>

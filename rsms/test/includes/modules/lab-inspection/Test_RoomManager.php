<?php
class Test_RoomManager implements I_Test {
    public function setup(){
        $this->roomDao = new RoomDAO();
        $this->userDao = new UserDAO();
    }

    public function before__testdata(){
        //LabInspection_TestDataProvider
        // Test Teaching Room
        $teachingRoom = new Room();
        $teachingRoom->setRoom_type( RoomType::TEACHING_LAB );
        $this->teachingRoom = $this->roomDao->save($teachingRoom);

        $roles = Core_TestDataProvider::create_named_roles([
            LabInspectionModule::ROLE_PI,
            LabInspectionModule::ROLE_TEACHING_LAB_CONTACT,
        ]);

        $userDao = new UserDAO();

        $u = new User();
        $u->setFirst_name('test');
        $u->setLast_name('tester');
        $u->setEmail('test@test.er');
        $this->testUser = $userDao->save($u);

        // TODO: Grant the user the Teaching Lab Contact role
        $this->userDao->addRelatedItems(
            ($roles[LabInspectionModule::ROLE_TEACHING_LAB_CONTACT])->getKey_id(),
            $this->testUser->getKey_id(),
            DataRelationship::fromArray(User::$ROLES_RELATIONSHIP));
    }

    public function test__getAssignableUsers_unassignableReturnsEmpty(){
        // Given a non-Assignable room type
        // Training Rooms are not assignable
        $type = RoomType::of(RoomType::TRAINING_ROOM);
        Assert::not_null($type, 'Training Room type is not null');
        Assert::null($type->getAssignable_to(), 'Training Room type is not assignable');

        // When we get users assignable to that room type
        $users = RoomManager::get()->getAssignableUsers($type);

        // Then we retrieve an empty list
        Assert::empty($users, 'No assignable users are returned for Training Rooms');
    }

    public function test__getAssignableUsers_assignableReturnsList(){
        // Given an Assignable room type
        // Teaching Labs are assignable
        $type = RoomType::of(RoomType::TEACHING_LAB);
        Assert::not_null($type, 'Teaching Lab type is not null');
        Assert::not_null($type->getAssignable_to(), 'Teaching Lab type is assignable');

        // When we get users assignable to that room type
        $users = RoomManager::get()->getAssignableUsers($type);

        // Then we retrieve a non-empty list
        Assert::not_empty($users, 'Assignable users are returned for Teaching Lab');
    }

    public function test__saveRoom(){
        // Given a teaching room
        Assert::not_null($this->teachingRoom->getKey_id(), 'Teaching Room exists');

        // When we save an assignment
        $changes = clone $this->teachingRoom;
        $changes->setUserAssignments([
            ['User_id' => $this->testUser->getKey_id() ]
        ]);

        $saved = RoomManager::get()->saveRoom($changes);

        // Then the user is assigned with the TEACHING_LAB_CONTACT
        $assignments = $this->roomDao->getRoomAssignedUsers( $this->teachingRoom->getKey_id() );
        Assert::not_empty( $assignments, 'Assignments were created');
    }

    public function test__saveRoom_changeType(){
        // Given a teaching room
        Assert::not_null($this->teachingRoom->getKey_id(), 'Teaching Room exists');

        // with no assignments
        $assignments = $this->roomDao->getRoomAssignedUsers( $this->teachingRoom->getKey_id() );
        Assert::empty( $assignments, "No Assignments exist for $this->teachingRoom");

        // When we change its type to research lab
        $changes = clone $this->teachingRoom;
        $changes->setRoom_type( RoomType::RESEARCH_LAB );

        $saved = RoomManager::get()->saveRoom($changes);

        // Then the room is successfully changed
        Assert::eq( $saved->getRoom_type(), RoomType::RESEARCH_LAB, "Room is now a research lab");
    }
}
?>

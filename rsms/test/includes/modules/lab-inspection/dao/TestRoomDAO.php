<?php
class TestRoomDAO implements I_Test {
    public function setup(){
        $this->dao = new RoomDAO();

        $this->actionmanager = new ActionManager();
    }

    public function before__createReferenceData(){
        // Create test hazard
        $hazard = new Hazard();
        $hazard->setIs_active(true);
        $hazard->setName("Test Hazard");
        $hazard->setParent_hazard_id(1);    // biological hazards
        $this->test_hazard = $this->actionmanager->saveHazard($hazard);

        // Create test Room
        $room = new Room();
        $room->setIs_active(true);
        $room->setName("Test Room");
        $this->test_room = RoomManager::get()->saveRoom($room);

        $user = new User();
        $user->setIs_active(true);
        $user->setFirst_name("TestUserFirstName");
        $user->setLast_name("TestUserLastName");
        $this->test_user = $this->actionmanager->saveUser($user);

        // Create test PI
        $pidao = new PrincipalInvestigatorDAO();
        $pi = new PrincipalInvestigator();
        $pi->setIs_active(true);
        $pi->setUser_id($user->getKey_id());
        $this->test_pi = $pidao->save($pi);
    }

    private static function assign_pihr( &$pi, &$hazard, &$room ){
        $pihr = new PrincipalInvestigatorHazardRoomRelation();
        $pihr->setHazard_id($hazard->getKey_id());
        $pihr->setPrincipal_investigator_id( $pi->getKey_id() );
        $pihr->setRoom_id( $room->getKey_id() );

        return (new GenericDAO($pihr))->save($pihr);
    }

    public function test__getPrincipalInvestigatorsWithHazardInRoom_noHazards(){
        $results = $this->dao->getPrincipalInvestigatorsWithHazardInRoom( $this->test_room->getKey_id() );
        Assert::empty($results, 'No hazards in room');
    }

    public function test__getPrincipalInvestigatorsWithHazardInRoom_withHazards(){
        // Assign a hazard
        self::assign_pihr(
            $this->test_pi,
            $this->test_hazard,
            $this->test_room
        );

        $results = $this->dao->getPrincipalInvestigatorsWithHazardInRoom( $this->test_room->getKey_id() );
        Assert::not_empty($results, 'Hazards in room');
        Assert::eq( count($results), 1, '1 hazard in room');
        Assert::eq( $results[0]->principal_investigator_id, $this->test_pi->getKey_id(), 'Hazard is assigned to Test PI');
    }

    public function test__getPrincipalInvestigatorsWithHazardInRoom_withHazards_filterByPI(){
        // Assign a hazard
        self::assign_pihr(
            $this->test_pi,
            $this->test_hazard,
            $this->test_room
        );

        $results = $this->dao->getPrincipalInvestigatorsWithHazardInRoom( $this->test_room->getKey_id(), array($this->test_pi->getKey_id()) );
        Assert::not_empty($results, 'Hazards in room');
        Assert::eq( count($results), 1, '1 hazard in room');
        Assert::eq( $results[0]->principal_investigator_id, $this->test_pi->getKey_id(), 'Hazard is assigned to Test PI');
    }

    public function test__getPrincipalInvestigatorsWithHazardInRoom_withHazards_filterByOtherPI(){
        // Assign a hazard
        self::assign_pihr(
            $this->test_pi,
            $this->test_hazard,
            $this->test_room
        );

        $results = $this->dao->getPrincipalInvestigatorsWithHazardInRoom(
            $this->test_room->getKey_id(),
            array($this->test_pi->getKey_id() + 1)  // Add 1 to PI ID
        );

        Assert::empty($results, 'No Hazards in room for this PI');
    }
}
?>

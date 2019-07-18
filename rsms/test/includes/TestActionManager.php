<?php
class TestActionManager implements I_Test {
    public function setup(){
        $this->actionmanager = new ActionManager();
    }

    public function before__createReferenceData(){
        // Create test hazard
        $this->test_hazard = ReferenceData::create_hazard(
            $this->actionmanager,
            "Test Hazard",
            1,              // biological hazards
            true
        );

        // Create test Room
        $this->test_room = ReferenceData::create_room(
            $this->actionmanager,
            "Test Room",
            true
        );

        // Create test user
        $this->test_user = ReferenceData::create_user(
            $this->actionmanager,
            "TestUserFirstName",
            "TestUserLastName",
            "test@email.com",
            true
        );

        // Create test PI
        $piDao = new PrincipalInvestigatorDAO();
        $this->test_pi = ReferenceData::create_pi(
            $piDao,
            $this->test_user->getKey_id()
        );
    }

    private function assign_hazard(){
        // Assign a hazard to the pi/room
        $pihr_dao = new PrincipalInvestigatorHazardRoomRelationDAO();
        return ReferenceData::assign_hazard(
            $pihr_dao,
            $this->test_pi,
            $this->test_hazard,
            $this->test_room
        );
    }

    public function test__getRoomHasHazards(){
        // Test that one expected PI has hazards
        self::assign_hazard();

        $pis = $this->actionmanager->getRoomHasHazards(
            $this->test_room->getKey_id(),
            [$this->test_pi->getKey_id()]
        );

        Assert::true($pis->HasHazards, 'PI has hazards');
        Assert::not_empty($pis->PI_ids, 'PI has hazards in room');
    }

    public function test__getRoomHasHazards_noHazards(){
        // Do not assign hazard

        // Test that one expected PI has hazards
        $pis = $this->actionmanager->getRoomHasHazards(
            $this->test_room->getKey_id(),
            [$this->test_pi->getKey_id()]
        );

        Assert::false($pis->HasHazards, 'PI has no hazards');
        Assert::empty($pis->PI_ids, 'PI has no hazards in room');
    }
}
?>

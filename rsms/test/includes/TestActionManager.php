<?php
class TestActionManager implements I_Test {
    public function setup(){
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
        $this->test_room = $this->actionmanager->saveRoom($room);

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

    private function assign_hazard(){
        // Assign a hazard to the pi/room
        $pihr_dao = new GenericDAO(new PrincipalInvestigatorHazardRoomRelation());
        $pihr = new PrincipalInvestigatorHazardRoomRelation();
        $pihr->setHazard_id($this->test_hazard->getKey_id());
        $pihr->setPrincipal_investigator_id( $this->test_pi->getKey_id() );
        $pihr->setRoom_id( $this->test_room->getKey_id() );
        $pihr_dao->save($pihr);
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

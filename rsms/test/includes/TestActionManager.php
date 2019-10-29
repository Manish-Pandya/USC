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
        $this->test_hazard = $this->actionmanager->saveHazard($hazard);

        // Create test Room
        $room = new Room();
        $room->setIs_active(true);
        $room->setName("Test Room");
        $this->test_room = $this->actionmanager->saveRoom($room);

        $roles = Core_TestDataProvider::create_named_roles([
            'Principal Investigator'
        ]);

        $this->pi_role = $roles['Principal Investigator'];

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

    public function test__getChecklistByHazardId(){
        // Load a checklist for a hazard which has no checklist
        $c = $this->actionmanager->getChecklistByHazardId($this->test_hazard->getKey_id());
        Assert::true(isset($c), 'Checklist not null');
        Assert::false($c->hasPrimaryKeyValue(), 'Cheklist is transient');

        // Attempt to retrieve questions
        $qs = $c->getQuestions();
        Assert::empty($qs, 'Checklist has no questions');
    }

    public function test__saveUser_existingPi_noDupe(){
        // Given a PI User with data to change
        $incoming = new User();
        $incoming->setKey_id( $this->test_user->getKey_id() );
        $incoming->setFirst_name( $this->test_user->getFirst_name() );
        $incoming->setLast_name( $this->test_user->getLast_name() );
        $incoming->setEmail( "changed-email@address.com" );             // Modified email address

        $incoming->setRoles([
            [
                'Key_id' => $this->pi_role->getKey_id(),
                'Name' => $this->pi_role->getName()
            ]
        ]);

        // and an empty PI object
        $incoming->setPrincipalInvestigator( new PrincipalInvestigator() );

        // When we save this PI user
        $saved = $this->actionmanager->saveUser($incoming);

        // Then no duplicate PI is created
        $piUsers = QueryUtil::selectFrom(new PrincipalInvestigator())
            ->where(Field::create('user_id', 'principal_investigator'), '=', $this->test_user->getKey_id())
            ->getAll();

        Assert::eq( count($piUsers), 1, "Only one PI exists for $this->test_user");

        // and email has been changed
        Assert::eq( $saved->getEmail(), 'changed-email@address.com', 'Email address was changed');
    }
}
?>

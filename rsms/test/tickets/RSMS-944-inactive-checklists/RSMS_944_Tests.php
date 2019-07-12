<?php

class RSMS_944_Tests implements I_Test {
    function setup(){
        $this->actionmanager = new ActionManager();
        $this->inspectionDao = new InspectionDAO();
        $this->piDao = new PrincipalInvestigatorDAO();
        $this->pihrDao = new GenericDAO(new PrincipalInvestigatorHazardRoomRelation());
        $this->responseDao = new GenericDAO(new Response());
    }

    function _changeActiveStatus( $obj, $activeStatus ){
        $st = $activeStatus ? 'Active' : 'Inactive';
        $obj->setIs_active($activeStatus);
        $cls = get_class($obj);
        $fn = "save$cls";

        $this->actionmanager->$fn($obj);
        Assert::true( $obj->getIs_active() == $activeStatus, "$cls is $st");
        return $obj;
    }

    function _getTestHazard( $activeStatus = true ){
        $st = $activeStatus ? 'Active' : 'Inactive';

        // Create test hazard
        $hazard = new Hazard();
        $hazard->setIs_active($activeStatus);
        $hazard->setName("Test Hazard");
        $hazard->setParent_hazard_id(1);    // biological hazards
        $this->actionmanager->saveHazard($hazard);

        // Create test Checklist
        $checklist = new Checklist();
        $checklist->setIs_active($activeStatus);
        $checklist->setName('Test Hazard Checklist');
        $checklist->setHazard_id( $hazard->getKey_id() );
        $this->actionmanager->saveChecklist($checklist);

        // Create test question
        $question = new Question();
        $question->setIs_active( $activeStatus );
        $question->setChecklist_id( $checklist->getKey_id() );
        $question->setText('Test Hazard Checklist Question Text');
        $question->setDescription('Test Hazard Checklist Question Description');
        $question->setReference('Test Hazard Checklist Question Reference');
        $this->actionmanager->saveQuestion($question);

        Assert::true( $hazard->getIs_active() == $activeStatus, "Hazard is $st");
        Assert::true( $hazard->getChecklist()->getIs_active() == $activeStatus, "Checklist is $st");
        Assert::true( $hazard->getChecklist()->getQuestions()[0]->getIs_active() == $activeStatus, "Question is $st");

        return $hazard;
    }

    function _assignHazardToRoom($hazard, $pi, $room){
        $pihr = new PrincipalInvestigatorHazardRoomRelation();
        $pihr->setHazard_id($hazard->getKey_id());
        $pihr->setPrincipal_investigator_id($pi->getKey_id());
        $pihr->setRoom_id($room->getKey_id());
        $this->pihrDao->save($pihr);
        return $pihr;
    }

    function _unassignFromRoom( PrincipalInvestigatorHazardRoomRelation $pihr ){
        if( !$pihr->hasPrimaryKeyValue() ){
            throw new Exception('Cannot delete item with no ID');
        }

        return $this->pihrDao->deleteById($pihr->getKey_id());
    }

    function _createInspection($pi, $room){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        // Create a new inspection
        $LOG->info("Creating test inspection");
        $insp = new Inspection();
        $insp->setPrincipal_investigator_id( $pi->getKey_id() );
        $insp->setSchedule_month( date("m"));
        $insp->setSchedule_year( date("Y") );
        $this->inspectionDao->save($insp);
        $LOG->info("Created: $insp");

        // Assign Rooms to Inspection
        $this->actionmanager->saveInspectionRoomRelation($room->getKey_id(), $insp->getKey_id(), true);

        return $insp;
    }

    function _getChecklistsForHazard(Array $checklists, $hazard){
        return array_filter( $checklists, function($c) use ($hazard){
            return $c->getHazard_id() == $hazard->getKey_id();
        });
    }

    /**
     * Tests that the Checklist for a Hazard which is not assigned
     * to an inspected room is never present
     */
    function test__getChecklistsForInspection_unassignedHazard_omitted(){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        // Get reference data
        $testHazard = $this->_getTestHazard( true );
        $pi = $this->piDao->getById(1);
        $room = $pi->getRooms()[0];

        if( !isset($testHazard) || !isset($pi) || !isset($room) ){
            throw new Exception("Error buildling test");
        }

        // Do not assign testHazard to Room

        $insp = $this->_createInspection($pi, $room);

        $checklists = $this->actionmanager->getChecklistsForInspection($insp->getKey_id());
        $matches = $this->_getChecklistsForHazard( $checklists, $testHazard );
        Assert::empty( $matches, 'Checklist is not included');
    }

    /**
     * Tests that the Checklist for an Inactive Hazard which is still assigned
     * to an inspected room is always present
     */
    function test__getChecklistsForInspection_assignedInactiveHazard_present(){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        // Get reference data
        $testHazard = $this->_getTestHazard( true );
        $pi = $this->piDao->getById(1);
        $room = $pi->getRooms()[0];

        if( !isset($testHazard) || !isset($pi) || !isset($room) ){
            throw new Exception("Error buildling test");
        }

        // Assign testHazard to Room
        $pihr = $this->_assignHazardToRoom($testHazard, $pi, $room);

        $insp = $this->_createInspection($pi, $room);

        $checklists = $this->actionmanager->getChecklistsForInspection($insp->getKey_id());
        $matches = $this->_getChecklistsForHazard( $checklists, $testHazard );
        Assert::eq( count($matches), 1, 'Active Checklist is included');

        // Get inactive test hazard
        $testHazard = $this->_changeActiveStatus( $testHazard, false );

        $checklists = $this->actionmanager->getChecklistsForInspection($insp->getKey_id());
        $matches = $this->_getChecklistsForHazard( $checklists, $testHazard );
        Assert::not_empty( $matches, 'Inactive Checklist is still included');
    }

    /**
     * Tests that a Checklist whose Hazard is Inactivated
     * is removed from an Inspection which has no Responses
     * to the Checklist
     */
    function test__unused_checklist_removed(){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        // Get reference data
        $testHazard = $this->_getTestHazard( true );
        $pi = $this->piDao->getById(1);
        $room = $pi->getRooms()[0];

        if( !isset($testHazard) || !isset($pi) || !isset($room) ){
            throw new Exception("Error buildling test");
        }

        // Assign the hazard
        $pihr = $this->_assignHazardToRoom($testHazard, $pi, $room);

        // Create a new inspection
        $insp = $this->_createInspection($pi, $room);

        // Generate Checklists
        $LOG->info("Generating checklists ($testHazard)");

        $inspectionWithChecklists_with_test = $this->actionmanager->resetChecklists( $insp->getKey_id() );

        // Verify test hazard is included
        $matches = $this->_getChecklistsForHazard( $inspectionWithChecklists_with_test->getChecklists(), $testHazard );

        Assert::eq( count($matches), 1, "Test checklist is present");

        // Inactivate the hazard
        $LOG->info("Inactivating test hazard");
        $testHazard = $this->_changeActiveStatus( $testHazard, false );
        Assert::false( $testHazard->getIs_active(), 'Hazard is inactive');
        Assert::false( $testHazard->getChecklist()->getIs_active(), 'Checklist is inactive');

        // Unassign the hazard
        $this->_unassignFromRoom($pihr);

        // Regenerate the Checklists
        $LOG->info("Generating checklists ($testHazard)");
        $inspectionWithChecklists_without_test = $this->actionmanager->resetChecklists( $insp->getKey_id() );

        // Verify test hazard is NOT included
        $matches = $this->_getChecklistsForHazard( $inspectionWithChecklists_without_test->getChecklists(), $testHazard );
        Assert::empty( $matches, "Test checklist is not present after inactivation");
    }

    /**
     * Tests that a Checkist whose Hazard is Inactivated
     * is retained in an Inspection which has Responses
     * to the Checklist
     */
    function test__used_checklist_retained(){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        // Get reference data
        $testHazard = $this->_getTestHazard( true );
        $pi = $this->piDao->getById(1);
        $room = $pi->getRooms()[0];

        if( !isset($testHazard) || !isset($pi) || !isset($room) ){
            throw new Exception("Error buildling test");
        }

        // Assign the hazard
        $pihr = $this->_assignHazardToRoom($testHazard, $pi, $room);

        // Create a new inspection
        $insp = $this->_createInspection($pi, $room);

        // Generate Checklists
        $LOG->info("Generating checklists ($testHazard)");

        $inspectionWithChecklists_with_test = $this->actionmanager->resetChecklists( $insp->getKey_id() );

        // Verify test hazard is included
        $matches = $this->_getChecklistsForHazard( $inspectionWithChecklists_with_test->getChecklists(), $testHazard );

        Assert::eq( count($matches), 1, "Test checklist is present");

        // Record a response to the test checklist
        // Note use of array_values, becuase the above filtering retains indecies as keys
        $q = array_values($matches)[0]->getQuestions()[0];

        $response = new Response();
        $response->setInspection_id( $insp->getKey_id() );
        $response->setAnswer("yes");
        $response->setQuestion_id($q->getKey_id());
        $this->responseDao->save($response);

        Assert::true( $response->hasPrimaryKeyValue(), 'Response was saved');

        $usedChecklists = $this->inspectionDao->getChecklistsUsedInInspection( $insp->getKey_id() );
        Assert::not_empty( $usedChecklists, 'Inspection has used checklists');

        // Inactivate the hazard
        $LOG->info("Inactivating test hazard");
        $testHazard = $this->_changeActiveStatus( $testHazard, false );
        Assert::false( $testHazard->getIs_active(), 'Hazard is inactive');
        Assert::false( $testHazard->getChecklist()->getIs_active(), 'Checklist is inactive');

        // Unassign the hazard
        $this->_unassignFromRoom($pihr);

        $usedChecklists = $this->inspectionDao->getChecklistsUsedInInspection( $insp->getKey_id() );
        Assert::not_empty( $usedChecklists, 'Inspection still has used checklists');

        // Regenerate the Checklists
        $LOG->info("Regenerating checklists ($testHazard)");
        $inspectionWithChecklists_without_test = $this->actionmanager->resetChecklists( $insp->getKey_id() );

        // Verify test hazard is still included
        $matches = $this->_getChecklistsForHazard( $inspectionWithChecklists_without_test->getChecklists(), $testHazard );
        Assert::eq( count($matches), 1, "Test checklist is still present after inactivation");
    }
}


?>

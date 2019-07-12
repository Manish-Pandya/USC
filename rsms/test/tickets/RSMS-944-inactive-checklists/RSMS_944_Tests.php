<?php

class RSMS_944_Tests implements I_Test {
    function setup(){
        $this->actionmanager = new ActionManager();
        $this->inspectionDao = new InspectionDAO();
        $this->piDao = new PrincipalInvestigatorDAO();
        $this->responseDao = new GenericDAO(new Response());
    }

    /**
     * Tests that a Checklist whose Hazard is Inactivated
     * is removed from an Inspection which has no Responses
     * to the Checklist
     */
    function test__unused_checklist_removed(){
        $LOG = LogUtil::get_logger(__CLASS__, __FUNCTION__);

        // Get reference data
        $testHazard = $this->actionmanager->getHazardById( 11199 );
        $pi = $this->piDao->getById(1);
        $room = $pi->getRooms()[0];

        if( !isset($testHazard) || !isset($pi) || !isset($room) ){
            throw new Exception("Error buildling test");
        }

        // Create a new inspection
        $LOG->info("Creating test inspection");
        $insp = new Inspection();
        $insp->setPrincipal_investigator_id( 1 );
        $insp->setSchedule_month( date("m"));
        $insp->setSchedule_year( date("Y") );
        $this->inspectionDao->save($insp);
        $LOG->info("Created: $insp");

        // Assign Rooms to Inspection
        $this->actionmanager->saveInspectionRoomRelation($room->getKey_id(), $insp->getKey_id(), true);

        // Ensure hazard is Active
        if( !$testHazard->getIs_active() ){
            $LOG->info("Activating test hazard");
            $testHazard->setIs_active(true);
            $this->actionmanager->saveHazard( $testHazard );
        }

        Assert::true( $testHazard->getIs_active(), 'Hazard is active');
        Assert::true( $testHazard->getChecklist()->getIs_active(), 'Checklist is active');

        // Generate Checklists
        $LOG->info("Generating checklists ($testHazard)");

        $inspectionWithChecklists_with_test = $this->actionmanager->resetChecklists( $insp->getKey_id() );

        // Verify test hazard is included
        $matches = array_filter( $inspectionWithChecklists_with_test->getChecklists(), function($c) use ($testHazard){
            return $c->getHazard_id() == $testHazard->getKey_id();
        });

        Assert::eq( count($matches), 1, "Test checklist is present");

        // Inactivate the hazard
        $LOG->info("Inactivating test hazard");
        $testHazard->setIs_active(false);
        $this->actionmanager->saveHazard($testHazard);
        Assert::false( $testHazard->getIs_active(), 'Hazard is inactive');
        Assert::false( $testHazard->getChecklist()->getIs_active(), 'Checklist is inactive');

        // Regenerate the Checklists
        $LOG->info("Generating checklists ($testHazard)");
        $inspectionWithChecklists_without_test = $this->actionmanager->resetChecklists( $insp->getKey_id() );

        // Verify test hazard is NOT included
        $matches = array_filter( $inspectionWithChecklists_without_test->getChecklists(), function($c) use ($testHazard){
            return $c->getHazard_id() == $testHazard->getKey_id();
        });
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
        $testHazard = $this->actionmanager->getHazardById( 11199 );
        $pi = $this->piDao->getById(1);
        $room = $pi->getRooms()[0];

        if( !isset($testHazard) || !isset($pi) || !isset($room) ){
            throw new Exception("Error buildling test");
        }

        // Create a new inspection
        $LOG->info("Creating test inspection");
        $insp = new Inspection();
        $insp->setPrincipal_investigator_id( 1 );
        $insp->setSchedule_month( date("m"));
        $insp->setSchedule_year( date("Y") );
        $this->inspectionDao->save($insp);
        $LOG->info("Created: $insp");
        Assert::true( $insp->hasPrimaryKeyValue(), 'Inspection was saved');

        // Assign Rooms to Inspection
        $this->actionmanager->saveInspectionRoomRelation($room->getKey_id(), $insp->getKey_id(), true);

        // Ensure hazard is Active
        if( !$testHazard->getIs_active() ){
            $LOG->info("Activating test hazard");
            $testHazard->setIs_active(true);
            $this->actionmanager->saveHazard( $testHazard );
        }

        Assert::true( $testHazard->getIs_active(), 'Hazard is active');
        Assert::true( $testHazard->getChecklist()->getIs_active(), 'Checklist is active');

        // Generate Checklists
        $LOG->info("Generating checklists ($testHazard)");

        $inspectionWithChecklists_with_test = $this->actionmanager->resetChecklists( $insp->getKey_id() );

        // Verify test hazard is included
        $matches = array_filter( $inspectionWithChecklists_with_test->getChecklists(), function($c) use ($testHazard){
            return $c->getHazard_id() == $testHazard->getKey_id();
        });

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
        $testHazard->setIs_active(false);
        $this->actionmanager->saveHazard($testHazard);
        Assert::false( $testHazard->getIs_active(), 'Hazard is inactive');
        Assert::false( $testHazard->getChecklist()->getIs_active(), 'Checklist is inactive');

        $usedChecklists = $this->inspectionDao->getChecklistsUsedInInspection( $insp->getKey_id() );
        Assert::not_empty( $usedChecklists, 'Inspection still has used checklists');

        // Regenerate the Checklists
        $LOG->info("Regenerating checklists ($testHazard)");
        $inspectionWithChecklists_without_test = $this->actionmanager->resetChecklists( $insp->getKey_id() );

        // Verify test hazard is still included
        $matches = array_filter( $inspectionWithChecklists_without_test->getChecklists(), function($c) use ($testHazard){
            return $c->getHazard_id() == $testHazard->getKey_id();
        });
        Assert::eq( count($matches), 1, "Test checklist is still present after inactivation");
    }
}


?>

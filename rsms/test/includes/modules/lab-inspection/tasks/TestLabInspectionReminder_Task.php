<?php
class TestLabInspectionReminder_Task implements I_Test {
    public function setup(){
        $this->inspectionDao = new InspectionDAO();
        $this->hazardDao = new GenericDAO(new Hazard());
        $this->checklistDao = new GenericDAO(new Checklist());
        $this->questionDao = new GenericDAO(new Question());
        $this->responseDao = new GenericDAO(new Response());
        $this->deficiencyDao = new GenericDAO(new Deficiency());
        $this->deficiencySelectionDao = new GenericDAO(new DeficiencySelection());
        $this->correctiveActionDao = new GenericDAO(new CorrectiveAction());
    }

    public function before__createTestData(){
        $date_twoweeksago = (new DateTime())->sub(date_interval_create_from_date_string('14 days'));
        $twoweeksago = $date_twoweeksago->format("Y-m-d H:i:s");

        // Create a test PI
        $userDao = new UserDAO();
        $pi_user = new User();
        $pi_user->setFirst_name('PI_FN');
        $pi_user->setLast_name('PI_LN');
        $pi_user->setEmail('pi@test.com');
        $pi_user = $userDao->save($pi_user);

        $piDao = new PrincipalInvestigatorDAO();
        $pi = new PrincipalInvestigator();
        $pi->setUser_id($pi_user->getKey_id());
        $pi = $piDao->save($pi);

        ///////////
        // Create an inspection which was scheduled, started, and CAP submitted 2 weeks ago
        $inspection = new Inspection();
        $inspection->setPrincipal_investigator_id( $pi->getKey_id() );
        $inspection->setSchedule_month( $date_twoweeksago->format('m') );
        $inspection->setSchedule_year( $date_twoweeksago->format('Y') );
        $inspection->setDate_started( $twoweeksago );
        $inspection->setNotification_date( $twoweeksago );
        $inspection->setCap_submitted_date( $twoweeksago );
        $this->inspectionDao->save($inspection);

        $hazard = new Hazard();
        $hazard->setName('TEST HAZARD');
        $this->hazardDao->save($hazard);

        $checklist = new Checklist();
        $checklist->setName('TEST CHECKLIST');
        $checklist->setHazard_id( $hazard->getKey_id() );
        $checklist->setMaster_hazard( $hazard->getName() );
        $checklist->setMaster_id( $hazard->getKey_id() );
        $this->checklistDao->save($checklist);

        $question = new Question();
        $question->setText("TEST QUESTION");
        $question->setOrder_index(0);
        $question->setIs_mandatory(true);
        $question->setDescription('DESC');
        $question->setReference('REF');
        $question->setRoot_cause('ROOT CAUSE');
        $question->setChecklist_id( $checklist->getKey_id() );
        $question = $this->questionDao->save($question);

        $response = new Response();
        $response->setInspection_id( $inspection->getKey_id() );
        $response->setQuestion_id( $question->getKey_id() );
        $response->setAnswer('no');
        $response->setQuestion_text('Test question');
        $this->responseDao->save($response);

        $def = new Deficiency();
        $def->setQuestion_id( $question->getKey_id() );
        $def->setText('Test Deficiency');
        $this->deficiencyDao->save($def);

        $defsel = new DeficiencySelection();
        $defsel->setResponse_id($response->getKey_id());
        $defsel->setDeficiency_id( $def->getKey_id() );
        $this->deficiencySelectionDao->save($defsel);

        $cap = new CorrectiveAction();
        $cap->setDeficiency_selection_id( $defsel->getKey_id() );
        $cap->setText("Test CAP");
        $cap->setStatus( CorrectiveAction::$STATUS_PENDING );
        $this->correctiveActionDao->save($cap);
        ///////////////

        // Assign reference for testing
        $this->inspection = $inspection;
        $this->date_twoweeksago = $date_twoweeksago;
    }

    public function test__getPendingCapInspections_submittedInspection(){
        $task = new LabInspectionReminder_Task();

        // Given a 2-week old Inspection with a Pending CAP
        Assert::true( isset($this->inspection) && $this->inspection->hasPrimaryKeyValue(), 'Test Inspection exists');
        Assert::eq( $this->inspection->getStatus(), 'SUBMITTED CAP', 'Inspection is in SUBMITTED_CAP status');

        // When we prepare pending CAP reminders
        $pending = $task->getPendingCapInspections();

        // Then the inspection is returned
        Assert::not_empty($pending, 'Pending items matched by Task');
        $inspection_id = $this->inspection->getKey_id();
        $matches = array_filter($pending, function($i) use ($inspection_id) { return $i->inspection_id == $inspection_id; });
        Assert::eq( count($matches), 1, "One inspection is matched by Task");
        Assert::eq( array_values($matches)[0]->inspection_id, $inspection_id, "Test Inspection (#$inspection_id) is matched by Task");
    }

    public function test__getPendingCapInspections_closedInspectionWithPendingCAP(){
        $task = new LabInspectionReminder_Task();

        // Given a 2-week old Inspection with a Pending CAP
        Assert::true( isset($this->inspection) && $this->inspection->hasPrimaryKeyValue(), 'Test Inspection exists');

        // When we Close the inspection with the CAP still pending
        $this->inspection->setDate_closed( $this->date_twoweeksago->format('Y-m-d H:i:s'));
        $this->inspectionDao->save($this->inspection);

        Assert::eq( $this->inspection->getStatus(), 'CLOSED OUT', 'Inspection is in CLOSED_OUT status');

        // Then when we prepare pending CAP reminders
        $pending = $task->getPendingCapInspections();
        $inspection_id = $this->inspection->getKey_id();
        $matches = array_filter($pending, function($i) use ($inspection_id) { return $i->inspection_id == $inspection_id; });
        Assert::empty( $matches, 'Test Inspection is not matched by Task');
    }
}
?>

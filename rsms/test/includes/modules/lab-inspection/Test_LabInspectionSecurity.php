<?php
class Test_LabInspectionSecurity implements I_Test {
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    public function setup(){
        $this->inspectionDao = new InspectionDAO();
    }

    public function before__createTestData(){
        $userDao = new UserDAO();
        $user = new User();
        $user->setIs_active(true);
        $user->setFirst_name("TestUserFirstName");
        $user->setLast_name("TestUserLastName");
        $this->test_user = $userDao->save($user);

        // Create test PI
        $pidao = new PrincipalInvestigatorDAO();
        $pi = new PrincipalInvestigator();
        $pi->setIs_active(true);
        $pi->setUser_id($user->getKey_id());
        $this->test_pi = $pidao->save($pi);
    }

    public function test__inspectionIsOldOrArchived_closedOut(){
        // Given an inspection which is Closed Out
        $insp = new Inspection();
        $insp->setPrincipal_investigator_id( $this->test_pi->getKey_id() );
        $insp->setDate_closed( date('Y-m-d H:i:s') );

        $closedInspection = $this->inspectionDao->save($insp);
        Assert::true( is_numeric($closedInspection->getKey_id()), 'Inspection is saved');
        Assert::eq( $closedInspection->getStatus(), 'CLOSED OUT', 'Inspection is Closed Out');
        Assert::true( $closedInspection->getIsArchived(), 'Inspection is considered Archived');

        // When checking its Archival status
        $isOldOrArchived = LabInspectionSecurity::inspectionIsOldOrArchived( $closedInspection->getKey_id() );

        Assert::true($isOldOrArchived, "$closedInspection is Old or Archived");
    }

    public function test__inspectionIsOldOrArchived_openOneYearOldNoNew(){
        // Given an inspection which is not Closed Out and started 1 year ago
        $insp = new Inspection();
        $insp->setPrincipal_investigator_id( $this->test_pi->getKey_id() );
        $currentYear = date('Y');
        $currentMonth = date('m');

        $insp->setSchedule_year($currentYear - 1);
        $insp->setSchedule_month($currentMonth);

        $startOneYearAgo = date_create( $insp->getSchedule_year() . "-" . $insp->getSchedule_month() );
        $insp->setDate_started( $startOneYearAgo->format(self::DATE_FORMAT) );
        $insp->setCap_submitted_date( $startOneYearAgo->format(self::DATE_FORMAT) );

        $oldInspection = $this->inspectionDao->save($insp);
        Assert::true( is_numeric($oldInspection->getKey_id()), 'Inspection is saved');
        Assert::eq( $oldInspection->getStatus(), 'SUBMITTED CAP', 'Inspection is SUBMITTED CAP');
        Assert::false( $oldInspection->getIsArchived(), 'Inspection is not considered Archived');

        // When checking its Archival status
        $isOldOrArchived = LabInspectionSecurity::inspectionIsOldOrArchived( $oldInspection->getKey_id() );

        Assert::false($isOldOrArchived, "$oldInspection is not Old or Archived");
    }

    public function test__inspectionIsOldOrArchived_openOneYearOldWithNew(){
        // Given an inspection which is not Closed Out and started 1 year ago
        $insp = new Inspection();
        $insp->setPrincipal_investigator_id( $this->test_pi->getKey_id() );
        $currentYear = date('Y');
        $currentMonth = date('m');

        $insp->setSchedule_year($currentYear - 1);
        $insp->setSchedule_month($currentMonth);

        $startOneYearAgo = date_create( $insp->getSchedule_year() . "-" . $insp->getSchedule_month() );
        $insp->setDate_started( $startOneYearAgo->format(self::DATE_FORMAT) );
        $insp->setCap_submitted_date( $startOneYearAgo->format(self::DATE_FORMAT) );
        $oldInspection = $this->inspectionDao->save($insp);

        // ...And a newer Inspection
        $insp = new Inspection();
        $insp->setPrincipal_investigator_id( $this->test_pi->getKey_id() );
        $currentYear = date('Y');
        $currentMonth = date('m');

        $insp->setSchedule_year($currentYear);
        $insp->setSchedule_month($currentMonth);

        $startThisYear = date_create( $insp->getSchedule_year() . "-" . $insp->getSchedule_month() );
        $insp->setDate_started( $startThisYear->format(self::DATE_FORMAT) );
        $insp->setCap_submitted_date( $startThisYear->format(self::DATE_FORMAT) );
        $newInspection = $this->inspectionDao->save($insp);

        Assert::true( is_numeric($oldInspection->getKey_id()) && is_numeric($newInspection->getKey_id()), 'Inspections are saved');
        Assert::eq( $oldInspection->getStatus(), 'SUBMITTED CAP', 'Inspection is SUBMITTED CAP');
        Assert::false( $oldInspection->getIsArchived(), 'Inspection is not considered Archived');

        // When checking its Archival status
        $isOldOrArchived = LabInspectionSecurity::inspectionIsOldOrArchived( $oldInspection->getKey_id() );

        // Then it's considered old/archived because there's a newer inspection
        Assert::true($isOldOrArchived, "$oldInspection is Old or Archived");
    }

    public function test__inspectionIsOldOrArchived_openTwoYearsOld(){
        // Given an inspection which is not Closed Out and started 2 years ago
        $insp = new Inspection();
        $insp->setPrincipal_investigator_id( $this->test_pi->getKey_id() );
        $currentYear = date('Y');
        $currentMonth = date('m');

        $insp->setSchedule_year($currentYear - 2);
        $insp->setSchedule_month($currentMonth);

        $startTwoYearsAgo = date_create( $insp->getSchedule_year() . "-" . $insp->getSchedule_month() );
        $insp->setDate_started( $startTwoYearsAgo->format(self::DATE_FORMAT) );
        $insp->setCap_submitted_date( $startTwoYearsAgo->format(self::DATE_FORMAT) );

        $oldInspection = $this->inspectionDao->save($insp);
        Assert::true( is_numeric($oldInspection->getKey_id()), 'Inspection is saved');
        Assert::eq( $oldInspection->getStatus(), 'SUBMITTED CAP', 'Inspection is SUBMITTED CAP');
        Assert::false( $oldInspection->getIsArchived(), 'Inspection is not considered Archived');

        // When checking its Archival status
        $isOldOrArchived = LabInspectionSecurity::inspectionIsOldOrArchived( $oldInspection->getKey_id() );

        Assert::true($isOldOrArchived, "$oldInspection is Old or Archived");
    }
}
?>

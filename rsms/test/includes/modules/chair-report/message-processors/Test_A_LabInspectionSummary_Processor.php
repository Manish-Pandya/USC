<?php

class Test_A_LabInspectionSummary_Processor implements I_Test {
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    public function setup(){
        $this->processor = new A_LabInspectionSummary_Processor();
    }

    public function before__createTestData(){
        $roles = Core_TestDataProvider::create_named_roles(['Department Chair']);
        $chairRole = $roles['Department Chair'];

        $userDao = new UserDAO();
        $deptDao = new GenericDAO(new Department());
        $piDao = new PrincipalInvestigatorDAO();

        // Create Department with Chair
        $deptWithChair = new Department();
        $deptWithChair->setIs_active(true);
        $deptWithChair->setName("Department With Chair");
        $this->deptWithChair = $deptDao->save($deptWithChair);

        // Create Department without Chair
        $deptNoChair = new Department();
        $deptNoChair->setIs_active(true);
        $deptNoChair->setName("Department With No Chair");
        $this->deptNoChair = $deptDao->save($deptNoChair);

        // Create Chair user
        $chairUser = new User();
        $chairUser->setFirst_name("Test");
        $chairUser->setLast_name("Chair");
        $chairUser->setEmail("chair@email.com");
        $chairUser->setPrimary_department_id( $deptWithChair->getKey_id() );
        $this->chairUser = $userDao->save($chairUser);

        $pi = new PrincipalInvestigator();
        $pi->setUser_id( $chairUser->getKey_id() );
        $piDao->save($pi);

        // Assign PI to Department
        $piDao->addRelatedItems(
            $deptWithChair->getKey_id(),
            $pi->getKey_id(),
            DataRelationship::fromArray(PrincipalInvestigator::$DEPARTMENTS_RELATIONSHIP)
        );

        // Assign user Chair role
        $userDao->addRelatedItems(
            $chairRole->getKey_id(),
            $chairUser->getKey_id(),
            DataRelationship::fromArray(User::$ROLES_RELATIONSHIP)
        );
    }

    public function test__getDepartment_withChair(){
        $deptDao = new GenericDAO(new Department());
        $dept = $deptDao->getById( $this->deptWithChair->getKey_id() );
        Assert::true( isset($dept) && $dept->getKey_id() == $this->deptWithChair->getKey_id(), 'Test department exists');

        $deptInfo = $this->processor->getDepartment( $this->deptWithChair->getKey_id() );
        Assert::true( isset($deptInfo), 'Retrieved Test Department info');

        Assert::eq( $deptInfo->getChair_email(), 'chair@email.com', 'Department chair email is set');
    }

    public function test__getDepartment_noChair(){
        try{
            $deptInfo = $this->processor->getDepartment( $this->deptNoChair->getKey_id() );
            Assert::fail("No exception was thrown due to missing chair");
        }
        catch(Exception $e){
            Assert::pass('Exception is thrown because dept has no chair');
        }
    }


}

?>

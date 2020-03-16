<?php
class LabInspection_TestDataProvider {

    public const EMAIL_PI = "pi@email.com";
    public const EMAIL_INSPECTOR = "inspector@email.com";
    public const EMAIL_CONTACT_1 = "contact1@email.com";
    public const EMAIL_CONTACT_2 = "contact2@email.com";
    public const EMAIL_PERSONNEL_1 = "personnel1@email.com";

    private static function _saveUser($f, $l, $e, $s = null){
        $userDao = new UserDAO();

        $u = new User();
        $u->setFirst_name($f);
        $u->setLast_name($l);
        $u->setEmail($e);
        $u->setSupervisor_id($s);
        return $userDao->save($u);
    }

    public static function createTestInspection(){
        $piDao = new PrincipalInvestigatorDAO();
        $inspectorDao = new GenericDAO(new Inspector());
        $inspectionDao = new InspectionDAO();

        // PI user
        $pi_user = self::_saveUser('Principal', 'Investigator', self::EMAIL_PI);
        $pi = new PrincipalInvestigator();
        $pi->setUser_id($pi_user->getKey_id());
        $pi = $piDao->save($pi);

        // Lab Contacts
        $contact1 = self::_saveUser('Test1', 'Contact1', self::EMAIL_CONTACT_1, $pi->getKey_id());
        $contact2 = self::_saveUser('Test2', 'Contact2', self::EMAIL_CONTACT_2, $pi->getKey_id());

        // Lab Personnel
        $personnel1 = self::_saveUser('Test1', 'Personnel1', self::EMAIL_PERSONNEL_1, $pi->getKey_id());

        // Inspector user
        $inspector_user = self::_saveUser('Test', 'Inspector', self::EMAIL_INSPECTOR);
        $inspector = new Inspector();
        $inspector->setUser_id($inspector_user->getKey_id());
        $inspector = $inspectorDao->save($inspector);

        // Inspection
        $insp = new Inspection();
        $insp->setPrincipal_investigator_id( $pi->getKey_id() );
        $insp = $inspectionDao->save($insp);

        // Assign Inspectors
        $inspectionDao->addRelatedItems( $inspector->getKey_id(), $insp->getKey_id(), DataRelationship::fromArray(Inspection::$INSPECTORS_RELATIONSHIP) );

        // Assign Inspection Personnel
        $inspectionDao->addRelatedItems( $contact2->getKey_id(), $insp->getKey_id(), DataRelationship::fromArray(Inspection::$INSPECTION_LAB_PERSONNEL_RELATIONSHIP) );

        return $insp;
    }
}
?>

<?php
class Test_LabInspection_Hooks implements I_Test {
    public function setup(){

    }

    public function test__after_save_user_roles__removeSupervisor(){
        // Given a user with an assigned PI and no Lab Personnel/Contact role
        $user = new User();
        $user->setRoles([]);
        $user->setSupervisor_id(1234);

        // When the hook fires
        LabInspection_Hooks::after_save_user_roles( $user );

        // Then their supervisor is unassigned
        Assert::true( $user->getSupervisor_id() == NULL, 'Supervisor ID is unset');
    }
}
?>

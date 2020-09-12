<?php
class Test_IsotopeDAO implements I_Test {
    public function setup(){
        $this->radActionmanager = new Rad_ActionManager();
        $this->actionmanager = new ActionManager();

        $this->piDao = new PrincipalInvestigatorDAO();

        $this->isotopeDao = new IsotopeDAO();
    }

    public function before__createTestData(){
        // User
        $this->test_user = ReferenceData::create_user( $this->actionmanager, "PIFirstName", "PILastName", "test-pi@email.com");

        // PI
        $this->test_pi = ReferenceData::create_pi( $this->piDao, $this->test_user->getKey_id() );

        // Isotope
        $this->test_isotope = Rad_TestDataProvider::create_isotope($this->radActionmanager, 'T-123', 'Gamma');

        // PI Auth
        $this->test_pi_auth = Rad_TestDataProvider::create_pi_authorization($this->radActionmanager, $this->test_pi);

        // Authorization
        $this->auth = Rad_TestDataProvider::create_isotope_authorization($this->radActionmanager, $this->test_pi_auth, $this->test_isotope);
    }

    public function test__getIsotopeTotalsReport_empty(){
        $singleInv = $this->isotopeDao->getCurrentInvetoriesByPiId( $this->test_pi->getKey_id(), $this->test_pi_auth->getKey_id() );
        Assert::eq(count($singleInv), 1, 'Inventory has one entry');
        $item = $singleInv[0];

        // Assert that inventory calculations are all zero
        Assert::eq($item->getAmount_disposed(), 0, 'Nothing disposed');
        Assert::eq($item->getAmount_on_hand(), 0, 'Nothing on hand');
        Assert::eq($item->getAmount_picked_up(), 0, 'Nothing picked up');
        Assert::eq($item->getOrdered(), 0, 'Nothing ordered');
        Assert::eq($item->getUsable_amount(), 0, 'Nothing usable');
        Assert::eq($item->getMax_order(), 100, '100 Max order');
    }

    public function test__getIsotopeTotalsReport_populated_singleIsotope(){

        // Create a single-isotope parcel
        $parcel = Rad_TestDataProvider::create_parcel($this->radActionmanager, $this->test_pi, $this->auth, 50);

        $singleInv = $this->isotopeDao->getCurrentInvetoriesByPiId( $this->test_pi->getKey_id(), $this->test_pi_auth->getKey_id() );
        Assert::eq(count($singleInv), 1, 'Inventory has one entry');
        $item = $singleInv[0];

        // Assert that inventory calculations
        Assert::eq($item->getAmount_disposed(), 0, 'Nothing disposed');
        Assert::eq($item->getAmount_on_hand(), 50, '50 on hand');
        Assert::eq($item->getAmount_picked_up(), 0, 'Nothing picked up');
        Assert::eq($item->getOrdered(), 50, '50 ordered');
        Assert::eq($item->getUsable_amount(), 50, '50 usable');
        Assert::eq($item->getMax_order(), 50, '50 Max order');

        // TODO: Add use logs and reassert
        // TODO: Add disposals and reassert
        // TODO: Add transfers and reassert

    }
}
?>

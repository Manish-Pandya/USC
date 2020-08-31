<?php
class Test_Rad_ActionManager implements I_Test {
    public function setup(){
        $this->radActionmanager = new Rad_ActionManager();
        $this->actionmanager = new ActionManager();

        $this->piDao = new PrincipalInvestigatorDAO();
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

        // Parcel
        $this->test_parcel = Rad_TestDataProvider::create_parcel($this->radActionmanager, $this->test_pi, $this->auth);
    }

    private function __getInventoryItem( Isotope $isotope ){
        foreach( $this->radActionmanager->getTotalInventories() as $item ){
            if( $item->getIsotope_id() == $isotope->getKey_id() ){
                return $item;
            }
        }

        return null;
    }

    /**
     * Given a Parcel with 10 units
     * When a Transfer of 1 unit from the Parcel
     * Then the inventory reports only 9 units total
     */
    public function test__saveParcelUse_transfer(){
        // Given a test PI, Isotope, authorization, parcel
        Assert::not_null($this->test_pi, 'Test PI exists');
        Assert::not_null($this->test_isotope, 'Test Isotope exists');
        Assert::not_null($this->test_parcel, 'Test Parcel exists');

        $pre_inventory = $this->__getInventoryItem($this->test_isotope);
        Assert::eq( $pre_inventory->getTotal_ordered(), 10, '10 units of isotope ordered before test');
        Assert::eq( $pre_inventory->getTotal_quantity(), 10, '10 units of isotope inventory before test');

        // When we save a new transfer from this parcel
        $transfer_use = new ParcelUse();
        $transfer_use->setParcel_id($this->test_parcel->getKey_id());
        $transfer_use->setDate_transferred( date('Y-m-d G:i:s') );
        $transfer_use->setQuantity( 1 );

        $amt = new ParcelUseAmount();
        $amt->setComments('TEST TRANSFER 1mCi');
        $amt->setCurie_level( 1 );
        $amt->setWaste_type_id( 6 );    // Transfer type
        $transfer_use->setParcelUseAmounts( [$amt] );

        $use = $this->radActionmanager->saveParcelUse( $transfer_use );
        Assert::not_null($use, 'Transfer use was saved');
        Assert::not_null($use->getKey_id(), 'Transfer use has primary key');

        Assert::not_empty($use->getParcelUseAmounts(), 'Transfer Use has use amounts');
        foreach ($use->getParcelUseAmounts() as $amount) {
            Assert::eq($amount->getParcel_use_id(), $use->getKey_id(), 'Transfer Use Amount is linked to Transfer Use');
        }

        // Then the Transfer amount is no longer listed in the total inventory
        $inventory = $this->__getInventoryItem($this->test_isotope);

        Assert::eq( $inventory->getTotal_ordered(), 10, '10 units of isotope ordered after test');
        Assert::eq( $inventory->getTotal_quantity(), 9, 'Transfer of 1 is not reflected in inv');
    }

    /**
     * Sample amounts are differentiated not only by waste type, but by is_active=false.
     * Transfers must be active, so ensure that transferring from a use-log Sample
     * is activated
     */
    public function test__saveParcelUse_transferFromSample(){
        // Given a test PI, Isotope, authorization, parcel
        Assert::not_null($this->test_pi, 'Test PI exists');
        Assert::not_null($this->test_isotope, 'Test Isotope exists');
        Assert::not_null($this->test_parcel, 'Test Parcel exists');

        $pre_inventory = $this->__getInventoryItem($this->test_isotope);
        Assert::eq( $pre_inventory->getTotal_ordered(), 10, '10 units of isotope ordered before test');
        Assert::eq( $pre_inventory->getTotal_quantity(), 10, '10 units of isotope inventory before test');

        ///////////////////
        // Create a new Non-transfer Use
        $use_log_entry = new ParcelUse();
        $use_log_entry->setParcel_id($this->test_parcel->getKey_id());
        $use_log_entry->setDate_used( date('Y-m-d G:i:s') );
        $use_log_entry->setQuantity( 5 );

        $amt = new ParcelUseAmount();
        $amt->setComments('Sample of 5mCi');
        $amt->setCurie_level( 5 );
        $amt->setWaste_type_id( 7 );    // Sample type
        $use_log_entry->setParcelUseAmounts( [$amt] );

        $sample_use = $this->radActionmanager->saveParcelUse( $use_log_entry );
        $sample_amount = $sample_use->getParcelUseAmounts()[0];

        Assert::not_null($sample_amount, 'Sample amount exists');
        Assert::not_null($sample_amount->getKey_id(), 'Sample amount has key');
        Assert::false($sample_amount->getIs_active(), 'Sample amount is inactive');
        ///////////////////

        // When we save a new transfer from this parcel using the samplle amount
        $transfer_use = new ParcelUse();
        $transfer_use->setParcel_id($this->test_parcel->getKey_id());
        $transfer_use->setDate_transferred( date('Y-m-d G:i:s') );
        $transfer_use->setQuantity( 5 );

        // TODO: do we need to change type??
        $transfer_use->setParcelUseAmounts( [$sample_amount] );

        $use = $this->radActionmanager->saveParcelUse( $transfer_use );
        Assert::not_null($use, 'Transfer use was saved');
        Assert::not_null($use->getKey_id(), 'Transfer use has primary key');

        Assert::not_empty($use->getParcelUseAmounts(), 'Transfer Use has use amounts');
        foreach ($use->getParcelUseAmounts() as $amount) {
            Assert::eq($amount->getParcel_use_id(), $use->getKey_id(), 'Transfer Use Amount is linked to Transfer Use');
            Assert::true($sample_amount->getIs_active(), 'Transferred Sample amount is active');
        }

        // Then the Transfer amount is no longer listed in the total inventory
        $inventory = $this->__getInventoryItem($this->test_isotope);

        Assert::eq( $inventory->getTotal_ordered(), 10, '10 units of isotope ordered after test');
        Assert::eq( $inventory->getTotal_quantity(), 5, 'Transfer of 5 is not reflected in inv');
    }
}
?>
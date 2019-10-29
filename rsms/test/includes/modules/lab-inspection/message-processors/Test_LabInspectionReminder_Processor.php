<?php

class Test_LabInspectionReminder_Processor implements I_Test {
    public function setup(){
        $this->processor = new LabInspectionReminder_Processor();
    }

    public function before__createTestData(){
        $this->inspection = LabInspection_TestDataProvider::createTestInspection();
    }

    public function test__prepareRecipientsArray(){
        $to = "test1,test2";
        $cc = "test3,test4";
        $emails = $this->processor->prepareRecipientsArray($to, $cc);

        Assert::eq( $emails['to'], $to, "To is set");
        Assert::false( isset($emails['cc']), "CC is not set");
    }

    public function test__getRecipientEmailAddressesFromInspection(){
        // Test that correct emails are pulled from Inspections
        $emails = $this->processor->getRecipientEmailAddressesFromInspection( $this->inspection );

        Assert::eq( $emails['lab_emails'], ['pi@email.com','contact2@email.com'], 'Send to PI and inspection\'s contact' );
        Assert::eq( $emails['inspector_emails'], ['inspector@email.com'], 'Send to Inspector' );
    }

    public function test__computeEmailRecipients_nocontext(){
        // Test that correct emails are computed
        $emails = $this->processor->computeEmailRecipients( $this->inspection, null );

        Assert::eq( $emails['to'], 'pi@email.com,contact2@email.com', "To is set to PI and Inspection contact");
        Assert::eq( $emails['cc'], '', "CC is not set");
    }

    public function test__computeEmailRecipients_contextAddedRecipient(){
        // TODO
    }
}

?>

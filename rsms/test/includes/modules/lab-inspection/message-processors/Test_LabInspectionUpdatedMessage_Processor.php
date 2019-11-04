<?php

class Test_LabInspectionUpdatedMessage_Processor implements I_Test {
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    public function setup(){
        $this->processor = new LabInspectionUpdatedMessage_Processor();
    }

    const EMAIL_PI = "pi@email.com";
    const EMAIL_INSPECTOR = "inspector@email.com";
    const EMAIL_CONTACT_1 = "contact1@email.com";
    const EMAIL_CONTACT_2 = "contact2@email.com";
    const EMAIL_PERSONNEL_1 = "personnel1@email.com";
    const EMAIL_EXTRA = "extra@email.com";

    public function before__createTestData(){
        $this->inspection = LabInspection_TestDataProvider::createTestInspection();
    }

    public function test__prepareRecipientsArray(){
        $to = "test1,test2";
        $cc = "test3,test4";
        $emails = $this->processor->prepareRecipientsArray($to, $cc);

        Assert::eq( $emails['to'], $to, "To is set");
        Assert::eq( $emails['cc'], $cc, "CC is set");
    }

    public function test__getRecipientEmailAddressesFromInspection(){
        // Test that correct emails are pulled from Inspections
        $emails = $this->processor->getRecipientEmailAddressesFromInspection( $this->inspection );

        Assert::eq( $emails['lab_emails'], ['pi@email.com','contact2@email.com'], 'Send to PI and inspection\'s contact' );
        Assert::eq( $emails['inspector_emails'], ['inspector@email.com'], 'Send to Inspector' );
    }

    public function test__computeEmailRecipients_nocontext(){
        // Test that correct emails are computed
        $emails = $this->processor->computeEmailRecipients( $this->inspection, $this->inspection_context );

        Assert::eq( $emails['to'], 'pi@email.com,contact2@email.com', "To is set to PI and Inspection contact");
        Assert::eq( $emails['cc'], 'inspector@email.com', "CC is set to Inspector");
    }

    public function test__computeEmailRecipients_contextAddedRecipient(){
        // Given that an email was sent at inspection-finalization which included extra emails

        $inspid = $this->inspection->getKey_id();
        $desc = '{"inspection_id":"' . $inspid . '",'
            . '"inspectionState":{"totals":9,"pendings":0,"completes":0,"correcteds":0,"uncorrecteds":9,"unSelectedSumplementals":[],"noDefs":[],"noDefIDS":[],"unselectedIDS":[],"readyToSubmit":false},'
            . '"email":{"entity_id":"' . $inspid . '","recipient_ids":[],"text":"TEST EMAIL",'
            . '"other_emails":["' . self::EMAIL_EXTRA . '"]}}';

        $mdao = new MessageDAO();
        $m = new Message();
        $m->setModule(LabInspectionModule::$NAME);
        $m->setMessage_Type( LabInspectionModule::$MTYPE_DEFICIENCIES_FOUND );
        $m->setContext_descriptor( $desc );
        $m->setSent_date( date(self::DATE_FORMAT) );
        $m = $mdao->save($m);

        $email = new QueuedEmail();
        $email->setMessage_id($m->getKey_id());
        $email->setSent_date( date(self::DATE_FORMAT) );
        $email->setRecipients('pi@email.com,extra@email.com');
        $email->setCc_recipients("");
        $email->setSend_from("test@email.com");
        $email->setSubject("TEST");
        $email->setBody("TEST EMAIL");
        $eDao = new QueuedEmailDAO();
        $eDao->save($email);

        // When we compute recipients
        $ctx = new LabInspectionReminderContext($inspid, null);
        $emails = $this->processor->computeEmailRecipients( $this->inspection, $ctx );

        // Then the extra email addresses are included along with defaults
        Assert::eq( $emails['to'], 'pi@email.com,extra@email.com,contact2@email.com', "To is set to PI,Inspection contact, And Extra");
        Assert::eq( $emails['cc'], 'inspector@email.com', "CC is set to Inspector");

    }
}

?>

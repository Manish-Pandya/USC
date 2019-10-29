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
}

?>

<?php

class SendQueuedEmailsTask implements ScheduledTask {

    public function getPriority(){
        return -20;
    }

    public function run(){
        $messenger = new Messaging_ActionManager();
        $result = $messenger->sendAllQueuedEmails();

        return "Sent " . $result['sent'] . " queued Emails. Failed to send " .  $result['failed'] . " queued Emails.";
    }
}

?>
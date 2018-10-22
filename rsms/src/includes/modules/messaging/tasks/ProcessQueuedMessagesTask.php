<?php

/**
 * Scheduled Task responsible for processing all Messages in the queue.
 *
 * Processing of a Message involves mapping it to its Processor and Template(s),
 * and enqueuing one or more Emails to be sent by SendQueuedEmailsTask.
 */
class ProcessQueuedMessagesTask implements ScheduledTask {

    public function getPriority(){
        return -10;
    }

    public function run(){
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->info("Message Queue Processing");
        $messenger = new Messaging_ActionManager();

        // Get all queued emails
        $unsent = $messenger->getUnsentMessages();

        $LOG->info( count($unsent) . ' messages are enqueued');

        $queuedEmailsCount = 0;

        if( count($unsent) > 0 ){

            foreach( $unsent as $message ){
                $LOG->info("Processing queued message: $message");

                $queuedEmailsPerMessage = 0;

                try {
                    // Get MessageProcessor based on type
                    $processor = $messenger->getMessageTypeProcessor($message->getModule(), $message->getMessage_type());
                    $LOG->info("Found processor '" . get_class($processor) . "' for $message");

                    // Look up the Template(s) for this message type
                    $templates = $messenger->getTemplatesForMessage($message);
                    $LOG->info("Found " . count($templates) . " templates for $message");

                    if( count($templates) == 0 ){
                        $LOG->warn("No matching templates for message type: " . $message->getMessage_type());
                        continue;
                    }

                    $messageDetails = $processor->process($message);

                    $queuedEmails = array();

                    foreach($templates as $template){
                        $LOG->debug("Applying $template to $message");
                        $formattedTemplateEmails = $messenger->buildFormattedMessages($template, $messageDetails);

                        $LOG->info("Generated " . count($formattedTemplateEmails) . " emails to send for $message");

                        foreach($formattedTemplateEmails as $email){
                            $recipients_str = implode(',', $email->to);
                            $queuedEmail = new QueuedEmail();
                            $queuedEmail->setMessage_id($message->getKey_id());
                            $queuedEmail->setTemplate_id($template->getKey_id());
                            $queuedEmail->setRecipients( $recipients_str );
                            $queuedEmail->setSend_from($email->from);
                            $queuedEmail->setSubject($email->subject);
                            $queuedEmail->setBody($email->body);

                            $queuedEmails[] = $queuedEmail;
                        }
                    }

                    // TODO: Persist each formattedEmail in the email queue
                    $emailQueueDao = new GenericDAO(new QueuedEmail());
                    foreach($queuedEmails as $email){
                        $emailQueueDao->save($email);
                        if( $email->getKey_id() != null ){
                            $queuedEmailsPerMessage++;
                        }
                        else {
                            $LOG->error("Unable to save queued email $email for $message");
                        }
                    }

                    $queuedEmailsCount += $queuedEmailsPerMessage;
                    $LOG->info("Queued $queuedEmailsPerMessage emails to be sent for $message");

                    // Update message as processed
                    $message->setSent_date(date());

                    $messageDao = new GenericDAO(new Message());
                    $messageDao->save($message);
                }
                catch(Exception $error){
                    $LOG->error("Cannot process $message: " . $error->getMessage());
                }
            }
        }

        $LOG->info("Message Queue Processing completed");

        return "Enqueued $queuedEmailsCount emails to be sent";
    }
}

?>
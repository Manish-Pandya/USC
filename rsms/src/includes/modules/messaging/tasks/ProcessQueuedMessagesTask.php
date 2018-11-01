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

        // Get all queued emails whose send_on date has passed (or wasn't specified)
        $unsent = $messenger->getAllReadyToSend();

        $LOG->info( count($unsent) . ' messages are enqueued');

        $queuedEmailsCount = 0;

        if( count($unsent) > 0 ){

            $_cache = array();

            foreach( $unsent as $message ){
                $LOG->info("Processing queued message: $message");

                $queuedEmailsPerMessage = 0;

                try {
                    // Prime processor cache
                    $proc_key = $message->getModule() . '/' . $message->getMessage_type();
                    if( $_cache[$proc_key] ){
                        $messageType = $_cache[$proc_key][0];
                        $processor   = $_cache[$proc_key][1];
                    }
                    else{
                        // Get MessageProcessor based on type

                        // Look up message type definition
                        $messageType = $this->getMessageTypeDetails($message->getModule(), $message->getMessage_type());
                        $LOG->trace("Matched message type: $messageType");

                        if( $messageType->processorName != null ){
                            $LOG->debug("Create processor '$messageType->processorName'");
                            $processor = new $messageType->processorName;

                            // Cache type/processor so we only look it up once
                            $_cache[$proc_key] = array($messageType, $processor);
                        }
                    }

                    if( $processor == null ){
                        $LOG->warn("No matching processor for message type " . $message->getMessage_type());
                        continue;
                    }

                    $LOG->debug("Found message type: $messageType for $message");
                    $LOG->debug("Found processor '" . get_class($processor) . "' for $message");

                    // Look up the Template(s) for this message type
                    $templates = $messenger->getTemplatesForMessage($message);
                    $LOG->debug("Found " . count($templates) . " templates for $message");

                    if( count($templates) == 0 ){
                        $LOG->warn("No matching templates for message type: " . $message->getMessage_type());
                        continue;
                    }

                    // Get MacroResolvers from the Module
                    $macroProvider = MacroResolverProvider::build( $messageType );

                    // Process the message
                    $messageDetails = $processor->process($message, $macroProvider);

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

                    // Persist each formattedEmail in the email queue
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
                    $message->setSent_date(date('Y-m-d H:i:s'));

                    $messageDao = new GenericDAO(new Message());
                    $messageDao->save($message);
                }
                catch(Exception $error){
                    $LOG->error("Cannot process $message: " . $error->getMessage());
                }
            }
        }

        return "Enqueued $queuedEmailsCount emails to be sent";
    }

    private function getMessageTypeDetails( $moduleName, $messageTypeName ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        $module = ModuleManager::getModuleByName($moduleName);

        if( $module != null ){
            // Match message type to module's declarations
            foreach( $module->getMessageTypes() as $messageType ){
                if( $messageType->getTypeName() == $messageTypeName ){
                    // Message type is matched
                    break;
                }
            }

            if( $messageType != null ){
                return $messageType;
            }
            else{
                throw new Exception("Module '$moduleName' does not declare message type '$messageTypeName'");
            }
        }
        else{
            throw new Exception("No such  module '$moduleName'");
        }

        return null;
    }
}

?>
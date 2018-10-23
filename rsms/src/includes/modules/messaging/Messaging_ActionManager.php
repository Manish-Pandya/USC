<?php

class Messaging_ActionManager extends ActionManager {

    public function getContextDescriptor(MessageContext $context){
        $context_json = json_encode($context);

        return $context_json;
    }

    public function getContextFromMessage(Message $message){
        $context = json_decode( $message->getContext_descriptor());
        return $context;
    }

    public function enqueueMessages($module, $messageType, Array $contexts, $send_on = NULL){
        $LOG = Logger::getLogger(__CLASS__);

        $messageDao = new MessageDAO();

        $newMessages = array();

        foreach( $contexts as $context ){
            $descriptor = $this->getContextDescriptor($context);
            $description = "$module:$messageType($descriptor)";
            $LOG->debug("Find existing message for: $description");

            // Messages with the same template + context
            $matches = $messageDao->findByContext($module, $messageType, $descriptor);

            // TODO: Optionally allow for repeat emails (with repeat threshold)

            if( $matches != null && count($matches) > 0){
                $LOG->debug("$description is already queued: " . $matches[0]);
                continue;
            }

            $LOG->info("Enqueue new message for $description");

            // Construct Message
            $message = new Message();
            $message->setModule( $module );
            $message->setMessage_type( $messageType );
            $message->setContext_descriptor($descriptor);

            if( $send_on != NULL ){
                $message->setSend_on($send_on);
            }

            // Save & push onto reference array
            $newMessages[] = $messageDao->save($message);
        }

        // Return the newly-created messages
        return $newMessages;
    }

    public function getAllUnsentMessages(){
        $messageDao = new MessageDAO();
        return $messageDao->getAllUnsent();
    }

    public function getAllReadyToSend(){
        $messageDao = new MessageDAO();
        return $messageDao->getAllReadyToSend();
    }

    public function getTemplatesForMessage( $message ){
        $templateDao = new GenericDAO( new MessageTemplate() );
        return $templateDao->getAllWhere(new WhereClauseGroup(array(
            new WhereClause("message_type", "=", $message->getMessage_type()),
            new WhereClause("module", "=", $message->getModule())
        )));
    }

    public function getMessageTypeProcessor( $moduleName, $messageType ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        $module = ModuleManager::getModuleByName($moduleName);

        if( $module != null ){
            // Assume processor class name based on messageType
            $assumeTypeName = $messageType . '_Processor';

            // Look for any of these types declared within the module
            $candidates = ModuleManager::getModuleFeatureClasses($module, 'message-processors', $assumeTypeName);
            if( class_exists($assumeTypeName )){
                $LOG->debug("Module '$moduleName' declares message processor '$assumeTypeName'");
                return new $assumeTypeName;
            }
            else{
                $LOG->warn("Module '$moduleName' declares message type '$messageType' but does not include corresponding processor '$assumeTypeName'");
            }
        }
        else{
            $LOG->warn("No such  module '$moduleName'");
        }

        return null;
    }

    /**
     * Generates a formatted message object for each Details array provided
     */
    public function buildFormattedMessages($template, $details){
        $formattedMessages = array();

        foreach($details as $messageDetails){
            $macromap = $messageDetails['macromap'];

            // Parse template body
            $body    = $this->replaceMacros($macromap, $template->getCorpus());

            // Parse template subject
            $subject = $this->replaceMacros($macromap, $template->getSubject());

            // get recipients, from, etc
            $formattedMessages[] = new FormattedMessage(
                $subject,
                $body,
                $messageDetails['recipients'],
                $messageDetails['from']
            );
        }

        return $formattedMessages;
    }

    public function sendQueuedEmail($unsent) {
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->debug("Sending email $unsent");

        $headers = array();

        if( $unsent->getSend_from() != null){
            $headers['From'] = $unsent->getSend_from();
        }

        // TODO: Support additional headers (like CC)

        if( count($headers) == 0){
            $headers = null;
        }

        $is_sent = mail(
            $unsent->getRecipients(),
            $unsent->getSubject(),
            $unsent->getBody(),
            $headers,
            "Return-path: mmartin+returned@graysail.com"
        );

        if( $is_sent ){
            $dao = new QueuedEmailDAO();
            $unsent->setSent_date(date());
            $dao->save($unsent);

            return true;
        }
        else {
            $LOG->error("Unable to send $unsent");
            return false;
        }

    }

    public function sendAllQueuedEmails(){
        $LOG = Logger::getLogger(__CLASS__);

        $dao = new QueuedEmailDAO();
        $unsentEmails = $dao->getAllUnsent();

        $sentCount = 0;
        $failedCount = 0;

        if( count($unsentEmails) > 0 ){
            $LOG->debug("Sending all unsent emails");
            foreach($unsentEmails as $unsent){
                if($this->sendQueuedEmail($unsent)){
                    $sentCount++;
                }
                else{
                    $failedCount++;
                }
            }
        }
        else{
            $LOG->debug("No unsent emails in queue");
        }

        return array(
            'sent' => $sentCount,
            'failed' => $failedCount
        );
    }

    function replaceMacros($macromap, $content){
        return str_replace(
            array_keys($macromap),
            array_values($macromap),
            $content);
    }
}
?>
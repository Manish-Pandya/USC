<?php

class Messaging_ActionManager extends ActionManager {

    public function getAllMessageTypes(){
        // Get all Modules which declare message types
        $allMessageTypes = array();
        foreach(ModuleManager::getAllModules() as $module){
            if( $module instanceof MessageTypeProvider ){
                $allMessageTypes = array_merge($allMessageTypes, $module->getMessageTypes());
            }
        }

        return $allMessageTypes;
    }

    public function getMessageTemplates($type = NULL){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $type = $this->getValueFromRequest('type', $type);

        $LOG->debug("Get all templates for '$type'");

        $constraint = new WhereClauseGroup();
        if( $type != null ){
            $constraint->setClauses(
                array(
                    new WhereClause('message_type','=', $type)
                )
            );
        }

        $dao = new GenericDAO(new MessageTemplate());
        $templates = $dao->getAllWhere($constraint);
        $LOG->debug($templates);

        // FIXME: Ensure that all content is UTF-8 encoded before JSONing
        return $templates;
    }

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
            $body = $this->replaceMacros($macromap, $template->getCorpus());

            // Append standard disclaimer
            $body .= "\n\n***This is an automatic email notification. Please do not reply to this message.***";

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

        // Prepare target email addresses
        $recipients = $unsent->getRecipients();
        $cc = $unsent->getCc_recipients();

        $roleFilter = ApplicationConfiguration::get(MessagingModule::$CONFIG_EMAIL_SEND_TO_ROLE, null);
        if( $roleFilter != null ){
            $LOG->info("Filtering email recipients and CCs to users with role: '$roleFilter'");
            // Filter target emails to only match users of a given role
            $recipients = $this->filterToEmailsOfRole($recipients, $roleFilter);
            $cc = $this->filterToEmailsOfRole($cc, $roleFilter);
        }

        $headers = array();

        $default_send_from = ApplicationConfiguration::get(MessagingModule::$CONFIG_EMAIL_DEFAULT_SEND_FROM, null);
        $return_path = ApplicationConfiguration::get(MessagingModule::$CONFIG_EMAIL_DEFAULT_RETURN_PATH, null);

        if( $unsent->getSend_from() != null){
            $headers['From'] = $unsent->getSend_from();
        }
        else if($default_send_from != null ){
            $headers['From'] = $default_send_from;
        }

        if( $return_path != null ){
            $headers['Return-Path'] = $return_path;
        }

        if( $cc != null ){
            $headers['Cc'] = $cc;
        }

        // TODO: Support additional headers

        if( count($headers) == 0){
            $headers = null;
        }

        // Allow for the suppression of email-sending
        // Enabling this feature will result in the module not sending any email
        // Instead, queued entries are updated as if they had been sent successfully
        if( ApplicationConfiguration::get(MessagingModule::$CONFIG_EMAIL_SUPPRESS_ALL, false)){
            $LOG->info("Suppressing sending of email due to configuration: " . MessagingModule::$CONFIG_EMAIL_SUPPRESS_ALL);
            $is_sent = true;
        }
        else{
            $LOG->info("Sending email to '$recipients' (cc: '$cc')");

            $is_sent = mail(
                $recipients,
                $unsent->getSubject(),
                $unsent->getBody(),
                $headers
            );
        }

        if( $is_sent ){
            $dao = new QueuedEmailDAO();
            $unsent->setSent_date(date('Y-m-d H:i:s'));
            $dao->save($unsent);

            return true;
        }
        else {
            $LOG->error("Unable to send $unsent");
            return false;
        }
    }

    private function filterToEmailsOfRole( $emailCsv, $rolename ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        $array = explode(',', $emailCsv);

        $params = array_merge(
            array($rolename),
            $array
        );

        $inArrayClause = implode(',', array_fill(0, count($array), '?'));

        $sql = "SELECT user.email
            FROM erasmus_user user
            JOIN user_role ur oN user.key_id = ur.user_id
            JOIN role r ON r.key_id = ur.role_id

            WHERE r.name = ? AND user.email IN ($inArrayClause)";

        $stmt = DBConnection::prepareStatement($sql);

        // Execute the statement
        if( $LOG->isTraceEnabled() ){
            $LOG->trace($sql);
        }

        if ($stmt->execute($params)) {
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN, 'email');

            // 'close' the statement
            $stmt = null;

            // Should be array of strings
            return implode(',', $result);
        }
        else {
            $error = $stmt->errorInfo();
            $LOG->error("Error filtering $rolename emails in (" . implode(',', $array) . "): " . $error[2]);

            // 'close' the statement
            $stmt = null;
            return new QueryError($error[2]);
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
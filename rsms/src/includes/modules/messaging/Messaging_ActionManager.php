<?php

class Messaging_ActionManager extends ActionManager {

    public function getAllMessageTypes(){
        // Get all Modules which declare message types
        $allMessageTypes = array();
        foreach(ModuleManager::getAllModules() as $module){
            if( $module instanceof MessageTypeProvider ){
                foreach( $module->getMessageTypes() as $mtype ){
                    $mtype->setMacroDescriptions( MacroResolverProvider::build($mtype)->resolveDescriptions());
                    $allMessageTypes[] = $mtype;
                }
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

        if( $LOG->isTraceEnabled() ){
            $LOG->trace($templates);
        }

        // FIXME: Ensure that all content is UTF-8 encoded before JSONing
        return $templates;
    }

    public function getContextDescriptor(MessageContext $context){
        $context_json = json_encode($context);

        return $context_json;
    }

    public function getContextFromMessage(Message $message, $base = null){
        $context = JsonManager::decode( $message->getContext_descriptor(), $base );
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

    /**
     * Generates a formatted message object for each Details array provided
     */
    public function buildFormattedMessages($template, $details){
        $formattedMessages = array();

        foreach($details as $messageDetails){
            $macromap = $messageDetails['macromap'];

            $corpus = $template->getCorpus();
            if( array_key_exists('body', $messageDetails) ){
                // Override Templated Body
                $corpus = $messageDetails['body'];
            }

            // Parse template body
            $body = $this->replaceMacros($macromap, $corpus);

            // Parse template subject
            $subject = $this->replaceMacros($macromap, $template->getSubject());

            // get recipients, from, etc
            $formattedMessages[] = new FormattedMessage(
                $subject,
                $body,
                $messageDetails['recipients'],
                $messageDetails['from'],
                $messageDetails['cc']
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

        // Append standard disclaimer
        $body = $unsent->getBody();
        $body .= "\n\n***This is an automatic email notification. Please do not reply to this message.***";

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

        // Enable HTML emails
        $headers['MIME-Version'] = "1.0";
        $headers['Content-Type'] = "text/html; charset=UTF-8";

        // TODO: Support additional headers

        if( count($headers) == 0){
            $headers = null;
        }
        else{
            // Headers is populated; implode and reassign so that this is a string
            $headers_str = '';
            foreach($headers as $k => $v){
                $headers_str .= "$k: $v\r\n";
            }
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
                $body,
                $headers_str
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

    public function toggleTemplateActive( $templateId ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);
        $templateId = $this->getValueFromRequest('templateId', $templateId);

        if( $templateId == null ){
            return new ActionError('No template was specified', 400);
        }

        $dao = new GenericDAO(new MessageTemplate());
        $template = $dao->getById( $templateId );

        if( $template == NULL ){
            return new ActionError("Template does not exist", 404);
        }

        // Toggle active status
        $LOG->debug("Toggle active status for: $template");
        $LOG->trace("Template is active: " . $template->getIs_active());
        $template->setIs_active( !$template->getIs_active() );
        $template = $dao->save($template);
        $LOG->trace("Template is active: " . $template->getIs_active());

        return $template->getIs_active();
    }

    public function createNewTemplate( MessageTypeDto $typeDto = NULL ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        if( $typeDto == null ){
            $typeDto = $this->convertInputJson();
        }

        if( !($typeDto instanceof MessageTypeDto) ){
            $LOG->error("Provided message type is not a MessageTypeDto: $typeDto");
            return new ActionError("Invalid message type", 400);
        }

        // Validate message type
        $moduleName = $typeDto->getModule();
        $module = ModuleManager::getModuleByName( $moduleName );
        if( $module == null ){
            $msg = "No such module: $moduleName";
            $LOG->error($msg);
            return new ActionError($msg, 404);
        }

        // Module is valid; match requested message type to Module's declarations
        $messageTypeName = $typeDto->getTypeName();
        $messageType = null;
        foreach($module->getMessageTypes() as $moduleType) {
            if( $moduleType->getTypeName() == $messageTypeName){
                $messageType = $moduleType;
                break;
            }
        }

        if( $messageType == null ){
            $msg = "No such message type '$messageTypeName' in module '$moduleName'";
            $LOG->error($msg);
            return new ActionError($msg, 404);
        }

        // Requested type/module is valid; create a new template
        $LOG->debug("Creating new message template for $messageType");
        $template = new MessageTemplate();
        $template->setIs_active(true);
        $template->setModule($messageType->getModule());
        $template->setMessage_type($messageType->getTypeName());
        $template->setTitle("New Template: $moduleName / $messageTypeName");

        $dao = new GenericDAO($template);
        $template = $dao->save($template);
        $LOG->debug("Saved new template: $template");

        return $template;
    }

    public function saveTemplate( $id, $templateDto = null){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $id = $this->getValueFromRequest('id', $id);

        if( $templateDto == null ){
            $templateDto = $this->convertInputJson();
        }

        if( !($templateDto instanceof MessageTemplate) ){
            $LOG->error("Provided template is not a MessageTemplate: $templateDto");
            return new ActionError("Invalid template", 400);
        }

        if( $id == null ){
            $LOG->warn("Template ID was not provided; inferring from request entity");
            $id = $templateDto->getKey_id();
        }

        $dao = new GenericDAO( new MessageTemplate() );
        $template = $dao->getById( $id );

        if( $template == null ){
            $msg = "No such template: $id";
            $LOG->error($msg);
            return new ActionError($msg, 404);
        }

        // TODO: Validate Template content (UTF-8 chars, etc)

        $template->setTitle( $templateDto->getTitle() );
        $template->setCorpus( $templateDto->getCorpus() );
        $template->setSubject( $templateDto->getSubject() );

        $LOG->debug("Saving $template");
        $template = $dao->save($template);

        return $template;
    }

    public function getEmails( $page, $size ){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        $pageNum = $this->getValueFromRequest('page', $page);
        $pageSize = $this->getValueFromRequest('size', $size);

        $paging = array(
            'page' => $pageNum,
            'size' => $pageSize
        );

        $emailDao = new QueuedEmailDAO();
        $resultPage = $emailDao->getQueue($paging, null, false, false);

        return $resultPage;
    }

    function previewMessage($message, $contexts){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );
        $LOG->trace("Build preview for $message");

        $messageType = $this->getMessageTypeDetails($message->getModule(), $message->getMessage_type());
        $LOG->trace("Previewing message of type: $messageType");

        $templates = $this->getTemplatesForMessage( $message );

        if( count($templates) > 0 ){
            $LOG->trace("Found " . count($templates) . " templates");
            $macroProvider = MacroResolverProvider::build( $messageType );

            // Ensure $contexts is an array
            $_ctxs = is_array($contexts) ? $contexts : array($contexts);
            if($LOG->isTraceEnabled()){
                $LOG->trace($_ctxs);
            }

            $previews = array();

            foreach($templates as $template){
                // Process macros for pre-filled message
                $macromap = array();

                foreach($_ctxs as $ctx){
                    $macromap = array_merge($macromap, $macroProvider->resolve($ctx) );
                }

                if($LOG->isTraceEnabled()){
                    $LOG->trace($macromap);
                }

                $details = array(
                    'macromap' => $macromap,
                    'recipients' => array(),
                    'from' => ''
                );

                $previews = array_merge($previews, $this->buildFormattedMessages($template, array($details)));
            }

            return $previews;
        }

        $LOG->warn("No templates found for $message");
        return array();
    }

    function replaceMacros($macromap, $content){
        return str_replace(
            array_keys($macromap),
            array_values($macromap),
            $content);
    }

    function getMessageTypeDetails( $moduleName, $messageTypeName ){
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
<?php

/**
 * Processor for LabInspectionSummaryReady messages.
 * Message context is a Department, User, and Year.
 */
class A_LabInspectionSummary_Processor implements MessageTypeProcessor {

    public function process(Message $message, $macroResolverProvider){
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->debug("Processing context for $message");

        // Processor should...
        //  Look up details from desscriptor
        $messenger = new Messaging_ActionManager();
        $context = $messenger->getContextFromMessage($message, new LabInspectionSummaryContext());

        // Look up department
        $departmentInfo = $this->getDepartment($context->department_id);
        $LOG->debug("Department: $departmentInfo");

        $context_macros = $macroResolverProvider->resolve( $context );
        $dept_macros = $macroResolverProvider->resolve( $departmentInfo );

        //  Construct macromap
        $macromap = array_merge($context_macros, $dept_macros);

        if( $LOG->isTraceEnabled() ){
            $LOG->trace($macromap);
        }

        // prepare email details
        $details = array(
            'recipients' => array($departmentInfo->getChair_email()),
            'from' => 'LabInspectionReports@ehs.sc.edu<RSMS Portal>',
            'macromap' => $macromap
        );

        $LOG->info("Done processing details for $message");
        if( $LOG->isTraceEnabled() ){
            $LOG->trace($details);
        }

        // Must return an array.
        // This message type doesn't fan out, so no more is needed here
        return array(
            $details
        );
    }

    function getDepartment($departmentId){
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->debug("Look up Department with ID $departmentId");

        $reportManager = new Reports_ActionManager();
        $info = $reportManager->getDepartmentInfo($departmentId);

        if( $info == null ){
            // ERROR - Dept doesn't exist
            throw new Exception("Departmnet $departmentId does not exist");
        }
        else if( $info instanceof ActionError ){
            // ERROR - Something went wrong
            throw new Exception("Error retrieving info for Department $departmentId: " . $info->getMessage());
        }

        if( $info->getChair_id() == null ){
            throw new Exception("Department '" . $info->getName() . "' has no Chair");
        }

        return $info;
    }

    function getDepartmentChairUser($deptChairUserId, $departmentId){
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->debug("Look up Department Chair user with ID $deptChairUserId");
        $userDao = new GenericDAO(new User());
        $chairUser = $userDao->getById($deptChairUserId);

        if( $chairUser == null ){
            // ERROR - User doesn't exist
            throw new Exception("Chair User $deptChairUserId does not exist");
        }

        $userRoles = array();
        foreach($chairUser->getRoles() as $role){
            $userRoles[] = $role->getName();
        }

        if( $chairUser->getPrimary_department_id() != $departmentId){
            // ERROR - wrong department user
            throw new Exception("Chair User $deptChairUserId belongs to another department: " . $chairUser->getPrimary_department_id());
        }
        else if( !in_array('Department Chair', $userRoles) ){
            // ERROR - user isn't a department chair
            throw new Exception("User $chairUser does not have the Department Chair role");
        }

        return $chairUser;
    }

    public static function getReportLink($departmentId, $reportYear){
        $urlBase = ApplicationConfiguration::get('server.web.url');
        $webRoot = WEB_ROOT;
        return "$urlBase$webRoot" . "reports/#/inspection-summary/reports/$departmentId/$reportYear";
    }
}

?>
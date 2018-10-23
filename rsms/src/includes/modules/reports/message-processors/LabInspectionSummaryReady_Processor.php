<?php

/**
 * Processor for LabInspectionSummaryReady messages.
 * Message context is a Department, User, and Year.
 */
class LabInspectionSummaryReady_Processor implements MessageTypeProcessor {

    public function process(Message $message){
        $LOG = Logger::getLogger(__CLASS__);
        $LOG->debug("Processing context for $message");

        // Processor should...
        //  Look up details from desscriptor
        $context = json_decode( $message->getContext_descriptor());

        $deptChairUserId = $context->user_id;
        $departmentId = $context->department_id;
        $reportYear = $context->report_year;

        // Look up target user
        $chairUser = $this->getDepartmentChairUser($deptChairUserId, $departmentId);
        $LOG->debug("Department Chair user: $chairUser");

        // Look up department
        $department = $this->getDepartment($departmentId);
        $LOG->debug("Department: $department");

        $email = $chairUser->getEmail();

        // Build link to summary report
        $link = $this->getReportLink($departmentId, $reportYear);

        //  determine who to send to / from / etc

        //  Construct macromap
        $macromap = array(
            '[Chair Name]' => $chairUser->getName(),
            '[Chair First Name]' => $chairUser->getFirst_name(),
            '[Chair Last Name]' => $chairUser->getLast_name(),
            '[Department Name]' => $department->getName(),
            '[Report Year]' => $reportYear,
            '[Report Link]' => $link
        );

        $details = array(
            "recipients" => array($email),
            "from" => 'LabInspectionReports@ehs.sc.edu<RSMS Portal>',
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
        $dao = new GenericDAO( new Department() );
        $dept = $dao->getById($departmentId);

        if( $dept == null ){
            // ERROR - Dept doesn't exist
            throw new Exception("Departmnet $departmentId does not exist");
        }

        return $dept;
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

    function getReportLink($departmentId, $reportYear){
        $urlBase = ApplicationConfiguration::get('server.web.url');
        $webRoot = WEB_ROOT;
        return "$urlBase$webRoot" . "reports/#/inspection-summary/reports/$departmentId/$reportYear";
    }
}

?>
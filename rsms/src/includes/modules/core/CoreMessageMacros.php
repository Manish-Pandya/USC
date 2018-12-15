<?php

class CoreMessageMacros {

    public static function getResolvers(){
        $resolvers = array();

        // General
        $resolvers[] = new MacroResolver(
            null,
            '[RSMS Login]', 'URL of the RSMS Login page',
            function(){
                $urlBase = ApplicationConfiguration::get('server.web.url');
                $loginPath = ApplicationConfiguration::get('server.web.LOGIN_PAGE', '/rsms');
                return "$urlBase$loginPath";
            }
        );

        // Inspection
        $resolvers[] = new MacroResolver(
            'Inspection',
            '[PI Name]', 'Full name of the Principal Investigator',
            function(Inspection $inspection){
                return $inspection->getPrincipalInvestigator()->getUser()->getName();
            }
        );

        $resolvers[] = new MacroResolver(
            'Inspection',
            '[CAP Due Date]', 'Due Date of the inspection\'s Corrective Action Plan',
            function(Inspection $inspection){
                // Add 14 days to the Notification date to get the Due date
                $due = strtotime($inspection->getNotification_date() . ' + 14 days');
                // Format the updated stamp
                return date('F jS, Y', $due);
            }
        );

        $resolvers[] = new MacroResolver(
            'Inspection',
            '[Start Date]', 'Date on which the Inspection was started',
            function(Inspection $inspection){
                $date = strtotime($inspection->getDate_started());
                return date('F jS, Y', $date);
            }
        );

        $resolvers[] = new MacroResolver(
            'Inspection',
            '[Link]', 'URL of the Inspection Report page',
            function(Inspection $inspection){
                return LabInspectionReminder_Processor::getInspectionReportLink(
                    $inspection->getKey_id()
                );
            }
        );

        $resolvers[] = new MacroResolver(
            'LabInspectionReminderContext',
            '[Inspection Report Link]', 'URL of the Inspection Report page',
            function(LabInspectionReminderContext $context){
                return LabInspectionReminder_Processor::getInspectionReportLink(
                    $context->inspection_id
                );
            }
        );

        $resolvers[] = new MacroResolver(
            'LabInspectionStateDto',
            '[Corrected Deficiencies]', 'Number of deficiencies Corrected during Inspection',
            function(LabInspectionStateDto $state){
                return $state->getCorrecteds();
            }
        );

        $resolvers[] = new MacroResolver(
            'LabInspectionStateDto',
            '[Deficiency or Deficiencies]', 'Plural form ("Deficiency" or "Deficiencies") based on [Corrected Deficiencies]',
            function(LabInspectionStateDto $state){
                return $state->getCorrecteds() == 1 ? 'Deficiency' : 'Deficiencies';
            }
        );

        $resolvers[] = new MacroResolver(
            'LabInspectionStateDto',
            '[It or Each]', 'Plural form ("it" or "each deficiency") based on [Corrected Deficiencies]',
            function(LabInspectionStateDto $state){
                return $state->getCorrecteds() == 1 ? 'it' : 'Each deficiency';
            }
        );

        return $resolvers;
    }

}

?>
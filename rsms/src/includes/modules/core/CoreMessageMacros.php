<?php

class CoreMessageMacros {

    public static function getResolvers(){
        $resolvers = array();

        // Inspection
        $resolvers[] = new MacroResolver(
            Inspection,
            '[PI Name]', 'Full name of the Principal Investigator',
            function(Inspection $inspection){
                return $inspection->getPrincipalInvestigator()->getUser()->getName();
            }
        );

        $resolvers[] = new MacroResolver(
            Inspection,
            '[CAP Due Date]', 'URL of the Summary Report page',
            function(Inspection $inspection){
                // Add 14 days to the Notification date to get the Due date
                $due = strtotime($inspection->getNotification_date() . ' + 14 days');
                // Format the updated stamp
                return date('Y-m-d', $due);
            }
        );

        $resolvers[] = new MacroResolver(
            LabInspectionReminderContext,
            '[Inspection Report Link]', 'URL of the Summary Report page',
            function(LabInspectionReminderContext $context){
                return LabInspectionReminder_Processor::getInspectionReportLink(
                    $context->inspection_id
                );
            }
        );

        return $resolvers;
    }

}

?>
<?php

class ChairReportModule implements RSMS_Module, MessageTypeProvider {

    public static $NAME = 'Chair Report';

    public static $MTYPE_INSPECTION_SUMMARY_READY = 'LabInspectionSummaryReady';
    public static $MTYPE_INSPECTION_SUMMARY_YEARLY = 'LabInspectionSummaryYearly';

    public function getModuleName(){
        return self::$NAME;
    }

    public function getUiRoot(){
        return '/reports';
    }

    public function isEnabled() {
        // Enabled if the referer comes from our UI Root
        // OR if the 'reports' param is set
        if(	( isset($_SERVER["HTTP_REFERER"]) && stristr($_SERVER["HTTP_REFERER"], '/reports/' ) ) || isset($_GET['reports']))
            return true;

        return false;
    }

    public function getActionManager(){
        return new Reports_ActionManager();
    }

    public function getActionConfig(){
        return Reports_ActionMappingFactory::readActionConfig();
    }

    public function getMessageTypes(){
        // completely describe a type:
        //  Name, Processor, Context Types, Macro Resolvers
        return array(
            new MessageTypeDto(
                self::$NAME,
                ChairReportModule::$MTYPE_INSPECTION_SUMMARY_READY,
                'Automatic email sent when ' . LabInspectionSummaryReady_Task::$COMPLETION_THRESHOLD . '% of the PIs in a department have been inspected.',
                'LabInspectionSummaryReady_Processor',
                array('DepartmentDetailDto', 'LabInspectionSummaryContext')
            ),

            new MessageTypeDto(
                self::$NAME,
                ChairReportModule::$MTYPE_INSPECTION_SUMMARY_YEARLY,
                'Automatic email sent on December 1st each year.',
                'LabInspectionSummaryYearly_Processor',
                array('DepartmentDetailDto', 'LabInspectionSummaryContext')
            )
        );
    }

    public function getMacroResolvers(){
        return ChairReportMessageMacros::getResolvers();
    }
}
?>
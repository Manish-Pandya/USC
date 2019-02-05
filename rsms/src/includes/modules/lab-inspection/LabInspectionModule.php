<?php

class LabInspectionModule implements RSMS_Module, MessageTypeProvider {
    public static $NAME = 'Lab Inspection';

    public static $MTYPE_CAP_REMINDER_DUE = 'LabInspectionReminderCAPDue';
    public static $MTYPE_CAP_REMINDER_OVERDUE = 'LabInspectionReminderCAPOverdue';
    public static $MTYPE_CAP_REMINDER_PENDING = 'LabInspectionReminderPendingCAPs';
    public static $MTYPE_CAP_APPROVED = 'LabInspectionApprovedCAP';

    public static $MTYPE_NO_DEFICIENCIES = 'PostInspectionNoDeficiencies';
    public static $MTYPE_DEFICIENCIES_FOUND = 'PostInspectionDeficienciesFound';
    public static $MTYPE_DEFICIENCIES_CORRECTED = 'PostInspectionDeficienciesCorrected';

    public static $MTYPE_CAP_SUBMITTED_ALL_COMPLETE = 'LabInspectionAllCompletedCAPSubmitted';
    public static $MTYPE_CAP_SUBMITTED_PENDING = 'LabInspectionPendingCAPSubmitted';

    public function getModuleName(){
        return self::$NAME;
    }

    public function getUiRoot(){
        return '/';
    }

    public function isEnabled() {
        return true;
    }

    public function getActionManager(){
        return new LabInspection_ActionManager();
    }

    public function getActionConfig(){
        return LabInspection_ActionMappingFactory::readActionConfig();
    }

    public function getMessageTypes(){
        return array(
            // RSMS-752: Inspection Reminders
            new MessageTypeDto(self::$NAME, self::$MTYPE_CAP_REMINDER_DUE,
                'Automatic email is sent one week before the corrective action plan due date if the CAP has not already been submitted (i.e. one week after the lab inspection report is sent).',
                'LabInspectionReminder_Processor',
                array('Inspection', 'LabInspectionReminderContext')),

            new MessageTypeDto(self::$NAME, self::$MTYPE_CAP_REMINDER_OVERDUE,
                'Automatic email sent the day after the corrective action plan due date if the CAP has not yet been submitted. Recurring email sent every week until the CAP is submitted or until December 31st of the inspection year.',
                'LabInspectionReminder_Processor',
                array('Inspection', 'LabInspectionReminderContext')),

            // RSMS-826: Pending CAP Reminder
            new MessageTypeDto(self::$NAME, self::$MTYPE_CAP_REMINDER_PENDING,
                'Automatic email sent two weeks after the CAP is submitted, ONLY when there are pending CAPs. Recurring email sent every two weeks until all pending CAPs are updated to complete or until December 31st of the inspection year.',
                'LabInspectionReminder_Processor',
                array('Inspection', 'LabInspectionReminderContext')),

            new MessageTypeDto(self::$NAME, self::$MTYPE_CAP_APPROVED,
                'Automatic email event is sent when corrective action plan is approved by EHS.',
                'LabInspectionUpdatedMessage_Processor',
                array('Inspection', 'LabInspectionReminderContext')),

            // RSMS-827: Send email on submission of fully-completed CAP
            new MessageTypeDto(self::$NAME, self::$MTYPE_CAP_SUBMITTED_ALL_COMPLETE,
                'Automatic confirmation email is sent after PI submits CAP that has no pending (all Completed).',
                'LabInspectionUpdatedMessage_Processor',
                array('Inspection', 'LabInspectionReminderContext')),

            // RSMS-828: Send email on submission of Pending CAP
            new MessageTypeDto(self::$NAME, self::$MTYPE_CAP_SUBMITTED_PENDING,
                'Automatic confirmation email is sent after PI submits CAP that has one or more pending.',
                'LabInspectionUpdatedMessage_Processor',
                array('Inspection', 'LabInspectionReminderContext')),

            // RSMS-739: Refactor existing Inspections email generation to be handled by Email Hub
            new MessageTypeDto(self::$NAME, self::$MTYPE_NO_DEFICIENCIES,
                'Inspection Report Email template - No Deficiencies Found during inspection',
                'InspectionEmailMessage_Processor',
                array('Inspection', 'LabInspectionStateDto')),

            new MessageTypeDto(self::$NAME, self::$MTYPE_DEFICIENCIES_FOUND,
                'Inspection Report Email template - Deficiencies Found during inspection',
                'InspectionEmailMessage_Processor',
                array('Inspection', 'LabInspectionStateDto')),

            new MessageTypeDto(self::$NAME, self::$MTYPE_DEFICIENCIES_CORRECTED,
                'Inspection Report Email template - Deficiencies Found and Corrected during inspection',
                'InspectionEmailMessage_Processor',
                array('Inspection', 'LabInspectionStateDto'))
        );
    }

    public function getMacroResolvers(){
        return LabInspectionMessageMacros::getResolvers();
    }
}
?>
<?php

class CoreModule implements RSMS_Module, MessageTypeProvider {
    public static $NAME = 'Core';

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
        return new ActionManager();
    }

    public function getActionConfig(){
        return ActionMappingFactory::readActionConfig();
    }

    public function getMessageTypes(){
        return array(
            // RSMS-752: Inspection Reminders
            new MessageTypeDto(self::$NAME, 'LabInspectionReminderCAPDue',
                'Automatic email is sent one week before the corrective action plan due date if the CAP has not already been submitted (i.e. one week after the lab inspection report is sent).',
                LabInspectionReminder_Processor,
                array(Inspection, LabInspectionReminderContext)),

            new MessageTypeDto(self::$NAME, 'LabInspectionReminderCAPOverdueDue',
                'Automatic email is sent the day after the corrective action plan due date if the CAP has not already been submitted (i.e. two weeks plus one day after the lab inspection report is sent).',
                LabInspectionReminder_Processor,
                array(Inspection, LabInspectionReminderContext)),

            new MessageTypeDto(self::$NAME, 'LabInspectionApprovedCAP',
                'Automatic email event is sent when corrective action plan is approved by EHS.',
                LabInspectionReminder_Processor,
                array(Inspection, LabInspectionReminderContext))
        );
    }

    public function getMacroResolvers(){
        return CoreMessageMacros::getResolvers();
    }
}
?>
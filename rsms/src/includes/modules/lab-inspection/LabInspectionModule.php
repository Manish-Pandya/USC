<?php

class LabInspectionModule implements RSMS_Module, MessageTypeProvider, MyLabWidgetProvider {
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

    public function getMyLabWidgets( User $user ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $widgets = array();
        $_WIDGET_GROUP_PROFILE = "000_my-profile";
        $_WIDGET_GROUP_INSPECTIONS = "001_lab-inspections";

        $manager = $this->getActionManager();

        // Get relevant PI for lab
        $principalInvestigator = $manager->getPIByUserId( $user->getKey_id() );

        // Collect User Info
        // Notes:
        //   Phone number inclusion varies by Role:
        //     PI user – Office Phone and Emergency Phone.
        //     Lab Contact – Lab Phone and Emergency Phone.
        //     Lab Personnel – Lab Phone only
        $userData = array(
            'First_name' => $user->getFirst_name(),
            'Last_name' => $user->getLast_name(),
            'Name' => $user->getName(),
            'Position' => $user->getPosition()
        );

        if( CoreSecurity::userHasRoles($user, array('Principal Investigator')) ){
            $userData['Office_phone'] = $user->getOffice_phone();
            $userData['Emergency_phone'] = $user->getEmergency_phone();
        }
        else{
            if( CoreSecurity::userHasRoles($user, array('Lab Personnel')) ){
                $userData['Lab_phone'] = $user->getLab_phone();
            }

            if( CoreSecurity::userHasRoles($user, array('Lab Contact')) ){
                $userData['Emergency_phone'] = $user->getEmergency_phone();
            }
        }

        $userInfoWidget = new MyLabWidgetDto();
        $userInfoWidget->title = "My Profile";
        $userInfoWidget->icon = "icon-user";
        $userInfoWidget->group = $_WIDGET_GROUP_PROFILE;
        $userInfoWidget->template = 'my-profile';
        $userInfoWidget->data = new GenericDto($userData);
        $widgets[] = $userInfoWidget;

        $piInfoWidget = new MyLabWidgetDto();
        $piInfoWidget->title = "Principal Investigator Details";
        $piInfoWidget->icon = "icon-user-3";
        $piInfoWidget->group = $_WIDGET_GROUP_PROFILE;
        $piInfoWidget->template = 'pi-profile';
        $piInfoWidget->data = $manager->buildUserDTO($user)->PrincipalInvestigator;

        if( !CoreSecurity::userHasRoles($user, array('Principal Investigator')) ){
            // If user is not a PI, omit Lab Location data
            $piInfoWidget->data->Buildings = null;
            $piInfoWidget->data->Rooms = null;
        }

        $widgets[] = $piInfoWidget;

        // Collect inspections
        $open_inspections = array();
        $archived_inspections = array();

        if( isset($principalInvestigator) ){
            // Filter inspections by year based on current user role
            $inspections = $principalInvestigator->getInspections();

            $minYear = CoreSecurity::userHasRoles($user, array("Admin"))
                ? 2017
                : 2018;

            foreach($inspections as $key => $inspection){
                if( $inspection->getIsArchived() ){
                    $closedYear = date_create($inspection->getDate_closed())->format("Y");

                    if( $closedYear < $minYear ){
                        $LOG->debug("Omit $inspection (closed $closedYear) for MyLab");
                        unset($inspections[$key]);
                    }
                }
            }

            $principalInvestigator->setInspections($inspections);

            // Collect Open vs Archived
            foreach( $inspections as $i ){
                if( $i->getIsArchived() ){
                    $archived_inspections[] = $i;
                }
                else{
                    $open_inspections[] = $i;
                }
            }
        }

        // Group by 'Lab Inspections'
        // TODO: Combine into a single Lab Inspections widget
        // Pending Inspection Reports
        $pendingWidget = new MyLabWidgetDto();
        $pendingWidget->group = $_WIDGET_GROUP_INSPECTIONS;
        $pendingWidget->title = "Pending Reports";
        $pendingWidget->icon = "icon-search-2";
        $pendingWidget->template = "inspection-table";
        $pendingWidget->data = $open_inspections;
        $widgets[] = $pendingWidget;

        // Archived Inspection Reports
        $archivedWidget = new MyLabWidgetDto();
        $archivedWidget->group = $_WIDGET_GROUP_INSPECTIONS;
        $archivedWidget->title = "Archived Reports";
        $archivedWidget->icon = "icon-search-2";
        $archivedWidget->template = "inspection-table";
        $archivedWidget->data = $archived_inspections;
        $widgets[] = $archivedWidget;

        return $widgets;
    }
}
?>
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

    public static $MYLAB_GROUP_PROFILE = "000_my-profile";
    public static $MYLAB_GROUP_INSPECTIONS = '001_lab-inspections';

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

        $manager = $this->getActionManager();

        // Get relevant PI for lab
        $principalInvestigator = $manager->getPrincipalInvestigatorOrSupervisorForUser( $user );
        $profileData = $manager->getMyProfile( $user->getKey_id() );

        $userInfoWidget = new MyLabWidgetDto();
        $userInfoWidget->title = "My Profile";
        $userInfoWidget->icon = "icon-user";
        $userInfoWidget->group = self::$MYLAB_GROUP_PROFILE;
        $userInfoWidget->template = 'my-profile';
        $userInfoWidget->data = $profileData;

        if( !isset($profileData->Position) ){
            $profilePositionWidget = new MyLabWidgetDto();
            $profilePositionWidget->title = "My Profile - Position";
            $profilePositionWidget->icon = "icon-user";
            $profilePositionWidget->template = 'my-profile-position';

            $userInfoWidget->actionWidgets = array( $profilePositionWidget );
        }

        $widgets[] = $userInfoWidget;

        if( isset( $principalInvestigator ) ){
            $piInfoWidget = new MyLabWidgetDto();
            $piInfoWidget->title = "Principal Investigator Details";
            $piInfoWidget->icon = "icon-user-3";
            $piInfoWidget->group = self::$MYLAB_GROUP_PROFILE;
            $piInfoWidget->template = 'pi-profile';
            $piInfoWidget->data = new GenericDto(array(
                'pi' => $manager->buildPIDTO($principalInvestigator)
            ));

            if( !CoreSecurity::userHasRoles($user, array('Principal Investigator')) ){
                // If user is not a PI, omit Lab Location data
                $piInfoWidget->data->pi->Buildings = null;
                $piInfoWidget->data->pi->Rooms = null;
            }
            else {
                // Display Help block for PI users
                $helpContact = $manager->getUserByUsername(ApplicationConfiguration::get('server.web.HELP_CONTACT_USERNAME'));

                if( isset($helpContact) ){
                    $piInfoWidget->data->help = new GenericDto(array(
                        'Name' => $helpContact->getName(),
                        'Email' => $helpContact->getEmail(),
                        'Office_phone' => $helpContact->getOffice_phone()
                    ));
                }
            }

            $widgets[] = $piInfoWidget;

            // Collect inspections
            $inspections = array();
            if( isset($principalInvestigator) ){
                // Filter inspections by year based on current user role
                $inspections = $principalInvestigator->getInspections();

                $minYear = CoreSecurity::userHasRoles($user, array("Admin"))
                    ? 2017
                    : 2018;

                /* Statuses to display in My Lab */
                $show_statuses = array(
                    "CLOSED OUT",
                    "INCOMPLETE CAP",
                    "OVERDUE CAP",
                    "SUBMITTED CAP"
                );

                /** Statuses to Notify in mylab */
                $notify_statuses = array(
                    "INCOMPLETE CAP",
                    "OVERDUE CAP"
                );

                foreach($inspections as $key => $inspection){
                    if( in_array($inspection->getStatus(), $show_statuses) ){
                        $closedYear = date_create($inspection->getDate_closed())->format("Y");

                        if( $closedYear < $minYear ){
                            $LOG->debug("Omit $inspection (closed $closedYear) for MyLab");
                            unset($inspections[$key]);
                        }
                    }
                    else {
                        $LOG->debug("Omit $inspection (still open) for MyLab");
                        unset($inspections[$key]);
                    }
                }

                $principalInvestigator->setInspections($inspections);
            }

            $inspectionsWidget = new MyLabWidgetDto();
            $inspectionsWidget->group = self::$MYLAB_GROUP_INSPECTIONS;
            $inspectionsWidget->title = "Inspection Reports";
            $inspectionsWidget->icon = "icon-search-2";
            $inspectionsWidget->template = "inspection-table";
            $inspectionsWidget->fullWidth = 1;
            $inspectionsWidget->data = $inspections;

            $inspectionsWidget->alerts = array();
            foreach( $inspectionsWidget->data as $inspection ){
                if( in_array($inspection->getStatus(), $notify_statuses) ){
                    $inspectionsWidget->alerts[] = $inspection->getKey_id();
                }
            }

            $widgets[] = $inspectionsWidget;
        }

        return $widgets;
    }
}
?>
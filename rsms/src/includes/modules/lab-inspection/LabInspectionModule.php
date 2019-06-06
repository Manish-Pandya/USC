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

    private $manager;

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
        if( !isset($this->manager) ){
            $this->manager = new LabInspection_ActionManager();
        }

        return $this->manager;
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

        // Add profile widget
        $widgets[] = $this->buildUserInfoWidget( $profileData, $principalInvestigator );

        // Ad PI-related widgets, if we have PI data
        if( isset( $principalInvestigator ) ){
            foreach( $this->buildPIWidgets( $user, $principalInvestigator) as $w ){
                $widgets[] = $w;
            }
        }

        return $widgets;
    }

    private function buildUserInfoWidget( &$profileData, &$principalInvestigator ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

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

        return $userInfoWidget;
    }

    private function buildPIWidgets( &$user, &$principalInvestigator ){
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        $pi_widgets = array();

        // Compile all PI details into a single DTO
        // We'll pass this to individual widget-builders so we don't need to query again
        $piDto = $this->manager->buildPIDTO($principalInvestigator);

        $piInfoWidget = $this->buildPIWidgets_info( $user, $principalInvestigator, $piDto );
        $pi_widgets[] = $piInfoWidget;

        if( CoreSecurity::userHasRoles($user, array('Principal Investigator')) ){
            $piPersonnelWidget = $this->buildPIWidgets_personnel( $user, $principalInvestigator, $piDto);
            $pi_widgets[] = $piPersonnelWidget;

            $piLocationWidget = $this->buildPIWidgets_locations( $user, $principalInvestigator, $piDto );
            $pi_widgets[] = $piLocationWidget;

            // Display Help block for PI users
            $helpContact = $this->manager->getUserByUsername(ApplicationConfiguration::get('server.web.HELP_CONTACT_USERNAME'));
            if( isset($helpContact) ){
                $helpContactDto = new GenericDto(array(
                    'Name' => $helpContact->getName(),
                    'Email' => $helpContact->getEmail(),
                    'Office_phone' => $helpContact->getOffice_phone()
                ));

                $piInfoWidget->data->help = $helpContactDto;
                $piLocationWidget->data->help = $helpContactDto;
                $piPersonnelWidget->data->help = $helpContactDto;
            }

        }

        $pi_widgets[] = $this->buildPIWidgets_inspections( $user, $principalInvestigator );

        return $pi_widgets;
    }

    private function buildPIWidgets_info(User &$user, PrincipalInvestigator &$principalInvestigator, $piDto) : MyLabWidgetDto {
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        // remove pi data we don't need here
        $data = clone $piDto;
        $data->Buildings = null;
        $data->Rooms = null;
        $data->LabPersonnel = null;

        $piInfoWidget = new MyLabWidgetDto();
        $piInfoWidget->title = "Principal Investigator Details";
        $piInfoWidget->icon = "icon-user-3";
        $piInfoWidget->group = self::$MYLAB_GROUP_PROFILE;
        $piInfoWidget->template = 'pi-profile';
        $piInfoWidget->data = new GenericDto(array(
            'pi' => $data
        ));

        return $piInfoWidget;
    }

    private function buildPIWidgets_locations(User &$user, PrincipalInvestigator &$principalInvestigator, $piDto ) : MyLabWidgetDto {
        $piLocationWidget = new MyLabWidgetDto();
        $piLocationWidget->title = "Lab Locations";
        $piLocationWidget->icon = "icon-location";
        $piLocationWidget->group = self::$MYLAB_GROUP_PROFILE;
        $piLocationWidget->template = 'pi-locations';
        $piLocationWidget->data = new GenericDto(array(
            'Buildings' => $piDto->Buildings,
            'Rooms' => $piDto->Rooms
        ));

        return $piLocationWidget;
    }

    private function buildPIWidgets_inspections(User &$user, PrincipalInvestigator &$principalInvestigator ) : MyLabWidgetDto {
        $LOG = Logger::getLogger( __CLASS__ . '.' . __FUNCTION__ );

        // Collect inspections
        $inspections = array();
        $inspectionDtos = array();
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
                    $startedYear = date_create($inspection->getDate_started())->format("Y");

                    if( $closedYear < $minYear ){
                        // Closed prior to minYear
                        $LOG->debug("Omit $inspection (closed $closedYear) for MyLab");
                        unset($inspections[$key]);
                    }
                    else if( $startedYear < $minYear ){
                        // Started prior to minYear
                        $LOG->debug("Omit $inspection (started $startedYear) for MyLab");
                        unset($inspections[$key]);
                    }
                }
                else {
                    $LOG->debug("Omit $inspection (still open) for MyLab");
                    unset($inspections[$key]);
                }
            }

            // Look up hazard info for displayable inspections
            $dao = new InspectionDAO();
            foreach($inspections as $inspection){
                $inspectionDtos[] = new GenericDto(array(
                    'Key_id' => $inspection->getKey_id(),
                    'Status' => $inspection->getStatus(),
                    'Date_started' => $inspection->getDate_started(),
                    'Is_rad' => $inspection->getIs_rad(),
                    'HazardInfo' => $dao->getInspectionHazardInfo($inspection->getKey_id()),
                    'Inspectors' => array_map( function($i){ return $i->getName(); }, $inspection->getInspectors())
                ));
            }
        }

        $inspectionsWidget = new MyLabWidgetDto();
        $inspectionsWidget->group = self::$MYLAB_GROUP_INSPECTIONS;
        $inspectionsWidget->title = "Lab Inspection Reports";
        $inspectionsWidget->icon = "icon-search-2";
        $inspectionsWidget->template = "inspection-table";
        $inspectionsWidget->fullWidth = 1;
        $inspectionsWidget->data = $inspectionDtos;

        $inspectionsWidget->alerts = array();
        foreach( $inspectionsWidget->data as $inspection ){
            if( in_array($inspection->Status, $notify_statuses) ){
                $inspectionsWidget->alerts[] = $inspection->Key_id;
            }
        }

        return $inspectionsWidget;
    }

    private function buildPIWidgets_personnel( User &$user, PrincipalInvestigator &$principalInvestigator, $piDto) : MyLabWidgetDto {
        $personnelWidget = new MyLabWidgetDto();
        $personnelWidget->group = self::$MYLAB_GROUP_PROFILE;
        $personnelWidget->title = "Lab Personnel";
        $personnelWidget->icon = "icon-users";
        $personnelWidget->template = "pi-personnel";
        $personnelWidget->data = new GenericDto(array(
            'LabPersonnel' => $piDto->LabPersonnel
        ));

        return $personnelWidget;
    }
}
?>
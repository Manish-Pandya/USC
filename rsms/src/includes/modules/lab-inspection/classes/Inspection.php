<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Inspection extends GenericCrud implements ISelectWithJoins {

    /** Name of the DB Table */
    protected static $TABLE_NAME = "inspection";

    /** Key/Value Array listing column names mapped to their types */
    protected static $COLUMN_NAMES_AND_TYPES = array(
        //inspectors are a relationship
        "principal_investigator_id" => "integer",
        //responses are a relationship
        //rooms are a relationship
        "date_started"	=> "timestamp",
        "date_closed"	=> "timestamp",
        "notification_date"	=> "timestamp",
        "cap_submitted_date"	=> "timestamp",
        "cap_submitter_id"      => "integer",
        "cap_approver_id"       => "integer",
        "schedule_month"	=> "text",
        "schedule_year"		=> "text",
        "note"			=> "text",
        //GenericCrud
        "key_id"			=> "integer",
        "date_created"		=> "timestamp",
        "date_last_modified"	=> "timestamp",
        "is_active"			=> "boolean",
        "last_modified_user_id"			=> "integer",
        "created_user_id"	=> "integer",
        "cap_complete"      => "integer",
        "is_rad"			=> "boolean"
    );

    /** Relationships */
    public static $ROOMS_RELATIONSHIP = array(
            "className"	=>	"Room",
            "tableName"	=>	"inspection_room",
            "keyName"	=>	"room_id",
            "foreignKeyName"	=>	"inspection_id"
    );

    public static $RESPONSES_RELATIONSHIP = array(
            "className"	=>	"Response",
            "tableName"	=>	"response",
            "keyName"	=>	"key_id",
            "foreignKeyName"	=>	"inspection_id"
    );

    public static $INSPECTORS_RELATIONSHIP = array(
            "className"	=>	"Inspector",
            "tableName"	=>	"inspection_inspector",
            "keyName"	=>	"inspector_id",
            "foreignKeyName"	=>	"inspection_id"
    );

    public static $CHECKLISTS_RELATIONSHIP = array(
            "className"	=>	"Checklist",
            "tableName"	=>	"inspection_checklist",
            "keyName"	=>	"checklist_id",
            "foreignKeyName"	=>	"inspection_id"
    );

    public static $INSPECTION_WIPES_TESTS_RELATIONSHIP = array(
            "className"	=>	"InspectionWipeTest",
            "tableName"	=>	"inspection_wipe_test",
            "keyName"	=>	"key_id",
            "foreignKeyName"	=>	"inspection_id"
    );

    public static $INSPECTION_LAB_PERSONNEL_RELATIONSHIP = array(
            "className"	=>	"User",
            "tableName"	=>	"inspection_personnel",
            "keyName"	=>	"personnel_id",
            "foreignKeyName"	=>	"inspection_id"
    );

	public static $SELECT_INSPECTION_STATUS_RELATIONSHIP = array(
		"tableName" => 'inspection_status',
		"keyName" 	=>  "key_id",
		"className" => 'Inspection',
		"foreignKeyName"	=>  "inspection_id",
		"columns" => array(
			'inspection_status' => 'text'
        ),
        "columnAliases" => array(
            "inspection_status" => "status"
        )
	);


    /** Array of Inspector entities that took part in this Inspection */
    private $inspectors;

    /** Reference to the PrincipalInvestigator being inspected */
    private $principalInvestigator;
    private $principal_investigator_id;

    /** Array of User entities which took part in this Inspection as Lab Personnel (Contacts) */
    private $labPersonnel;

    /** Array of Response entities */
    private $responses;

    /** Array of Checklist entities */
    private $checklists;

    /** Date and time this Inspection began */
    private $date_started;

    /** Date and time this Inspection was completed */
    private $date_closed;

    /** Notes about this inspection */
    private $note;

    /** Date and time the inspection was finalized and the report provided to lab personnel */
    private $notification_date;

    /** decorator property to return an array of all Deficiency Selections in an inspection**/
    private $deficiency_selections;

    private $cap_submitted_date;

    private $schedule_year;

    private $schedule_month;

    private $cap_complete;

    private $cap_due_date;

    /** is this inspection archived, or can it still be edited **/
    private $isArchived;

    /** is this a radiation inspection **/
    private  $is_rad;

    private $hasDeficiencies = true;

    /**decorator to translate schedule month property into month name so that it doesn't have to be done repeatedly on client**/
    private $text_schedule_month;

    private $inspection_wipe_tests;

    /**
     * the id of the user who submitted the corrective action plan for this inspection
     */
    private $cap_submitter_id;
    private $cap_submitter_name;

    private $cap_approver_id;
    private $cap_approver_name;

    private $rooms;
    private $roomIds;

    private $status;

    public function __construct(){

    }

    public static function defaultEntityMaps(){
        $entityMaps = array();
        $entityMaps[] = EntityMap::eager("getInspectors");
        $entityMaps[] = EntityMap::eager("getLabPersonnel");
        $entityMaps[] = EntityMap::eager("getRooms");
        $entityMaps[] = EntityMap::eager("getResponses");
        $entityMaps[] = EntityMap::eager("getDeficiency_selections");
        $entityMaps[] = EntityMap::eager("getPrincipalInvestigator");
        $entityMaps[] = EntityMap::eager("getStatus");
        $entityMaps[] = EntityMap::lazy("getChecklists");
        $entityMaps[] = EntityMap::lazy("getInspection_wipe_tests");

        return $entityMaps;
    }

    // Required for GenericCrud
    public function getTableName(){
        return self::$TABLE_NAME;
    }

    public function getColumnData(){
        return self::$COLUMN_NAMES_AND_TYPES;
    }

	public function selectJoinReleationships(){
		return array(
			DataRelationship::fromArray(self::$SELECT_INSPECTION_STATUS_RELATIONSHIP)
		);
	}

    public function getInspectors(){
        if( $this->inspectors == null && $this->hasPrimaryKeyValue() ){
            $thisDAO = new GenericDAO($this);
            $this->inspectors = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$INSPECTORS_RELATIONSHIP));
        }

        return $this->inspectors;
    }
    public function setInspectors($inspectors){ $this->inspectors = $inspectors; }

    public function getLabPersonnel(){
        if( $this->labPersonnel == null ){
            // Read lab personnel (include inactive users for historical purposes)
            $thisDAO = new GenericDAO($this);
            $this->labPersonnel = $thisDAO->getRelatedItemsById(
                $this->getKey_id(),
                DataRelationship::fromArray(self::$INSPECTION_LAB_PERSONNEL_RELATIONSHIP)
            );
        }

        return $this->labPersonnel;
    }
    public function setLabPersonnel( $personnel ){ $this->labPersonnel = $personnel; }

    public function getChecklists(){
        $thisDAO = new GenericDAO($this);
        if($this->checklists == NULL)$this->checklists = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$CHECKLISTS_RELATIONSHIP));
        foreach ($this->checklists as &$checklist){
            $checklist->setInspectionId($this->key_id);
        }
        return $this->checklists;
    }
    public function setChecklists($checklists){ $this->checklists = $checklists; }

    public function getPrincipalInvestigator(){
        $piDAO = new GenericDAO(new PrincipalInvestigator());
        $this->principalInvestigator = $piDAO->getById($this->principal_investigator_id);
        return $this->principalInvestigator;
    }
    public function setPrincipalInvestigator($principalInvestigator){
        $this->principalInvestigator = $principalInvestigator;
    }

    public function getPrincipal_investigator_id(){ return $this->principal_investigator_id; }
    public function setPrincipal_investigator_id($principal_investigator_id){ $this->principal_investigator_id = $principal_investigator_id; }

    public function getRooms(){
        if( $this->rooms == null ){
            $thisDAO = new GenericDAO($this);
            $this->rooms = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$ROOMS_RELATIONSHIP));
        }

        return $this->rooms;
    }
    public function setRooms($rooms){
        $this->rooms = $rooms;
    }

    public function getResponses(){
        $thisDAO = new GenericDAO($this);
        $this->responses = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$RESPONSES_RELATIONSHIP));
        return $this->responses;
    }
    public function setResponses($responses){ $this->responses = $responses; }

    public function getDate_started(){ return $this->date_started; }
    public function setDate_started($date_started){ $this->date_started = $date_started; }

    public function getDate_closed(){ return $this->date_closed; }
    public function setDate_closed($date_closed){ $this->date_closed = $date_closed; }

    public function getNotification_date() { return $this->notification_date;}
    public function setNotification_date($notification_date) {$this->notification_date = $notification_date;}

    public function getSchedule_month() { return $this->schedule_month;}
    public function setSchedule_month($schedule_month) {$this->schedule_month = $schedule_month;}

    public function getSchedule_year() { return $this->schedule_year;}
    public function setSchedule_year($schedule_year) {$this->schedule_year = $schedule_year;}

    public function getText_schedule_month(){
        $month_names = array("January","February","March","April","May","June","July","August","September","October","November","December");
        if($this->schedule_month != NULL)$this->text_schedule_month = $month_names[$this->schedule_month-1];
        return $this->text_schedule_month;
    }

    public function getNote() { return $this->note;}
    public function setNote($note) {$this->note = $note;}

    public function getCap_submitted_date(){return $this->cap_submitted_date;}
    public function setCap_submitted_date($cap_submitted_date){$this->cap_submitted_date = $cap_submitted_date;}

    public function getDeficiency_selections(){
        $deficiencySelections = array();
        $correctedSelections = array();
        $roomsShownSelections = array();
        $responses = $this->getResponses();
        foreach ($responses as $response){
            $selections = $response->getDeficiencySelections();
            //$selections = array_merge($selections, $response->getSupplementalDeficiencies());
            foreach($selections as $selection){
                $id = $selection->getDeficiency_id();
                $deficiencySelections[] = $id;
                if($selection->getCorrected_in_inspection() == true){
                    $correctedSelections[] = $id;
                }
                if($selection->getShow_rooms() == true){
                    $roomsShownSelections[] = $id;
                }
            }
        }
        $this->deficiency_selections = array();
        $this->deficiency_selections['deficiencySelections'] = $deficiencySelections;
        $this->deficiency_selections['correctedSelections'] = $correctedSelections;
        $this->deficiency_selections['roomsShownSelections'] = $roomsShownSelections;

        return $this->deficiency_selections;
    }

    public function getStatus() {
        if( $this->status != null ){
            return $this->status;
        }

        $LOG = Logger::getLogger(__CLASS__);
        //approved?
        // If there is a close date, it's closed.
         // Create some reference dates for status checking
        $now = new DateTime("now");
        $then = new DateTime("now - 30 days");

        if ($this->date_closed != null) {
            $this->status = 'CLOSED OUT';
        }elseif($this->cap_submitted_date){
            $this->status = 'SUBMITTED CAP';
        }elseif($this->notification_date){
            //do we even, like, need a plan?
            $ds = $this->getDeficiency_selections();
            if($this->key_id == 97)$LOG->fatal($ds);
            if(!isset($ds['deficiencySelections']) ||  empty($ds['deficiencySelections'])){
                $this->hasDeficiencies = false;
                $this->status = "CLOSED OUT";
            }
            //Is the Corrective Action Plan overdue?
            $notificationDate = new DateTime($this->getNotification_date());

            if($now->diff($notificationDate)->days > 14){
                $this->status = "OVERDUE CAP";
            }else{
                $this->status = "INCOMPLETE CAP";
            }
        }elseif($this->date_started){
            $this->status = "INCOMPLETE INSPECTION";
        }elseif($this->schedule_month){
             // ... and it's not 30 days past the first day of the scheduled month
            if ($then < date_create($this->schedule_year . "-" . $this->schedule_month )  ) {
                // Then it's pending

                //not fully schedule if not inspector(s) assigned
                if($this->getInspectors() != NULL){
                    $this->status = 'SCHEDULED';
                }else{
                    $this->status = 'NOT ASSIGNED';
                }
                //Begin Inspection
            } else {
                // If it is 30 days past due, it's overdue for inspection
                $this->status = 'OVERDUE INSPECTION';
                //Begin Inspection
            }
        }else{
            $this->status = "NOT SCHEDULED";
        }

        return $this->status;
    }

    public function getCap_complete() {return $this->cap_complete;}
    public function setCap_complete($cap_complete) {$this->cap_complete = $cap_complete;}
    public function getCap_due_date() {
        if($this->getNotification_date() == NULL)return;
        $LOG = Logger::getLogger(__CLASS__);

        //14 days after notification date
        $noteficationDate = new DateTime($this->getNotification_date());
        $interval = new DateInterval('P14D');
        $this->cap_due_date = $noteficationDate->add($interval)->format('Y-m-d H:i:s');
        return $this->cap_due_date;
    }

    public function getIs_rad() {return $this->is_rad;}
    public function setIs_rad($is_rad) {$this->is_rad = $is_rad;}

    public function getInspection_wipe_tests() {
        $thisDAO = new GenericDAO($this);
        $this->inspection_wipe_tests = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$INSPECTION_WIPES_TESTS_RELATIONSHIP));
        return $this->inspection_wipe_tests;
    }

    public function getIsArchived(){
        $this->isArchived = ($this->getStatus() == "CLOSED OUT");
        return $this->isArchived;
    }

    public function setIsArchived($archived){
        $this->isArchived = $archived;
    }

    public function getHasDeficiencies(){return $this->hasDeficiencies;}

	public function getCap_submitter_id(){return $this->cap_submitter_id;}
	public function setCap_submitter_id($cap_submitter_id){$this->cap_submitter_id = $cap_submitter_id;}

	public function getCap_submitter_name(){
        if($this->getCap_submitter_id() != null && $this->cap_submitter_name == null){
            $thisDao = new GenericDAO(new User());
            $user = $thisDao->getById($this->cap_submitter_id);
            if($user != null){
                $this->cap_submitter_name = $user->getName();
            }
        }
		return $this->cap_submitter_name;
	}

	public function getCap_approver_id(){return $this->cap_approver_id;}
	public function setCap_approver_id($cap_approver_id){$this->cap_approver_id = $cap_approver_id;}

	public function getCap_approver_name(){
        if($this->getCap_approver_id() != null && $this->cap_approver_name == null){
            $thisDao = new GenericDAO(new User());
            $user = $thisDao->getById($this->cap_approver_id);
            if($user != null){
                $this->cap_approver_name = $user->getName();
            }
        }
		return $this->cap_approver_name;
	}

    public function getRoomIds(){
        if($this->roomIds == null && $this->hasPrimaryKeyValue()){
            if( $this->rooms != null ){
                // We already have our Rooms; just grab the IDs from them
                $this->roomIds = array();
                foreach($this->rooms as $room){
                    $this->roomIds[] = $room->getKey_id();
                }
            }
            else {
                // Just query for the IDs; no need to get whole rooms
                $thisDao = new GenericDAO($this);
                $this->roomIds = $thisDao->getRelatedItemKeysById($this->getKey_id(), DataRelationship::fromArray(self::$ROOMS_RELATIONSHIP));
            }

        }

        return $this->roomIds;
    }

    /**
     * Retrieves the unique statuses of all associated CorrectiveActions
     * contained within this Inspection's Responses
     */
    public function collectAllCorrectiveActionStatuses(){
        $LOG = Logger::getLogger(__CLASS__ . '.' . __FUNCTION__);

        // Reduce to all CAPs
        $LOG->debug("Reduce $this responses to their CAPs");
        $allCaps = array_reduce(
            $this->getResponses(),
            function($caps, $response){
                // Collect both DeficiencySelection and SupplementalDeficiencies
                //   into single array
                $defs = array();
                if( $response->getDeficiencySelections() != null ){
                    $defs = array_merge($defs, $response->getDeficiencySelections());
                }

                if( $response->getSupplementalDeficiencies() != null ){
                    $defs = array_merge($defs, $response->getSupplementalDeficiencies());
                }

                // don't bother mapping if empty
                if( count($defs) > 0){
                    // Map each response to its deficiency CAPs
                    foreach($defs as $def){
                        $caps = array_merge($caps, $def->getCorrectiveActions());
                    }
                }

                return $caps;
            },
            array()
        );

        // Further reduce to all Statuses
        $LOG->debug("Reduce " . count($allCaps) . " CAPs to their unique statuses");
        $allCapStatuses = array_reduce(
            $allCaps,
            function($statuses, $cap){
                if( $statuses == null ){
                    $statuses = array();
                }

                if(!in_array($cap->getStatus(), $statuses)){
                    $statuses[] = $cap->getStatus();
                }

                return $statuses;
            },
            array()
        );

        $LOG->debug("$this contains CAP statuses: " . implode(', ', $allCapStatuses));
        return $allCapStatuses;
    }
}
?>

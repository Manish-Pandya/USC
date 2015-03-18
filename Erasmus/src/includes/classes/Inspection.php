<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Inspection extends GenericCrud {

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
		"cap_complete"      => "integer"
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


	/** Array of Inspector entities that took part in this Inspection */
	private $inspectors;

	/** Reference to the PrincipalInvestigator being inspected */
	private $principalInvestigator;
	private $principal_investigator_id;

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

	/**decorator to translate schedule month property into month name so that it doesn't have to be done repeatedly on client**/
	private $text_schedule_month;

	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getInspectors");
		$entityMaps[] = new EntityMap("eager","getRooms");
		$entityMaps[] = new EntityMap("eager","getResponses");
		$entityMaps[] = new EntityMap("eager","getDeficiency_selections");
		$entityMaps[] = new EntityMap("eager","getPrincipalInvestigator");
		$entityMaps[] = new EntityMap("eager","getStatus");
		$entityMaps[] = new EntityMap("lazy","getChecklists");

		$this->setEntityMaps($entityMaps);

	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	public function getInspectors(){
		if($this->inspectors)return $this->inspectors;
		$thisDAO = new GenericDAO($this);
		$this->inspectors = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$INSPECTORS_RELATIONSHIP));
		return $this->inspectors;
	}
	public function setInspectors($inspectors){ $this->inspectors = $inspectors; }

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

	public function getResponses(){
		$thisDAO = new GenericDAO($this);
		$this->responses = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$RESPONSES_RELATIONSHIP));
		return $this->responses;
	}

	public function getRooms(){
		$thisDAO = new GenericDAO($this);
		$this->rooms = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$ROOMS_RELATIONSHIP));
		return $this->rooms;
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
		$LOG = Logger::getLogger(__CLASS__);

		// If there is a close date, it's closed.
		if ($this->date_closed != null) {
			return 'CLOSED OUT';
		}

		// Create some reference dates for status checking
		$now = new DateTime("now");
		$then = new DateTime("now - 30 days");
		

		// If it's been scheduled but not started...
		if ($this->schedule_month != null && $this->date_started == null) {
			// ... and it's not 30 days past the first day of the scheduled month
			if ($then < date_create($this->schedule_year . "-" . $this->schedule_month )  ) {
				// Then it's pending
				return 'SCHEDULED';
			} else {
				// If it is 30 days past due, it's overdue for inspection
				return 'OVERDUE FOR INSPECTION';
			}
		}
		//It's been started, but we know it hasn't been finished, because we checked above for date_closed
		if($this->date_started != null){
			$LOG->debug("date started is not null");
			//PI has not been notified of the results
			if($this->notification_date == null){
				$LOG->debug("there was no notification date for inspection with key_id $this->key_id");

				//Inspection has been started
				return "INCOMPLETE REPORT";

			}
			//PI has been notified of the results
			else{
				$notificationDate = new DateTime($this->notification_date);
				
				//CAP has been submitted
				if($this->cap_submitted_date != null && $this->cap_submitted_date != '0000-00-00 00:00:00'){
					return 'CLOSED OUT';
				}
				//CAP not been submitted
				else{
					//Is the Corrective Action Plan overdue?
					if($now->diff($notificationDate) < -14){
						return "OVERDUE CORRECTIVE ACTIONS";
					}else{
						return "PENDING CLOSEOUT";
					}
				}
			}
		}

		// Now we check to see if there are unresolved deficiencies.  Start by assuming all is good.
		$accepted = true;

		// iterate the responses that have deficiencies
		foreach($this->getResponses() as $response){
			foreach($response->getDeficiencySelections() as $def){
				// Get the corrective actions for this deficiency
				$cas = $def->getCorrectiveActions();
				// if there are no corrective actions, no es bueno
				if ($cas == null) {
					$accepted = false;
					break 2;
				}
				// If there are corrective actions, we need to make sure they're all "Accepted". Otherwise, not good.
				foreach($cas as $ca){
					if($ca != "Accepted") {
						$accepted = false;
						break 3;
					}
				}
			}
		}

		// If there are unresolved deficiences, and the PI was notified > 30 days ago, we're overdue for corrective action
		if ($accepted == false && $this->notification_date != null && $then > $this->notification_date){
				return 'OVERDUE FOR CORRECTIVE ACTION';
		}

		// If no other status applies, it's considered open.
		return 'OPEN';
	}
	public function getCapComplete() {return $this->cap_complete;}
	public function setCapComplete($cap_complete) {$this->cap_complete = $cap_complete;}
	public function getCapDueDate() {
		if($this->getNotification_date() == NULL)return;
		
		//14 days after notification date
		$noteficationDate = new DateTime($this->getNotification_date());
		$interval = new DateInterval('P14D');
		$this->cap_due_date = $noteficationDate->add($interval);
		return $this->cap_due_date;
	}
	
	

}
?>
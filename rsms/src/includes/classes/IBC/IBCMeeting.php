<?php
include_once 'GenericCrud.php';

/**
 *
 *
 * @author David Hamiter
 */
class IBCMeeting extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "ibc_meeting";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
        "agenda"				=> "text",
		"meeting_date"			=> "timestamp",
		"location"				=> "text",

		//GenericCrud
		"key_id"				=> "integer",
		"date_created"			=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"				=> "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"		=> "integer",
	);

    /** Relationships */
	public static $ATTENDEES_RELATIONSHIP = array(
		"className"				=>	"User",
		"tableName"				=>	"ibc_meeting_attendee",
		"keyName"				=>	"attendee_id",
		"foreignKeyName"		=>	"meeting_id"
	);
	public static $REVISIONS_RELATIONSHIP = array(
		"className"				=>	"IBCProtocolRevision",
		"tableName"				=>	"ibc_meeting_revision",
		"keyName"				=>	"revision_id",
		"foreignKeyName"		=>	"meeting_id"
	);

    /**
     * Summary of $attendees
     * @var User[]
     */
    private $attendees;

    /**
     * Summary of $agenda
     * @var string
     */
    private $agenda;

	/**
	 * Summary of $meeting_date
	 * @var string
	 */
    private $meeting_date;

	/**
	 * Summary of $location
	 * @var string
	 */
    private $location;

	/**
	 * Summary of $protocolRevisions
	 * @var IBCProtocolRevision[]
	 */
	private $protocolRevisions;

	public function __construct(){
		// Define which subentities to load
		/*$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy", "getAttendees");
		$this->setEntityMaps($entityMaps);*/
	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	public function getProtocolRevisions(){
		if($this->protocolRevisions === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->protocolRevisions = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$REVISIONS_RELATIONSHIP));
		}
		return $this->protocolRevisions;
	}
	public function setProtocolRevisions($revisions){$this->protocolRevisions = $revisions;}

    public function getAttendees(){
		//$log = Logger::getLogger(__FUNCTION__);
		if($this->attendees === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->attendees = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$ATTENDEES_RELATIONSHIP));
			//$log->fatal($this->attendees);
		}
		return $this->attendees;
	}
	public function setAttendees($attendees){ $this->attendees = $attendees; }

	public function getAgenda(){return $this->agenda;}
	public function setAgenda($agenda){$this->agenda = $agenda;}

	public function getMeeting_date(){return $this->meeting_date;}
	public function setMeeting_date($meeting_date){$this->meeting_date = $meeting_date;}

	public function getLocation(){return $this->location;}
	public function setLocation($location){$this->location = $location;}

}
?>
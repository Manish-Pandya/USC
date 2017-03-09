<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class IBCProtocolRevision extends GenericCrud
{

	/** Name of the DB Table */
	protected static $TABLE_NAME = "ibc_protocol_revision";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"revision_number"			=> "integer",
		"protocol_id"				=> "integer",
		"date_returned"				=> "timestamp",
        "date_approved"				=> "timestamp",
        "date_submitted"            => "timestamp",
        "date_in_review"            => "timestamp",

		"protocol_type"				=> "text",
        //GenericCrud
		"key_id"				=> "integer",
		"date_created"			=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"				=> "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"		=> "integer",
	);

    /* which revision of the the protocol is this?  If 0 or null, this is the first submission as opposed to a revision */
    private $revision_number;

    /* id of the protocol this is a revision of*/
	private $protocol_id;

    /* date this revision's protocl was sent to the lab to be revised this time*/
	private $date_returned;

    /* date this revision was submitted to the committee after being revised by the lab */
	private $date_submitted;

    /* date this revision was submitted to the full committee after pre-review by the chair */
	private $date_in_review;

    /* date this revision was approved by the committee after being revised by the lab */
	private $date_approved;

	private $protocol_type;

	private $status;

    /* array of responses submitted in this revision */
    private $IBCResponses;

    /* array of primary reviewers responsible for reviewing this protocal revision*/
    private $primaryReviewers;

    /* array of primary reviewers responsible for reviewing this protocal revision*/
    private $preliminaryReviewers;

    /** array of sections containing questions relevant to this protocol revision's hazard **/
    private $section;

   

    

	public function __construct(){
		// Define which subentities to load
		$entityMaps = array();
        $entityMaps[] = new EntityMap("lazy","getPreliminaryReviewers");
        $entityMaps[] = new EntityMap("lazy","getPrimaryReviewers");
		$entityMaps[] = new EntityMap("lazy","getIBCResponses");
		$this->setEntityMaps($entityMaps);
	}

    /** Relationships */
	public static $RESPONSES_RELATIONSHIP = array(
		"className"	=>	"IBCResponse",
		"tableName"	=>	"ibc_response",
		"keyName"	=>	"key_id",
		"foreignKeyName"	=>	"revision_id"
	);

    public static $PRIMARY_REVIEWERS_RELATIONSHIP = array(
        "className"	=>	"User",
        "tableName"	=>	"ibc_revision_primary_reviewer",
        "keyName"	=>	"reviewer_id",
        "foreignKeyName"	=>	"revision_id"
    );

    public static $PRELIMINARY_REVIEWERS_RELATIONSHIP = array(
        "className"	=>	"User",
        "tableName"	=>	"ibc_revision_preliminary_reviewer",
        "keyName"	=>	"reviewer_id",
        "foreignKeyName"	=>	"revision_id"
    );

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors / Mutators
    public function getRevision_number(){
		return $this->revision_number;
	}
	public function setRevision_number($revision_number){
		$this->revision_number = $revision_number;
	}

	public function getProtocol_id(){
		return $this->protocol_id;
	}
	public function setProtocol_id($protocol_id){
		$this->protocol_id = $protocol_id;
	}

	public function getDate_returned(){
		return $this->date_returned;
	}
	public function setDate_returned($date_returned){
		$this->date_returned = $date_returned;
	}

    public function getDate_in_review(){
		return $this->date_in_review;
	}
	public function setDate_in_review($d){
		$this->date_in_review = $d;
	}

	public function getDate_submitted(){
		return $this->date_submitted;
	}
	public function setDate_submitted($date_submitted){
		$this->date_submitted = $date_submitted;
	}

    public function getDate_approved(){
		return $this->date_approved;
	}
	public function setDate_approved($date){
		$this->date_approved = $date;
	}

	public function getProtocol_type(){
		return $this->protocol_type;
	}
	public function setProtocol_type($protocol_type){
		$this->protocol_type = $protocol_type;
	}

    /** define the possible statuses for revisions **/
    private static $STATUSES = array(
            "NOT_SUBMITTED" => "Not Submitted",
            "SUBMITTED" => "Submitted",
            "RETURNED_FOR_REVISION" => "Returned for Revision",
            "IN_REVIEW" => "In Review",
            "APPROVED" => "Approved"
        );
	public function getStatus(){
        if(!$this->date_submitted && !$this->date_returned){
            $this->status = IBCProtocolRevision::$STATUSES["NOT_SUBMITTED"];
        }
        elseif($this->date_submitted && !$this->date_to_review && !$this->date_approved && !$this->date_returned){
            $this->status = IBCProtocolRevision::$STATUSES["SUBMITTED"];
        }
        elseif($this->date_to_review && !$this->date_approved && !$this->date_returned){
            $this->status = IBCProtocolRevision::$STATUSES["IN_REVIEW"];
        }
        elseif(!$this->date_approved && $this->date_returned){
            $this->status = IBCProtocolRevision::$STATUSES["RETURNED_FOR_REVISION"];
        }
        elseif($this->date_approved){
            $this->status = IBCProtocolRevision::$STATUSES["APPROVED"];
        }
		return $this->status;
	}
	public function setStatus($status){
		$this->status = $status;
	}

    public function getIBCResponses(){
		if($this->IBCResponses === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->IBCResponses = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$RESPONSES_RELATIONSHIP));
		}
		return $this->IBCResponses;
	}
	public function setIBCResponses($IBCResponses){
		$this->IBCResponses = $IBCResponses;
	}

    public function getPrimaryReviewers(){
        if($this->primaryReviewers === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->primaryReviewers = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PRIMARY_REVIEWERS_RELATIONSHIP));
		}
		return $this->primaryReviewers;
	}
	public function setPrimaryReviewers($primaryReviewers){
		$this->primaryReviewers = $primaryReviewers;
	}

	public function getPreliminaryReviewers(){
        if($this->preliminaryReviewers === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->preliminaryReviewers = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PRELIMINARY_REVIEWERS_RELATIONSHIP));
		}
		return $this->preliminaryReviewers;
	}

	public function setPreliminaryReviewers($preliminaryReviewers){
		$this->preliminaryReviewers = $preliminaryReviewers;
	}

}
<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class IBCProtocol extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "ibc_protocol";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"protocol_number"				=> "text",
		"project_title"					=> "text",
		"department_id"					=> "integer",
		"hazard_id"						=> "integer",
		"approval_date"					=> "timestamp",
		"expiration_date"				=> "timestamp",

		//GenericCrud
		"key_id"				=> "integer",
		"date_created"			=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"				=> "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"		=> "integer",
	);

	private $protocol_number;
	private $project_title;
	private $approval_date;
	private $expiration_date;
	private $report_path;

	private $principalInvestigators;

	private $department_id;
	private $department;

	private $hazard_id;

    private $IBCProtocolRevisions;

	private $IBCSections;

	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getPrincipalInvestigators");
		$entityMaps[] = new EntityMap("lazy","getHazard");
		$entityMaps[] = new EntityMap("lazy","getDepartment");
        $entityMaps[] = new EntityMap("lazy","getRevisions");
		$this->setEntityMaps($entityMaps);
	}

    /** Relationships */
	public static $PIS_RELATIONSHIP = array(
		"className"	=>	"PrincipalInvestigator",
		"tableName"	=>	"protocol_pi",
		"keyName"	=>	"principal_investigator_id",
		"foreignKeyName"	=>	"protocol_id"
	);

    public static $REVISIONS_RELATIONSHIP = array(
			"className" => "IBCProtocolRevision",
			"tableName" => "ibc_protocol_revision",
			"keyName"   => "key_id",
			"foreignKeyName" => "protocol_id"
	);

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors / Mutators
	public function getProtocol_number(){
		return $this->protocol_number;
	}
	public function setProtocol_number($protocol_number){
		$this->protocol_number = $protocol_number;
	}

	public function getProject_title(){
		return $this->project_title;
	}
	public function setProject_title($project_title){
		$this->project_title = $project_title;
	}

	public function getApproval_date(){
		return $this->approval_date;
	}
	public function setApproval_date($approval_date){
		$this->approval_date = $approval_date;
	}

	public function getReport_path(){
		return $this->report_path;
	}
	public function setReport_path( $report_path ){
		$this->report_path = $report_path;
	}

	public function getExpiration_date(){
		return $this->expiration_date;
	}
	public function setExpiration_date($expiration_date){
		$this->expiration_date = $expiration_date;
	}

	public function getPrincipalInvestigators(){
        if($this->principalInvestigators === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->principalInvestigators = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$PIS_RELATIONSHIP));
		}
		return $this->principalInvestigators;
	}
	public function setPrincipalInvestigators($principal_investigators){
		$this->principalInvestigators = $principal_investigators;
	}

	public function getDepartment_id(){
		return $this->department_id;
	}
	public function setDepartment_id($department_id){
		$this->department_id = $department_id;
	}

	public function getDepartment(){
		return $this->department;
	}
	public function setDepartment($department){
		$this->department = $department;
	}

	public function getHazard_id(){
		return $this->hazard_id;
	}
	public function setHazards($hazard_id){
		$this->hazard_id = $hazard_id;
	}

    public function getIBCProtocolRevisions(){
		if($this->labPersonnel === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->IBCProtocolRevisions = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationship::fromArray(self::$REVISIONS_RELATIONSHIP));
		}
		return $this->IBCProtocolRevisions;
	}
	public function setIBCProtocolRevisions($revisions){ $this->labPersonnel = $revisions; }

	public function getIBCSections(){
		if($this->IBCSections == null){
			$sectionDao= new GenericDAO(new IBCSection());
			$whereClauseGroup = new WhereClauseGroup(
				array(new WhereClause("hazard_id","=",$this->hazard_id))
			);
			$this->IBCSections = $sectionDao->getAllWhere($whereClauseGroup);
		}
		return $this->IBCSections;
	}
	public function setIbcSections($sections){$this->IBCSections = $sections;}
}
?>
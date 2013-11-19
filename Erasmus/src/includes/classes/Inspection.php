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
		//TODO: Lead Inspector?
		"principal_investigator_id" => "integer",
		//responses are a relationship
		"date_started"	=> "timestamp",
		"date_closed"	=> "timestamp",
	);
	
	/** Array of Inspector entities that took part in this Inspection */
	private $inspectors;
	
	/** Reference to the PrincipalInvestigator being inspected */
	private $principalInvestigator;
	
	/** Array of Response entities */
	private $responses;
	
	/** Date and time this Inspection began */
	private $dateStarted;
	
	/** Date and time this Inspection was completed */
	private $dateClosed;
	
	public function __construct(){
	
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getInspectors(){ return $this->inspectors; }
	public function setInspectors($inspectors){ $this->inspectors = $inspectors; }
	
	public function getPrincipalInvestigator(){ return $this->principalInvestigator; }
	public function setPrincipalInvestigator($principalInvestigator){ $this->principalInvestigator = $principalInvestigator; }
	
	public function getResponses(){ return $this->responses; }
	public function setResponses($responses){ $this->responses = $responses; }
	
	public function getDateStarted(){ return $this->dateStarted; }
	public function setDateStarted($dateStarted){ $this->dateStarted = $dateStarted; }
	
	public function getDateClosed(){ return $this->dateClosed; }
	public function setDateClosed($dateClosed){ $this->dateClosed = $dateClosed; }
}
?>
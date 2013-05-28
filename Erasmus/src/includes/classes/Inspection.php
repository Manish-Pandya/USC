<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Inspection {
	
	/** Array of Inspector entities that took part in this Inspection */
	private $inspectors;
	
	/** Reference to the PrincipalInvestigator being inspected */
	private $principalInvestigator;
	
	/** Array of Response entities */
	private $responses;
	
	//TODO: dates: date started, date closed
	
	public function __construct(){
	
	}
	
	public function getInspectors(){ return $this->inspectors; }
	public function setInspectors($inspectors){ $this->inspectors = $inspectors; }
	
	public function getPrincipalInvestigator(){ return $this->principalInvestigator; }
	public function setPrincipalInvestigator($principalInvestigator){ $this->principalInvestigator = $principalInvestigator; }
	
	public function getResponses(){ return $this->responses; }
	public function setResponses($responses){ $this->responses = $responses; }
}
?>
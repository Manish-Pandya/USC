<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class HazardUseAuthorization {
	
	/** Hazard entity for which the associated PrincipalInvestigator entity is given authorization */
	private $hazard;
	
	/** PrincipalInvestigator entity given authorization for the associated Hazard entity */
	private $principalInvestigator;
	
	public function __construct(){
	
	}
	
	public function getHazard(){ return $this->hazard; }
	public function setHazard($hazard){ $this->hazard = $hazard; }
	
	public function getPrincipalInvestigator(){ return $this->principalInvestigator; }
	public function setPrincipalInvestigator($principalInvestigator){ $this->principalInvestigator = $principalInvestigator; }
}
?>
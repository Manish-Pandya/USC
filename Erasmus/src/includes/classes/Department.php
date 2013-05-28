<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Department {
	
	/** Array of PrincipalInvestigator entities that are part of this Department */
	private $principalInvestigators;
	
	public function __construct(){
	
	}
	
	public function getprincipalInvestigators(){ return $this->principalInvestigators; }
	public function setPrincipalInvestigators(principalInvestigators){ $this->principalInvestigators = $principalInvestigators; }
}
?>
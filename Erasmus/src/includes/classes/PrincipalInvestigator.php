<?php
/**
 * TODO: DOC
 * 
 * @author Mitch Martin, GraySail LLC
 */
class PrincipalInvestigator extends User {
	
	/** Array of Departments to which this PI belongs */
	private $departments;
	
	/** Array of Room entities managed by this PI */
	private $rooms;
	
	/** Array of LabSafetyManager entities */
	private $labSafetyManagers;
	
	/** Hazards for which this PI has explicit authorization to manage */
	private $hazardAuthorizations;
	
	public function __construct(){
		parent::__construct();
	}
	
}
?>
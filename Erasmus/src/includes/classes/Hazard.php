<?php
/**
 * 
 * 
 * 
 * @author Mitch Martin, GraySail LLC
 */
class Hazard {
	
	/** Array of parent Hazard entities */
	private $parentHazards;
	
	/** Array of child Hazard entities */
	private $subHazards;
	
	/** Array of Checklist entities associated with this Hazard */
	private $checklists;
	
	/** Array of Room entities in which this Hazard is contained */
	private $rooms;
	
	/** Array of PrincipalInvestigator entities who have explicit authorization to manage this Hazard */
	private $authorizedPrincipalInvestigators;
	
	public function __construct(){
	
	}
}
?>
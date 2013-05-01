<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Room {
	
	/** Reference to the Building entity that contains this Room */
	private $building;
	
	/** Array of PricipalInvestigator entities that manage this Room */
	private $principalInvestigators;
	
	/** Array of Hazard entities contained in this Room */
	private $hazards;
	
	public function __construct(){
	
	}
}
?>
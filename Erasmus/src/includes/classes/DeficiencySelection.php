<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class DeficiencySelection {
	
	/** Reference to the Response entity to which the associated Deficiency is applied */
	private $response;
	
	/** Reference to the Room entity in which the associated Deficiency applies */
	private $room;
	
	/** Reference to the Deficiency entity that was selected */
	private $deficiency;
	
	/** Array of DeficiencyRootCause entities that were selected with the associated Deficiency */
	private $deficiencyRootCauses;
	
	/** Array of CorrectiveAction entities describing this Deficiency's resolution */
	private $correctiveActions;
	
	public function __construct(){
	
	}
}
?>
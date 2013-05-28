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
	
	public function getResponse(){ return $this->response; }
	public function setResponse($response){ $this->response = $response; }
	
	public function getRoom(){ return $this->room; }
	public function setRoom($room){ $this->room = $room; }
	
	public function getDeficiency(){ return $this->deficiency; }
	public function setDeficiency($deficiency){ $this->deficiency = $deficiency; }
	
	public function getDeficiencyRootCauses(){ return $this->deficiencyRootCauses; }
	public function setDeficiencyRootCauses($deficiencyRootCauses){ $this->deficiencyRootCauses = $deficiencyRootCauses; }
	
	public function getCorrectiveActions(){ return $this->correctiveActions; }
	public function setCorrectiveActions($correctiveActions){ $this->correctiveActions = $correctiveActions; }
}
?>
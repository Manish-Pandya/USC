<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class CorrectiveAction {
	
	//TODO: "corrected during inspection" vs after
	
	/** DeficiencySelection entity describing the Deficiency to which this CorrectiveAction applies */
	private $deficiencySelection;
	
	/** String describing this CorrectiveAction plan */
	private $text;
	
	public function __construct(){
	
	}
	
	public function getDeficiencySelection(){ return $this->deficiencySelection; }
	public function setDeficiencySelection($selection){ $this->deficiencySelection = $selection; }
	
	public function getText(){ return $this->text; }
	public function setText($text){ $this->text = $text; }
}
?>
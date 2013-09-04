<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class CorrectiveAction {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "corrective_action";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//deficiency selection is a relationship
		"text"		=> "text",
	);
	
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
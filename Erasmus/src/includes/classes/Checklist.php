<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Checklist {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "erasmus_checklist";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//hazards is a relationship
		//questions is a relationship
	);
	
	/** Array of Hazard entities to which this Checklist applies */
	private $hazards;
	
	/** Array of Question entities that comprise this Checklist */
	private $questions;
	
	public function __construct(){
	
	}
	
	public function getHazards(){ return $this->hazards; }
	public function setHazards($hazards){ $this->hazards = $hazards; }

	public function getQuestions(){ return $this->questions; }
	public function setQuestions($questions){ $this->questions = $questions; }
}
?>
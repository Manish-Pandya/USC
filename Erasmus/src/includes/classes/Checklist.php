<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Checklist {
	
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
<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Deficiency {
	
	/** Reference to the Question entity to which this Deficiency applies */
	private $question;
	
	/** String containing the text describing this Deficiency */
	private $text;
	
	public function __construct(){
	
	}
	
	public function getQuestion(){ return $this->question; }
	public function setQuestion($question){ $this->question = $question; }
	
	public function getText(){ return $this->text; }
	public function setText($text){ $this->text = $text; }
}
?>
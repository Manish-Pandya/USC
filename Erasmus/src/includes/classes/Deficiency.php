<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Deficiency {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "erasmus_deficiency";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//question is a relationship
		"text"		=> "text",
	);
	
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
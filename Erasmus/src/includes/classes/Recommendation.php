<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Recommendation {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "recommendation";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//Is question a relationship?
		//"question"	=> "bolean",
		"text"		=> "text",
	);
	
	/** Reference to the Question entity to which this Recommendation applies */
	private $question;
	
	/** String containing the text describing this Recommendation */
	private $text;
	
	public function __construct(){
	
	}
	
	public function getQuestion(){ return $this->question; }
	public function setQuestion($question){ $this->question = $question; }
	
	public function getText(){ return $this->text; }
	public function setText($text){ $this->text = $text; }
}
?>
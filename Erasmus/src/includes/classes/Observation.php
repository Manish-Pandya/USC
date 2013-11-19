<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Observation extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "observation";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//Is question a relationship?
		//"question"	=> "bolean",
		"text"		=> "text",
	);
	
	/** Reference to the Question entity to which this Observation applies */
	private $question;
	
	/** String containing the text describing this Observation */
	private $text;
	
	public function __construct(){
	
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getQuestion(){ return $this->question; }
	public function setQuestion($question){ $this->question = $question; }
	
	public function getText(){ return $this->text; }
	public function setText($text){ $this->text = $text; }
}
?>
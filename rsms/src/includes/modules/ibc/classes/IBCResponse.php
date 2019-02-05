<?php

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class IBCResponse extends GenericCrud
{
    /** Name of the DB Table */
	protected static $TABLE_NAME = "ibc_response";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"revision_id"			=> "integer",
		"answer_id"				=> "integer",
		"text"					=> "text",
        "is_selected"           => "boolean",
        "grid_row"              => "integer",
        "question_id"           => "integer",


		//GenericCrud
		"key_id"				=> "integer",
		"date_created"			=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"				=> "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"		=> "integer",
	);

	private $revision_id;
    /* Not all responses will have an answer id.  Some responses are free text*/ // TODO: Find out if this is at all true!
	private $answer_id;
	private $text;
	private $is_selected;
    private $grid_row;

    /**for convenience, we want to know what question this is a response to, rather than just what IBCPossibleAnswer it is a child of**/
    private $question_id;

	public function __construct(){

		
    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		return $entityMaps;
	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

    public function getRevision_id(){return $this->revision_id;}
	public function setRevision_id($revision_id){$this->revision_id = $revision_id;}

	public function getAnswer_id(){return $this->answer_id;}
	public function setAnswer_id($answer_id){$this->answer_id = $answer_id;}

	public function getText(){return $this->text;}
	public function setText($text){$this->text = $text;}

	public function getIs_selected(){return (bool) $this->is_selected;}
	public function setIs_selected($is_selected){$this->is_selected = $is_selected;}

	public function getGrid_row(){return $this->grid_row;}
	public function setGrid_row($grid_row){$this->grid_row = $grid_row;}

    public function getQuestion_id(){return $this->question_id;}
    public function setQuestion_id($id){$this->question_id = $id;}

}
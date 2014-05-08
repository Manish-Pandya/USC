<?php
/**
 *
 *
 *
 * @author Hoke Currie, GraySail LLC
 */
class CorrectiveAction extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "corrective_action";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//deficiency selection is a relationship
		"deficiency_selection_id" => "integer",
		"text"		=> "text",
		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"status"			=>     "text",
		"completion_date"   =>     "timestamp",
		"promised_date"   =>     "timestamp"
	);
	
	
	/** DeficiencySelection entity describing the Deficiency to which this CorrectiveAction applies */
	private $deficiencySelection;
	private $deficiency_selection_id;
	
	/** String describing this CorrectiveAction plan */
	private $text;
	
	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getDeficiencySelection");
		$this->setEntityMaps($entityMaps);
		
	}
		
		
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getDeficiencySelection(){ 
		if($this->deficiencySelection == null) {
			$deficiencySelectionDAO = new GenericDAO(new DeficiencySelection());
			$this->deficiencySelection = $deficiencySelectionDAO->getById($this->deficiency_selection_id);
		}
		return $this->deficiencySelection; 
	}
	public function setDeficiencySelection($selection){
		$this->deficiencySelection = $selection;
	}
	
	public function getDeficiency_selection_id(){ return $this->deficiency_selection_id; }
	public function setDeficiency_selection_id($deficiency_selection_id){ $this->deficiency_selection_id = $deficiency_selection_id; }
	
	public function getText(){ return $this->text; }
	public function setText($text){ $this->text = $text; }
	
	public function getStatus(){ return $this->status; }
	public function setStatus($status){ $this->status = $status; }
	
	public function getCompletion_date(){ return $this->completion_date; }
	public function setCompletion_date($completion_date){ $this->completion_date = $completion_date; }

	public function getPromised_date(){ return $this->promised_date; }
	public function setPromised_date($promised_date){ $this->promised_date = $promised_date; }

}
?>
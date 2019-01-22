<?php
/**
 *
 *
 *
 * @author Hoke Currie, GraySail LLC
 */
class CorrectiveAction extends GenericCrud {

	public static $STATUS_INCOMPLETE = 'Incomplete';
	public static $STATUS_PENDING = 'Pending';
	public static $STATUS_COMPLETE = 'Complete';
	public static $STATUS_ACCEPTED = 'Accepted';

	/** Name of the DB Table */
	protected static $TABLE_NAME = "corrective_action";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//deficiency selection is a relationship
		"deficiency_selection_id" => "integer",
        "supplemental_deficiency_id" => "integer",
        "needs_ehs"         => "boolean",
        "needs_facilities"          => "boolean",
        "insuficient_funds"         => "boolean",
        "other_reason"              => "text",


		"text"		=> "text",
		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"status"			=>     "text",
		"completion_date"   =>     "timestamp",
		"promised_date"   =>     "timestamp",
		"created_user_id"	=> "integer"
	);


	/** DeficiencySelection entity describing the Deficiency to which this CorrectiveAction applies */
	private $deficiencySelection;
	private $deficiency_selection_id;

    private $supplementalDeficiency;
    private $supplemental_deficiency_id;

	/** String describing this CorrectiveAction plan */
	private $text;

    private $needs_ehs;
    private $needs_facilities;
    private $insuficient_funds;
    private $other_reason;

	private $status;
	private $promised_date;
	private $completion_date;

	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getDeficiencySelection");
        $entityMaps[] = new EntityMap("lazy","getSupplementalDeficiency");

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
		if($this->deficiencySelection == null && $this->deficiency_selection_id != null) {
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

    public function getSupplementalDeficiency(){
		if($this->supplementalDeficiency == null && $this->supplemental_deficiency_id != null) {
			$deficiencySelectionDAO = new GenericDAO(new SupplementalDeficiency());
			$this->supplementalDeficiency = $deficiencySelectionDAO->getById($this->supplemental_deficiency_id);
		}
		return $this->supplementalDeficiency;
	}
	public function setSupplementalDeficiency($selection){
		$this->supplementalDeficiency = $selection;
	}

	public function getSupplemental_deficiency_id(){ return $this->supplemental_deficiency_id; }
	public function setSupplemental_deficiency_id($id){ $this->supplemental_deficiency_id = $id; }

	public function getText(){ return $this->text; }
	public function setText($text){ $this->text = $text; }

	public function getStatus(){ return $this->status; }
	public function setStatus($status){ $this->status = $status; }

	public function getCompletion_date(){ return $this->completion_date; }
	public function setCompletion_date($completion_date){ $this->completion_date = $completion_date; }

	public function getPromised_date(){ return $this->promised_date; }
	public function setPromised_date($promised_date){ $this->promised_date = $promised_date; }

    public function getNeeds_ehs(){return (bool) $this->needs_ehs;}
	public function setNeeds_ehs($needs_ehs){$this->needs_ehs = $needs_ehs;}

	public function getNeeds_facilities(){return (bool) $this->needs_facilities;}
	public function setNeeds_facilities($needs_facilities){$this->needs_facilities = $needs_facilities;}

	public function getInsuficient_funds(){return (bool) $this->insuficient_funds;}
	public function setInsuficient_funds($insuficient_funds){$this->insuficient_funds = $insuficient_funds;}

	public function getOther_reason(){return $this->other_reason;}
	public function setOther_reason($other_reason){$this->other_reason = $other_reason;}

}
?>
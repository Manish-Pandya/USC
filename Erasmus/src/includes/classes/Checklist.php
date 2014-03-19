<?php
/**
 *
 *
 *
 * @author Hoke Currie, GraySail LLC
 */
class Checklist extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "checklist";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"	=>	"text",
		//hazards is a relationship
		"hazard_id"	=>	"integer",
		//questions is a relationship

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer"
	);
	
	/** Relationships */
	protected static $QUESTIONS_RELATIONSHIP = array(
			"className"	=>	"Question",
			"tableName"	=>	"question",
			"keyName"	=>	"key_id",
			"foreignKeyName"	=>	"checklist_id"
	);
	
	/** Name of this Checklist */
	private $name;
	
	/** The Hazard entity to which this Checklist applies */
	private $hazard;
	private $hazard_id;
	
	/** Array of Question entities that comprise this Checklist */
	private $questions;
	
	public function __construct(){
	
		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getHazard");
		$entityMaps[] = new EntityMap("eager","getQuestions");
		$this->setEntityMaps($entityMaps);
		
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getHazard(){
		if($this->hazard == null) {
			$hazardDAO = new GenericDAO("Hazard");
			$this->hazard = $hazardDAO->getById($this->hazard_id);
		}
		return $this->hazard; 
	}
	public function setHazard($hazard){
		$this->hazard = $hazard; 
	}

	public function getHazard_id(){ return $this->hazard_id; }
	public function setHazard_id($hazard_id){ $this->hazard_id = $hazard_id; }

	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }

	public function getQuestions(){
		if($this->questions === NULL && $this->hasPrimaryKeyValue()) {
			$thisDAO = new GenericDAO($this);
			$this->questions = $thisDAO->getRelatedItemsById($this->getKey_id(), DataRelationShip::fromArray(self::$QUESTIONS_RELATIONSHIP));
		}
		return $this->questions;
	}
	public function setQuestions($questions){ $this->questions = $questions; }
}
?>
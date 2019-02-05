<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Department extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "department";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"name"		=> "text",
		//principal investigators is a relationship

		//GenericCrud
		"key_id"			=> "integer",
		"date_created"		=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"			=> "boolean",
		"last_modified_user_id"			=> "integer",
		"created_user_id"	=> "integer",
        "specialty_lab"     => "boolean"
	);
	
	protected static $PIS_RELATIONSHIP = array(
		"className"	=>	"PrincipalInvestigator",
		"tableName"	=>	"principal_investigator_department",
		"keyName"	=>	"principal_investigator_id",
		"foreignKeyName"	=>	"department_id"
	); 
	
	/** Name of the department */
	private $name;
	
	/** Array of PrincipalInvestigator entities that are part of this Department */
	private $principalInvestigators;
    
    private $specialty_lab;
	
	public function __construct(){

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = EntityMap::lazy("getPrincipalInvestigators");
		return $entityMaps;
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getName(){ return $this->name; }
	public function setName( $name ){ $this->name = $name; }
	
	public function getPrincipalInvestigators(){
		if($this->principalInvestigators == null) {
			$thisDAO = new GenericDAO($this);
			$this->principalInvestigators = $thisDAO->getRelatedItemsById($this->getKey_Id(), DataRelationship::fromArray(self::$PIS_RELATIONSHIP));
		}
		return $this->principalInvestigators;
	}
	public function setPrincipalInvestigators($principalInvestigators){ $this->principalInvestigators = $principalInvestigators; }
    
    public function getSpecialty_lab(){ return $this->specialty_lab; }
	public function setSpecialty_lab( $specialty ){ $this->specialty_lab = $specialty; }
    
	}

?>
<?php

include_once 'GenericCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class BiosafetyProtocol extends GenericCrud {

	/** Name of the DB Table */
	protected static $TABLE_NAME = "room";

	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"protocol_number"				=> "text",
		"project_title"					=> "text",
		"principal_investigator_id" 	=> "integer",
		"department_id"					=> "integer",
		"hazard_id"						=> "integer",

		//GenericCrud
		"key_id"				=> "integer",
		"date_created"			=> "timestamp",
		"date_last_modified"	=> "timestamp",
		"is_active"				=> "boolean",
		"last_modified_user_id"	=> "integer",
		"created_user_id"		=> "integer",
	);

	private $protocol_number;
	private $project_title;
	
	private $principal_investigator_id;
	private $principalInvestigator;
	
	private $department_id;
	private $department;
	
	private $hazard_id;
	private $hazard;
	
	
	public function __construct(){

		// Define which subentities to load
		$entityMaps = array();
		$entityMaps[] = new EntityMap("lazy","getPrincipalInvestigator");
		$entityMaps[] = new EntityMap("lazy","getHazard");
		$entityMaps[] = new EntityMap("lazy","getDepartment");
		$this->setEntityMaps($entityMaps);
	}

	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}

	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}

	// Accessors / Mutators
	public function getProtocol_number(){
		return $this->protocol_number;
	}	
	public function setProtocol_number($protocol_number){
		$this->protocol_number = $protocol_number;
	}
	
	public function getProject_title(){
		return $this->project_title;
	}	
	public function setProject_title($project_title){
		$this->project_title = $project_title;
	}
	
	public function getPrincipal_investigator_id(){
		return $this->principal_investigator_id;
	}	
	public function setPrincipal_investigator_id($principal_investigator_id){
		$this->principal_investigator_id = $principal_investigator_id;
	}
	
	public function getPrincipalInvestigator(){
		return $this->principalInvestigator;
	}
	public function setPrincipalInvestigator($principal_investigator){
		$this->principalInvestigator = $principal_investigator;
	}
	
	public function getDepartment_id(){
		return $this->department_id;
	}
	public function setDepartment_id($department_id){
		$this->department_id = $department_id;
	}
	
	public function getDepartment(){
		return $this->department;
	}
	public function setDepartment($department){
		$this->department = $department;
	}
	
	public function getHazard_id(){
		return $this->hazard_id;
	}
	public function setHazard_id($hazard_id){
		$this->hazard_id = $hazard_id;
	}
	
	public function getHazard(){
		return $this->hazard;
	}
	public function setHazard($hazard){
		$this->hazard = $hazard;
	}

}
?>
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
	);
	
	/** Name of the department */
	private $name;
	
	/** Array of PrincipalInvestigator entities that are part of this Department */
	private $principalInvestigators;
	
	public function __construct(){
	
	}
	
	public function getName(){ return $this->name; }
	public function setName( $name ){ $this->name = $name; }
	
	public function getPrincipalInvestigators(){ return $this->principalInvestigators; }
	public function setPrincipalInvestigators( $principalInvestigators ){ $this->principalInvestigators = $principalInvestigators; }
}
?>
<?php

include_once 'GenericCrud.php';
include_once 'Hazard.php';

/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Room extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "room";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		"active"	=> "bolean",
		"name"		=> "text",
	);
	
	private $name;
	
	/** Reference to the Building entity that contains this Room */
	private $building;
	
	/** Array of PricipalInvestigator entities that manage this Room */
	private $principalInvestigators;
	
	/** Array of Hazard entities contained in this Room */
	private $hazards;
	
	/** String containing emergency contact information */
	private $safetyContactInformation;
	
	public function __construct(){
	
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	// Accessors / Mutators
	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }
	
	public function getHazards(){ return $this->hazards; }
	public function setHazards($hazards){ $this->hazards = $hazards; }

	public function getPrincipalInvestigators(){ return $this->principalInvestigators; }
	public function setPrincipalInvestigators($principalInvestigators){ $this->principalInvestigators = $principalInvestigators; }
	
	public function getSafetyContactInformation(){ return $this->safetyContactInformation; }
	public function setSafetyContactInformation($contactInformation){ $this->safetyContactInformation = $contactInformation; }
	
}
?>
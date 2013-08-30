<?php
/**
 * 
 * 
 * 
 * @author Mitch Martin, GraySail LLC
 */
class Hazard extends GenericCrud {
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "erasmus_hazard";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
			"name"	=> "text",
			//parent hazard is a relationship
			//subhazards are relationships
			//checklists are relationships
			//rooms are relationships
			//authorized PIs are relationships
	);
	
	/** Name of the hazard */
	private $name;
	
	/** parent Hazard entity */
	private $parentHazard;
	
	/** Array of child Hazard entities */
	private $subHazards;
	
	/** Array of Checklist entities associated with this Hazard */
	private $checklists;
	
	/** Array of Room entities in which this Hazard is contained */
	private $rooms;
	
	//TODO: Room relationship should/may contain information about Equipment, etc
	
	public function __construct(){
	
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getName(){ return $this->name; }
	public function setName($name){ $this->name = $name; }
	
	public function getParentHazard(){ return $this->parentHazard; }
	public function setParentHazard($parentHazard){ $this->parentHazard = $parentHazard; }
	
	public function getSubHazards(){ return $this->subHazards; }
	public function setSubHazards($subHazards){ $this->subHazards = $subHazards; }
	
	public function getChecklists(){ return $this->checklists; }
	public function setChecklists($checklists){ $this->checklists = $checklists; }
	
	public function getRooms(){ return $this->rooms; }
	public function setRooms($rooms){ $this->rooms = $rooms; }
	
	public function getAuthorizedPrincipalInvestigators(){ return $this->authorizedPrincipalInvestigators; }
	public function setAuthorizedPrincipalInvestigators($authorizedPrincipalInvestigators){ $this->authorizedPrincipalInvestigators = $authorizedPrincipalInvestigators; }
}
?>
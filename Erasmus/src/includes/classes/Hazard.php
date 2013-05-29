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
			"key_id"	=> "integer",
			//TODO
	);
	
	/** Array of parent Hazard entities */
	private $parentHazards;
	
	/** Array of child Hazard entities */
	private $subHazards;
	
	/** Array of Checklist entities associated with this Hazard */
	private $checklists;
	
	/** Array of Room entities in which this Hazard is contained */
	private $rooms;
	
	/** Array of PrincipalInvestigator entities who have explicit authorization to manage this Hazard */
	private $authorizedPrincipalInvestigators;
	
	public function __construct(){
	
	}
	
	// Required for GenericCrud
	public function getTableName(){
		return self::$TABLE_NAME;
	}
	
	public function getColumnData(){
		return self::$COLUMN_NAMES_AND_TYPES;
	}
	
	public function getParentHazards(){ return $this->parentHazards; }
	public function setParentHazards($parentHazards){ $this->parentHazards = $parentHazards; }
	
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
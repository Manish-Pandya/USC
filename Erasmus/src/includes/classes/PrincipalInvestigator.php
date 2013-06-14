<?php
/**
 * TODO: DOC
 * 
 * @author Mitch Martin, GraySail LLC
 */
class PrincipalInvestigator extends GenericCrud {
	
	//FIXME: Shouls PrincipalInvestigator just be a role?
	
	/** Name of the DB Table */
	protected static $TABLE_NAME = "erasmus_pi";
	
	/** Key/Value Array listing column names mapped to their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
		//TODO: IS user a relationship?
		"user_id" => "integer",
		//departments is a relationship
		//rooms is a relationship
		//lab_safetey_managers is a relationship
		//hazardAuthorizations is a relationship
	);
	
	/** Base User object that this PI represents */
	private $user;
	
	/** Array of Departments to which this PI belongs */
	private $departments;
	
	/** Array of Room entities managed by this PI */
	private $rooms;
	
	/** Array of LabSafetyManager entities */
	private $labSafetyManagers;
	
	/** Hazards for which this PI has explicit authorization to manage */
	private $hazardAuthorizations;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function getDepartments(){ return $this->departments; }
	public function setDepartments($departments){ $this->departments = $departments; }
	
	public function getRooms(){ return $this->rooms; }
	public function setRooms($rooms){ $this->rooms = $rooms; }
	
	public function getLabSafetyManager(){ return $this->labSafetyManager; }
	public function setLabSafetyManager($labSafetyManager){ $this->labSafetyManager = $labSafetyManager; }
	
	public function getHazardAuthorizations(){ return $this->hazardAuthorizations; }
	public function setHazardAuthorizations($hazardAuthorizations){ $this->hazardAuthorizations = $hazardAuthorizations; }
	
}
?>
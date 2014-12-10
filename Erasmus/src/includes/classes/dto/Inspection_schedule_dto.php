<?php
class Inspection_schedule_dto {

	/* The name of the PI.*/
	private $pi_name;

	/* The PI's key_id.  I don't think we need their user key_id*/
	private $pi_key_id;

	/* This PI's rooms that are in the building */
	/* Lazy load all relationships */
	private $building_rooms;

	/* Out of this->building_rooms, those rooms that have already been inspected*/
	/* Lazy load all relationships */
	private $inspection_rooms;

	/* Array of Inspections that have been performed or scheduled on $this->inspection_rooms */
	/* Each should include an array of Inspectors */
	/* Lazy load all relationships, except Inspectors */

	private $inspections;


	public function getPi_name(){return $this->pi_name;}
	public function getPi_key_id(){return $this->pi_key_id;}
	public function getBuilding_rooms(){return $this->building_rooms;}
	public function getInspection_rooms(){return $this->inspection_rooms;}
	public function getInspections(){return $this->inspections;}

	public function setPi_name($pi_name){$this->pi_name = $pi_name;}
	public function setPi_key_id($pi_key_id){$this->pi_key_id = $pi_key_id;}
	public function setBuilding_rooms($building_rooms){$this->building_rooms = $building_rooms;}
	public function setInspection_rooms($inspection_rooms){$this->inspection_rooms = $inspection_rooms;}
	public function setInspections($inspections){$this->inspections = $inspections;}

}
?>
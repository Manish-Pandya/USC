<?php
class InspectionScheduleDto {

	/* The name of the PI.*/
	private $pi_name;

	/* The PI's key_id.  I don't think we need their user key_id*/
	private $pi_key_id;

	private $building_name;

	private $building_key_id;

	private $campus_key_id;

	private $campus_name;

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

	private $inspection_id;
	private $bio_hazards_present;
	private $chem_hazards_present;
	private $rad_hazards_present;
    private $deficiency_selection_count;

    private $corrosive_gas_present;
    private $flammable_gas_present;
    private $toxic_gas_present;
    private $hf_present;

	public function getPi_name(){return $this->pi_name;}
	public function getPi_key_id(){return $this->pi_key_id;}
	public function getBuilding_rooms(){return $this->building_rooms;}
	public function getInspection_rooms(){return $this->inspection_rooms;}
	public function getInspections(){return $this->inspections;}
	public function getCampus_name() {return $this->campus_name;}
	public function getBuilding_name() {return $this->building_name;}
	public function getCampus_key_id() {return $this->campus_key_id;}
	public function getBuilding_key_id() {return $this->building_key_id;}
	public function getInspection_id() {return $this->inspection_id;}
	public function getBio_hazards_present() {return (bool) $this->bio_hazards_present;}
	public function getChem_hazards_present() {return (bool) $this->chem_hazards_present;}
	public function getRad_hazards_present() {return (bool) $this->rad_hazards_present;}

	public function getCorrosive_gas_present() {return (bool) $this->corrosive_gas_present;}
	public function getFlammable_gas_present() {return (bool) $this->flammable_gas_present;}
	public function getToxic_gas_present() {return (bool) $this->toxic_gas_present;}
	public function getHf_present() {return (bool) $this->hf_present;}



	public function setPi_name($pi_name){$this->pi_name = $pi_name;}
	public function setPi_key_id($pi_key_id){$this->pi_key_id = $pi_key_id;}
	public function setBuilding_rooms($building_rooms){$this->building_rooms = $building_rooms;}
	public function setInspection_rooms($inspection_rooms){$this->inspection_rooms = $inspection_rooms;}
	public function setInspections($inspections){$this->inspections = $inspections;}
	public function setCampus_name($campus_name){$this->campus_name = $campus_name;}
	public function setBuilding_name($building_name){$this->building_name = $building_name;}
	public function setCampus_key_id($campus_key_id){$this->campus_key_id = $campus_key_id;}
	public function setBuilding_key_id($building_key_id){$this->building_key_id = $building_key_id;}
	public function setInspection_id($inspection_id){$this->inspection_id = $inspection_id;}
	public function setBio_hazards_present($present){$this->bio_hazards_present = $present;}
	public function setChem_hazards_present($present){$this->chem_hazards_present = $present;}
	public function setRad_hazards_present($present){$this->rad_hazards_present = $present;}

    public function setCorrosive_gas_present($present) {$this->corrosive_gas_present = $present;}
	public function setFlammable_gas_present($present) {$this->flammable_gas_present = $present;}
	public function setToxic_gas_present($present) {$this->toxic_gas_present = $present;}
	public function setHf_present($present) {$this->hf_present= $present;}


    public function getDeficiency_selection_count(){return $this->deficiency_selection_count;}

	public function setDeficiency_selection_count($deficiency_selection_count){	$this->deficiency_selection_count = $deficiency_selection_count;}

}
?>
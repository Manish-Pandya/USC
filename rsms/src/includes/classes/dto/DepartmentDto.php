<?php
class DepartmentDto {
	private $department_id;
	private $department_name;
	private $pi_count;
	private $room_count;
	private $is_active;
    private $specialty_lab;
    private $campus_id;
    private $campus_name;
	
	public function getDepartment_id(){ return $this->department_id; }
	public function getDepartment_name(){ return $this->department_name; }
	public function getPi_count(){ return $this->pi_count; }
	public function getRoom_count(){ return $this->room_count; }
	public function getIs_active(){ return $this->is_active; }
    public function getSpecialty_lab(){ return $this->specialty_lab; }
	public function getCampus_id(){return $this->campus_id;}
	public function getCampus_name(){return $this->campus_name;}
	
	public function setDepartment_id($department_id){ $this->department_id = $department_id; }
	public function setDepartment_name($name) { $this->department_name = $name; }
	public function setPi_count($pi_count) { $this->pi_count = $pi_count; }
	public function setRoom_count($room_count) { $this->room_count = $room_count; }
	public function setIs_active($active){ $this->is_active = $active; }
    public function setSpecialty_lab($specialty){ $this->specialty_lab = $specialty; }
    public function setCampus_id($id){$this->campus_id = $id;}
    public function setCampus_name($name){$this->campus_name = $name;}
    
}
?>
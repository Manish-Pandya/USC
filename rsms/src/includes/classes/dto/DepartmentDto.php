<?php
class DepartmentDto {
	private $department_id;
	private $department_name;
	private $pi_count;
	private $room_count;
	private $is_active;

	
	public function getDepartment_id(){ return $this->department_id; }
	public function getDepartment_name(){ return $this->department_name; }
	public function getPi_count(){ return $this->pi_count; }
	public function getRoom_count(){ return $this->room_count; }
	public function getIs_active(){ return $this->is_active; }
	
	
	public function setDepartment_id($department_id){ $this->department_id = $department_id; }
	public function setDepartment_name($name) { $this->department_name = $name; }
	public function setPi_count($pi_count) { $this->pi_count = $pi_count; }
	public function setRoom_count($room_count) { $this->room_count = $room_count; }
	public function setIs_active($active){ $this->is_active = $active; }
	
}
?>
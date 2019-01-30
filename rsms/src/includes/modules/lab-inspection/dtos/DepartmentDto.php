<?php
class DepartmentDto {
	private $department_id;
	private $department_name;
	private $is_active;
	private $specialty_lab;
	private $campuses;

	public function __construct($dept = NULL ){
		if( $dept != null ){
			$this->setDepartment_id( $dept->getKey_id());
			$this->setDepartment_name( $dept->getName());
			$this->setIs_active( $dept->getIs_active());
			$this->setSpecialty_lab($dept->getSpecialty_lab());
		}
	}

	public function getDepartment_id(){ return $this->department_id; }
	public function getDepartment_name(){ return $this->department_name; }
	public function getIs_active(){ return $this->is_active; }
    public function getSpecialty_lab(){ return $this->specialty_lab; }
	public function getCampuses(){return $this->campuses;}
	
	public function setDepartment_id($department_id){ $this->department_id = $department_id; }
	public function setDepartment_name($name) { $this->department_name = $name; }
	public function setIs_active($active){ $this->is_active = $active; }
    public function setSpecialty_lab($specialty){ $this->specialty_lab = $specialty; }
    public function setCampuses($campuses){$this->campuses = $campuses;}
    
}
?>
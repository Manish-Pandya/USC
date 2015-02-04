<?php

/**
 *
 * Utility class for representing related entities and their loading
 *
 * @author Hoke Currie, GraySail LLC
 */
class LocationsDto {

	private $room_name;
	private $room_id;
	private $pi_name;
	private $pi_key_id;
	private $department_name;
	private $department_id;
	private $building_id;
	private $principal_investigators;


	/*SELECT a.key_id as room_id, a.building_id as building_id, a.name as room_name, c.key_id as pi_key_id, CONCAT(f.first_name, ' ', f.last_name) as pi_name, e.key_id as department_id, e.name as department_name
	FROM room a
	LEFT JOIN principal_investigator_room b
	ON a.key_id = b.room_id
	LEFT JOIN principal_investigator c
	ON b.principal_investigator_id = c.key_id
	LEFT JOIN principal_investigator_department d
	ON c.key_id = d.principal_investigator_id
	LEFT JOIN department e
	ON d.department_id = e.key_id
	LEFT JOIN erasmus_user f
	ON c.user_id = f.key_id
	ORDER BY a.building_id, c.key_id;*/



	public function getRoom_name()
	{
	    return $this->room_name;
	}

	public function setRoom_name($room_name)
	{
	    $this->room_name = $room_name;
	}

	public function getRoom_id()
	{
	    return $this->room_id;
	}

	public function setRoom_id($room_id)
	{
	    $this->room_id = $room_id;
	}

	public function getPi_name()
	{
	    return $this->pi_name;
	}

	public function setPi_name($pi_name)
	{
	    $this->pi_name = $pi_name;
	}

	public function getPi_key_id()
	{
	    return $this->pi_key_id;
	}

	public function setPi_key_id($pi_key_id)
	{
	    $this->pi_key_id = $pi_key_id;
	}

	public function getDepartment_name()
	{
	    return $this->department_name;
	}

	public function setDepartment_name($department_name)
	{
	    $this->department_name = $department_name;
	}

	public function getDepartment_id()
	{
	    return $this->department_id;
	}

	public function setDepartment_id($department_id)
	{
	    $this->department_id = $department_id;
	}

	public function getBuilding_id()
	{
	    return $this->building_id;
	}

	public function setBuilding_id($building_id)
	{
	    $this->building_id = $building_id;
	}

	public function getPrincipal_investigators()
	{
	    return $this->principal_investigators;
	}

	public function setPrincipal_investigators($principal_investigators)
	{
	    $this->principal_investigators = $principal_investigators;
	}
}


?>

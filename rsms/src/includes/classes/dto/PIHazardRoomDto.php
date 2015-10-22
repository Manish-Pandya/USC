<?php

class PIHazardRoomDto {
	
	private $principal_investigator_id;
	private $hazard_id;
	private $room_name;
	private $room_id;
	private $principal_investigator_hazard_room_relation_id;
	private $containsHazard;
	private $status;
	
	public function getPrincipal_investigator_id(){
		return $this->principal_investigator_id;
	}
	
	public function setPrincipal_investigator_id($principal_investigator_id){
		$this->principal_investigator_id = $principal_investigator_id;
	}
	
	public function getHazard_id(){
		return $this->hazard_id;
	}
	
	public function setHazard_id($hazard_id){
		$this->hazard_id = $hazard_id;
	}
	
	public function getRoom_name(){
		return $this->room_name;
	}
	
	public function setRoom_name($room_name){
		$this->room_name = $room_name;
	}
	
	public function getRoom_id(){
		return $this->room_id;
	}
	
	public function setRoom_id($room_id){
		$this->room_id = $room_id;
	}
	
	public function getPrincipal_investigator_hazard_room_relation_id(){
		return $this->principal_investigator_hazard_room_relation_id;
	}
	
	public function setPrincipal_investigator_hazard_room_relation_id($principal_investigator_hazard_room_relation_id){
		$this->principal_investigator_hazard_room_relation_id = $principal_investigator_hazard_room_relation_id;
	}
	
	public function getContainsHazard(){
		return $this->containsHazard;
	}
	
	public function setContainsHazard($containsHazard){
		$this->containsHazard = $containsHazard;
	}
	
	public function getStatus(){
		return $this->status;
	}
	
	public function setStatus($status){
		$this->status = $status;
	}
}
?>
	
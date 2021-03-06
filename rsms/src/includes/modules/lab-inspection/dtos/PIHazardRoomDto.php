<?php

class PIHazardRoomDto {

	private $principal_investigator_id;
	private $hazard_id;
	private $masterHazardId;
	private $room_name;
	private $room_id;
	private $building_id;
	private $building_name;
	private $principal_investigator_hazard_room_relation_id;
	private $containsHazard;
	private $status;
	private $hasMultiplePis;
    private $otherLab;
    private $stored;

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

	public function getMasterHazardId(){
		return $this->masterHazardId;
	}

	public function setMasterHazardId($masterHazardId){
		$this->masterHazardId = $masterHazardId;
	}

	public function getRoom_name(){
		return $this->room_name;
	}

	public function setRoom_name($room_name){
		$this->room_name = $room_name;
	}

	public function getBuilding_name(){
		return $this->building_name;
	}

	public function setBuilding_name($building_name){
		$this->building_name = $building_name;
	}

	public function getBuilding_id(){
		return $this->building_id;
	}

	public function setBuilding_id($building_id){
		$this->building_id = $building_id;
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
        if($this->status == "Stored Only"){
            $this->status = "STORED_ONLY";
        }elseif($this->status == "In Use" || $this->status == null){
            $this->status = "IN_USE";
        }
		return $this->status;
	}

	public function setStatus($status){
		$this->status = $status;
	}

    public function getStored(){return $this->stored;}
    public function setStored($stored){$this->stored = $stored;}

	public function getHasMultiplePis(){
		return $this->hasMultiplePis;
	}

	public function setHasMultiplePis($hasMultiplePis){
		$this->hasMultiplePis = $hasMultiplePis;
	}

    public function getOtherLab(){
		return $this->otherLab;
	}
	public function setOtherLab($other){
		$this->otherLab = $other;
	}

}
?>
	
<?php

/**
 *
 * Utility class for representing related entities and their loading
 *
 * @author Matt Breeden, GraySail LLC
 */
 
 class LeafHazardPiDto{
	private $hazard_id;
	private $hazard_name;
	private $roomIds;
	private $piHas;
	private $principal_investigator_id;
	private $master_hazard_id;
	
	public function getHazard_id(){
		return $this->hazard_id;
	}
	public function setHazard_id($hazard_id){
		$this->hazard_id = $hazard_id;
	}

	public function getHazard_name(){
		return $this->hazard_name;
	}
	public function setHazard_name($hazard_name){
		$this->hazard_name = $hazard_name;
	}

	public function getRoomIds(){
		if($this->roomIds == null){
			global $db;
			$dao = new GenericDao(new PrincipalInvestigatorHazardRoomRelation());
			$this->roomIds = $dao->getRoomIdsByPiAndHazarIds($this->getPrincipal_investigator_id(), $this->getHazard_id());
		}
		return $this->roomIds;
		
	}
	public function setRoomIds($roomIds){
		$this->roomIds = $roomIds;
	}
	
	public function getPiHas(){
		if($this->piHas == null){
			$this->piHas = false;
			if($this->getRoomIds() != null){
				$this->piHas = count($this->getRoomIds() > 0);
			}
		}
		return (bool) $this->piHas;
	}
	public function setPiHas($piHas){
		$this->piHas = $piHas;
	}
	
	public function getPrincipal_investigator_id(){
		return $this->principal_investigator_id;
	}
	public function setPrincipal_investigator_id($id){
		$this->principal_investigator_id = $id;
	}
	
	public function getMaster_hazard_id(){
		return $this->master_hazard_id;
	}
	public function setMaster_hazard_id($id){
		$this->master_hazard_id = $id;
	}
}
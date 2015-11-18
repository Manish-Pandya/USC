<?php
Class RelationDto{
	private $table;
	private $masterId;
	private $childId;
	
	public function getTable(){
		return $this->table;
	}
	
	public function setTable($table){
		$this->table = $table;
	}
	
	public function getMasterId(){
		return $this->masterId;
	}
	
	public function setMasterId($masterId){
		$this->masterId = $masterId;
	}
	
	public function getChildId(){
		return $this->childId;
	}
	
	public function setChildId($childId){
		$this->childId = $childId;
	}
}

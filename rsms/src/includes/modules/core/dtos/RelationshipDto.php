<?php
class RelationshipDto {
	private $master_id;
	private $relation_id;
	private $add;

	
	public function getMaster_id(){ return $this->master_id; }
	public function getRelation_id(){ return $this->relation_id; }
	public function getAdd(){ return $this->add; }
	
	public function setMaster_id($key_id){ $this->master_id = $key_id; }
	public function setRelation_id($key_id) { $this->relation_id = $key_id; }
	public function setAdd($add) { $this->add = $add; }
}
?>
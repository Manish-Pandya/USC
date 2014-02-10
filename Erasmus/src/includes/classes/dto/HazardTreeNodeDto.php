<?php
class HazardTreeNodeDto {
	private $key_id;
	private $hazard_name;
	private $room_ids;
	private $children;
	
	public function __construct( $key_id, $hazard_name, $room_ids, $children ){
		$this->key_id = $key_id;
		$this->hazard_name = $hazard_name;
		$this->room_ids = $room_ids;
		$this->children = $children;
	}
	
	public function getKey_Id(){ return $this->key_id; }
	public function getHazardName(){ return $this->hazard_name; }
	public function getRoomIds(){ return $this->room_ids; }
	public function getChildren(){ return $this->children; }
}
?>
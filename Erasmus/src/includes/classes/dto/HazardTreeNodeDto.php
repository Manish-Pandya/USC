<?php
class HazardTreeNodeDto {
	private $key_id;
	private $hazard_name;
	private $room_dtos;
	private $children;
	
	public function __construct( $key_id, $hazard_name, $room_dtos, $children ){
		$this->key_id = $key_id;
		$this->hazard_name = $hazard_name;
		$this->room_dtos = $room_dtos;
		$this->children = $children;
	}
	
	public function getKey_Id(){ return $this->key_id; }
	public function getHazardName(){ return $this->hazard_name; }
	public function getRoomDtos(){ return $this->room_dtos; }
	public function getChildren(){ return $this->children; }
}
?>
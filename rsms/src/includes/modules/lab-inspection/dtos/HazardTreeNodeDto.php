<?php
class HazardTreeNodeDto {
	private $key_id;
	private $hazard_name;
	private $possible_rooms;
	private $children;
	private $isPresent;
	private $ParentId;
	
	public function __construct( $key_id, $hazard_name, $possible_rooms, $children, $parentIds ){
		$this->key_id = $key_id;
		$this->hazard_name = $hazard_name;
		$this->possible_rooms = $possible_rooms;
		$this->children = $children;
		$this->parentIds = $parentIds;
		$this->isPresent = false;
		
		foreach ($possible_rooms as $room){
			if($room->getContainsHazard() == true){
				$this->isPresent = true;
			}
		}
		
	}
	
	public function getKey_Id(){ return $this->key_id; }
	public function getHazardName(){ return $this->hazard_name; }
	public function getPossibleRooms(){ return $this->possible_rooms; }
	public function getChildren(){ return $this->children; }
	public function getIsPresent(){ return $this->isPresent; }
	public function getParentIds(){ return $this->parentIds; }
}
?>
<?php
class RoomDto {
	private $key_id;
	private $room_name;
	private $containsHazard;
	private $isAllowed;

	public function __construct( $key_id, $room_name, $containsHazard, $isAllowed = null ){
		$this->key_id = $key_id;
		$this->room_name = $room_name;
		$this->containsHazard = $containsHazard;
		if($isAllowed !== null){
			$this->isAllowed = $isAllowed;
		}
	}
	
	public function getKey_Id(){ return $this->key_id; }
	public function getRoomName(){ return $this->room_name; }
	public function getContainsHazard(){ return $this->containsHazard; }
	public function getIsAllowed(){ return $this->isAllowed; }

	public function setKey_id($key_id){ $this->key_id = $key_id; }
	public function setRoomName($room_name) { $this->room_name = $room_name; }
	public function setContainsHazard($containsHazard){ $this->containsHazard = $containsHazard; }
	public function setIsAllowed($isAllowed){ $this->isAllowed = $isAllowed; }
}
?>
<?php
class RoomDto {
	private $key_id;
	private $room_name;
	
	public function __construct( $key_id, $room_name ){
		$this->key_id = $key_id;
		$this->room_name = $room_name;
	}
	
	public function getKey_Id(){ return $this->key_id; }
	public function getRoomName(){ return $this->room_name; }
}
?>
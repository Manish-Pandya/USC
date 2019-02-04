<?php

class PickupDto {
	private $user_id;
	private $date;
	private $containers;
	
	public function __construct( $user_id, $date, $containers ) {
		$this->user_id = $user_id;
		$this->date = $date;
		$this->containers = $containers;
	}
	
	public function getUser_id() { return $this->user_id; }
	public function getDate() { return $this->date; }
	public function getContainers() { return $this->containers; }
	
	public function setUser_id($newId) { $this->user_id = $newId; }
	public function setDate($newDate) { $this->date = $newDate; }
	public function setContainers($newContainers) { $this->containers = $newContainers; }
}
?>
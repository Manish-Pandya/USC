<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Building {
	
	/** Array of Room entities contained within this Building */
	private $rooms;
	
	public function __construct(){

	}
	
	public function getRooms(){ return $this->rooms; }
	public function setRooms($rooms){ $this->rooms = $rooms; }
	
}
?>
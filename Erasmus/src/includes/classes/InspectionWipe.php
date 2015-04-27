<?php

include_once 'RadCrud.php';

/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class InspectionWipe extends RadCrud {
	/** Name of the DB Table */
	protected static $TABLE_NAME = "inspection_wipe";
	
	/** Key/Value array listing column names and their types */
	protected static $COLUMN_NAMES_AND_TYPES = array(
			"room_id"					    => "integer",
			"curie_level"					=> "float",
			"notes"							=> "text",
			"inspection_wipe_test_id"		=> "integer",
	
			//GenericCrud
			"key_id"						=> "integer",
			"is_active"						=> "boolean",
			"date_last_modified"			=> "timestamp",
			"last_modified_user_id"			=> "integer",
			"date_created"					=> "timestamp",
			"created_user_id"				=> "integer"
	);
	
	
	//access information
	
	private $room_id;
	private $room;
	
	private $currie_level;
	
	private $notes;
	
	private $inspection_wipe_test_id;
	private $inspection_wipe_test;
		
	public function getRoom(){
		$roomDAO = new GenericDAO(new Room());
		$this->room = $roomDAO->getById($this->room_id);
		return $this->room;
	}
	
	public function getRoom_id() {return $this->room_id;}
	public function setRoom_id($room_id) {$this->room_id = $room_id;}
	
	public function getCurrie_level() {return $this->currie_level;}
	public function setCurrie_level($currie_level) {$this->currie_level = $currie_level;}
	
	public function getNotes() {return $this->notes;}
	public function setNotes($notes) {$this->notes = $notes;}
	
	public function getInspection_wipe_test_id() {return $this->inspection_wipe_test_id;}
	public function setInspection_wipe_test_id($inspection_wipe_test_id) {$this->inspection_wipe_test_id = $inspection_wipe_test_id;}
	
	public function getInspection_wipe_test() {
		$inspectionWipeTestDAO = new GenericDAO(new InspectionWipeTest());
		$this->inspection_wipe_test = $inspectionWipeTestDAO->getById($this->inspection_wipe_test_id);
		return $this->inspection_wipe_test;
	}
	
}
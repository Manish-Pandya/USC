<?php
/**
 *
 *
 *
 * @author Mitch Martin, GraySail LLC
 */
class Inspector extends User {
	
	/** Array of Inspection entities in which this Inspector took part */
	private $inspections;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function getinspections(){ return $this->inspections; }
	public function setinspections($inspections){ $this->inspections = $inspections; }
}
?>
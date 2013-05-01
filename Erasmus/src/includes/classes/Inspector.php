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
}
?>
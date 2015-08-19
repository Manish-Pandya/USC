<?php
/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class PendingHazardChange extends PendingChange {
	
	private $hazard;	
	
	public function getHazard() {
		if($this->hazard === NULL && $this->hasPrimaryKeyValue()) {
			$hazardDao = new GenericDAO(new hazard());
			$this->hazard = $hazardDao->getById($this->parent_id);
		}
		return $this->hazard;
	}
}
?>
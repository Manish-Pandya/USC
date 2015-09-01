<?php
/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class PendingHazardChange extends PendingChange {
	
	private $hazard;	
	
	public function __construct(){
	
		// Define which subentities to load
		$entityMaps = array();
		//$entityMaps[] = new EntityMap("lazy","getDeficiencySelection");
		$entityMaps[] = new EntityMap("eager","getParent_id");
		$entityMaps[] = new EntityMap("lazy","getHazard");
	
		$this->setEntityMaps($entityMaps);
	
	}
	public function getHazard() {
		if($this->hazard === NULL && $this->hasPrimaryKeyValue()) {
			$hazardDao = new GenericDAO(new hazard());
			$this->hazard = $hazardDao->getById($this->parent_id);
		}
		return $this->hazard;
	}
}
?>
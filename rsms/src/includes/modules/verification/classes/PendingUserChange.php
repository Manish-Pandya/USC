<?php
/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class PendingUserChange extends PendingChange {
	
	private $user;	
	
	public function __construct(){
	
		// Define which subentities to load
		$entityMaps = array();
		//$entityMaps[] = new EntityMap("lazy","getDeficiencySelection");
		$entityMaps[] = new EntityMap("eager","getParent_id");
		$entityMaps[] = new EntityMap("lazy","getUser");
		
		$this->setEntityMaps($entityMaps);
	
	}
	public function getUser() {
		if($this->user === NULL && $this->hasPrimaryKeyValue()) {
			$userDao = new GenericDAO(new User());
			$this->user = $userDao->getById($this->parent_id);
		}
		return $this->user;
	}
}
?>
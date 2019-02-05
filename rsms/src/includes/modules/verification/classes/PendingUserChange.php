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

    }

    public static function defaultEntityMaps(){
		$entityMaps = array();
		$entityMaps[] = new EntityMap("eager","getParent_id");
		$entityMaps[] = new EntityMap("lazy","getUser");
		
		return $entityMaps;
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
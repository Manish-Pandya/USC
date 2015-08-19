<?php
/**
 *
 *
 *
 * @author Matt Breeden, GraySail LLC
 */
class PendingUserChange extends PendingChange {
	
	private $user;	
	
	public function getUser() {
		if($this->user === NULL && $this->hasPrimaryKeyValue()) {
			$userDao = new GenericDAO(new User());
			$this->user = $userDao->getById($this->parent_id);
		}
		return $this->user;
	}
}
?>
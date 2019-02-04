<?php

class UserRoleRelation {
	private $user_id;
	private $role_id;

	public function getUser_id() { return $this->user_id; }
	public function getRole_id() { return $this->role_id; }

	public function setUser_id($newId) { $this->user_id = $newId; }
	public function setRole_id($newId) { $this->role_id = $newId; }
}
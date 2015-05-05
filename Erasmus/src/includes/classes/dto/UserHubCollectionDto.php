<?php
Class UserHubCollectionDTO{
	private $pis;
	private $users;
	private $inspectors;

	public function getPis()
	{
	    return $this->pis;
	}

	public function setPis($pis)
	{
	    $this->pis = $pis;
	}

	public function getUsers()
	{
	    return $this->users;
	}

	public function setUsers($users)
	{
	    $this->users = $users;
	}

	public function getInspectors()
	{
	    return $this->inspectors;
	}

	public function setInspectors($inspectors)
	{
	    $this->inspectors = $inspectors;
	}
}
?>
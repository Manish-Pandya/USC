<?php

/**
 * Factory for creating Daos
 *
 * Used by getDao in action_functions. Helpful for unit testing: inject mock
 * GenericDao in constructor for unit testing action functions sepparate from
 * the real GenericDao
 */
// TODO create interface for Dao Factories
class DaoFactory {

	public $modelDao;
	
	// only instantiates one of each GenericDao
	private $daos;
	
	public function __construct( $dao ) {
		$this->modelDao = $dao;
	}
	
	public function getDaos() {
		return $this->daos;
	}
	
	public function addDao($name, $newDao) {
		$this->daos[$name] = $newDao;
	}
	
	public function setModelDao( $newDao ) {
		$this->modelDao = $newDao;

	}

	public function getDao( $modelObject ) {
		$modelObjectName = get_class($modelObject);
		$daos = $this->getDaos();

		// GenericDao with this model object has already been created
		if( array_key_exists($modelObjectName, $daos) ) {
			return $daos[$modelObjectName];
		}
		else {
			// no such GenericDao yet
			$dao = clone $this->modelDao;
			$dao->setModelObject($modelObject);
			$this->addDao($modelObjectName, $dao);
			return $dao;
		}
	}
	
}

?>
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
	
	public function __construct( $dao ) {
		$this->modelDao = $dao;
	}
	
	public function setModelDao( $newDao ) {
		$this->modelDao = $newDao;

	}

	public function createDao( $modelObject ) {
		// prevents returned dao from affecting modelDao inadvertently
		$dao = clone $this->modelDao;
		$dao->setModelObject($modelObject);

		return $dao;
	}
	
}

?>
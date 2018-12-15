<?php

/**
 *
 * Utility class for passing an arbitrary number of where clauses into a prepared SQL query
 *
 * @author GrayBeard Entity Generator
 * @author Matt Breeden
 */

class WhereClauseGroup {
	
	/** an array of WhereClauses objects **/
	private $clauses = array();
	
	public function __construct( $clauses = null ){
		if($clauses != NULL && is_array($clauses)){
			$this->clauses = $clauses;
		}
	}
	
	public function getClauses(){return $this->clauses;}
	public function setClauses($clauses){$this->clauses = $clauses;}
	
}
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
	private $clauses;
	
	public function getClauses(){return $this->clauses;}
	public function setClauses($clauses){$this->clauses = $clauses;}
	
}
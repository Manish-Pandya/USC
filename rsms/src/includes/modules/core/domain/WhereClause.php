<?php

/**
 *
 * Object for describing a MySQL where clause
 *
 * @author GrayBeard Entity Generator
 * @author Matt Breeden
 */

class WhereClause {
	
	/** the column we are Whereing on  **/
	private $col;
	
	/** the operator for the where clause */
	private $operator;
	
	/** the value for comparison */
	private $val;
	
	public function __construct( $col = NULL, $operator = NULL, $val = NULL){
		if($col != null){
			$this->col = $col;
		}
		
		if($operator != NULL){
			$this->operator = $operator;
		}
		
		if($val != NULL){
			$this->val = $val;
		}
	}
	
	public function getCol() {
		return $this->col;
	}
	public function setCol($col) {
		$this->col = $col;
	}
	public function getOperator() {
		return $this->operator;
	}
	public function setOperator($operator) {
		$this->operator = $operator;
	}
	public function getVal() {
		return $this->val;
	}
	public function setVal($val) {
		$this->val = $val;
	}

}
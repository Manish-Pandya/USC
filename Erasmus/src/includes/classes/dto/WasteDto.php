<?php
/**
 * Dto class with the name of a specific waste type, and the amount used
 * 
 * 
 * @author Perry Cate, GraySail LLC
 */

class WasteDto {
	private $type;
	private $amount;
	
	public function __construct($wasteType, $wasteAmount) {
		$this->type = $wasteType;
		$this->amount = $wasteAmount;
	}
	
	public function getType() {
		return $this->type;
	}

	public function setType($newType) {
		$this->type = $newType;
	}
	
	public function getAmount() {
		return $this->amount;
	}
	
	public function setAmount($newAmount) {
		$this->amount = $newAmount;
	}
}
<?php

class IsotopeAmountDTO {
	private $isotope_name;
	private $curie_level;
	
	public function getIsotopeName() {
		return $this->isotope_name;
	}
	public function setIsotopeName($isotope_name) {
		$this->isotope_name = $isotope_name;
	}
	public function getCurie_level() {
		return $this->curie_level;
	}
	public function setCurieLevel($currie_level) {
		$this->curie_level = $currie_level;
	}
	public function addCuries($moreCuries){
		if($this->curie_level == NULL)$this->curie_level = 0;
		$this->curie_level = $this->curie_level + $moreCuries;
	}
	
}
?>
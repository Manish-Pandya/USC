<?php

class IsotopeAmountDTO {
	private $isotope_name;
	private $isotope_id;
	private $curie_level;
	private $waste;
    private $is_mass;

	public function getIsotope_name() {
		return $this->isotope_name;
	}
	public function setIsotope_name($isotope_name) {
		$this->isotope_name = $isotope_name;
	}
	public function getIsotope_id() {
		return $this->isotope_id;
	}
	public function setIsotope_id($isotope_id) {
		$this->isotope_id = $isotope_id;
	}
	public function getCurie_level() {
		return $this->curie_level;
	}
	public function setCurie_level($currie_level) {
		$this->curie_level = $currie_level;
	}
	public function addCuries($moreCuries){
		if($this->curie_level == NULL)$this->curie_level = 0;
		$this->curie_level = $this->curie_level + $moreCuries;
	}

    public function getWaste(){return $this->waste;}
    public function setWaste($w){$this->waste = $w;}

    public function getIs_mass(){return (boolean) $this->is_mass;}
    public function setIs_mass($is){ $this->is_mass = $is; }
}
?>
<?php

/**
 * RadReportDTO short summary.
 *
 * RadReportDTO description.
 *
 * @version 1.0
 * @author Matt Breeden
 */
class RadReportDTO
{
    private $isotope_name;
    private $isotope_id;
    private $ordered;
    private $waste;
    private $shipped;
    private $poured;
    private $transferred;
    private $calculated;
    private $is_mass;
    private $auth_limit;

    public function getOrdered(){
		return (float) $this->ordered;
	}

	public function setOrdered($ordered){
		$this->ordered = $ordered;
	}

	public function getWaste(){
		return (float) $this->waste;
	}

	public function setWaste($waste){
		$this->waste = $waste;
	}

	public function getShipped(){
		return (float) $this->shipped;
	}

	public function setShipped($shipped){
		$this->shipped = $shipped;
	}

	public function getPoured(){
		return (float) $this->poured;
	}

	public function setPoured($poured){
		$this->poured = $poured;
	}

	public function getTransferred(){
		return (float) $this->transferred;
	}

	public function setTransferred($transferred){
		$this->transferred = $transferred;
	}

    public function getIsotope_name(){
		return $this->isotope_name;
	}

	public function setIsotope_name($isotope_name){
		$this->isotope_name = $isotope_name;
	}

	public function getIsotope_id(){
		return $this->isotope_id;
	}

	public function setIsotope_id($isotope_id){
		$this->isotope_id = $isotope_id;
	}

    public function getIs_mass(){
		return (boolean) $this->is_mass;
	}

	public function setIs_mass($is_mass){
		$this->is_mass = $is_mass;
	}

	public function getAuth_limit(){
		return (int) $this->auth_limit;
	}

	public function setAuth_limit($auth_limit){
		$this->auth_limit = $auth_limit;
	}

    public function calculate(){
        return;
        if($this->calculated)return;
        $this->ordered = $this->ordered - $this->waste - $this->poured - $this->transferred;
        $this->waste = $this->waste - $this->poured - $this->transferred;
        $this->calculated = true;
    }
}
<?php

trait T_InspectionPresentHazards {
    public $is_rad;
    public $bio_hazards_present;
    public $chem_hazards_present;
    public $recombinant_dna_present;
	public $rad_hazards_present;
    public $corrosive_gas_present;
    public $flammable_gas_present;
    public $toxic_gas_present;
    public $hf_present;
    public $lasers_present;
    public $animal_facility;
    public $xrays_present;

    public function getIs_rad(){ return $this->is_rad; }
    public function setIs_rad($val){ $this->is_rad = $val; }
    public function getBio_hazards_present() { return $this->bio_hazards_present; }
    public function setBio_hazards_present( $val ) { $this->bio_hazards_present = $val; }

    public function getRecombinant_dna_present() { return $this->recombinant_dna_present; }
    public function setRecombinant_dna_present( $val ) { $this->recombinant_dna_present = $val; }

    public function getChem_hazards_present() { return $this->chem_hazards_present; }
    public function setChem_hazards_present( $val ) { $this->chem_hazards_present = $val; }

    public function getRad_hazards_present() { return $this->rad_hazards_present; }
    public function setRad_hazards_present( $val ) { $this->rad_hazards_present = $val; }

    public function getCorrosive_gas_present() { return $this->corrosive_gas_present; }
    public function setCorrosive_gas_present( $val ) { $this->corrosive_gas_present = $val; }

    public function getFlammable_gas_present() { return $this->flammable_gas_present; }
    public function setFlammable_gas_present( $val ) { $this->flammable_gas_present = $val; }

    public function getToxic_gas_present() { return $this->toxic_gas_present; }
    public function setToxic_gas_present( $val ) { $this->toxic_gas_present = $val; }

    public function getHf_present() { return $this->hf_present; }
    public function setHf_present( $val ) { $this->hf_present = $val; }

    public function getLasers_present() { return $this->lasers_present; }
    public function setLasers_present( $val ) { $this->lasers_present = $val; }

    public function getAnimal_facility() { return $this->animal_facility; }
    public function setAnimal_facility( $val ) { $this->animal_facility = $val; }

    public function getXrays_present() { return $this->xrays_present; }
    public function setXrays_present( $val ) { $this->xrays_present = $val; }
}
?>
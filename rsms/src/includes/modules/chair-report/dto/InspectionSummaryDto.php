<?php

/**
 * @author Mitch Martin, GraySail LLC
 */
class InspectionSummaryDto {
    private $inspection_id;
    private $principal_investigator_name;
    private $principal_investigator_id;
    private $schedule_year;
    private $schedule_month;
    private $started_date;
    private $closed_date;
    private $notification_date;
    private $cap_submitted_date;
    private $inspection_status;
    private $items_inspected;
    private $items_compliant;
    private $pending_caps;
    private $department_id;
    private $department_name;

    private $is_rad;
    private $bio_hazards_present;
	private $chem_hazards_present;
	private $rad_hazards_present;
    private $corrosive_gas_present;
    private $flammable_gas_present;
    private $toxic_gas_present;
    private $hf_present;
    private $lasers_present;
    private $animal_facility;
    private $xrays_present;

    public function getInspection_id(){
        return $this->inspection_id;
    }
    
    public function getPrincipal_investigator_name(){
        return $this->principal_investigator_name;
    }
    
    public function getPrincipal_investigator_id(){
        return $this->principal_investigator_id;
    }
    
    public function getSchedule_year(){
        return $this->schedule_year;
    }

    public function getSchedule_month(){
        return $this->schedule_month;
    }
    
    public function getStarted_date(){
        return $this->started_date;
    }
    
    public function getClosed_date(){
        return $this->closed_date;
    }
    
    public function getNotification_date(){
        return $this->notification_date;
    }
    
    public function getCap_submitted_date(){
        return $this->cap_submitted_date;
    }
    
    public function getInspection_status(){
        return $this->inspection_status;
    }

    public function getItems_inspected(){
        return $this->items_inspected;
    }

    public function getItems_compliant(){
        return $this->items_compliant;
    }

    public function getPending_caps(){
        return $this->pending_caps;
    }
    
    public function getDepartment_id(){
        return $this->department_id;
    }
    
    public function getDepartment_name(){
        return $this->department_name;
    }

    public function setInspection_id($val){
        $this->inspection_id = $val;
    }
    
    public function setPrincipal_investigator_name($val){
        $this->principal_investigator_name = $val;
    }
    
    public function setPrincipal_investigator_id($val){
        $this->principal_investigator_id = $val;
    }
    
    public function setSchedule_year($val){
        $this->schedule_year = $val;
    }

    public function setSchedule_month($val){
        $this->schedule_month = $val;
    }

    public function setStarted_date($val){
        $this->started_date = $val;
    }
    
    public function setClosed_date($val){
        $this->closed_date = $val;
    }
    
    public function setNotification_date($val){
        $this->notification_date = $val;
    }
    
    public function setCap_submitted_date($val){
        $this->cap_submitted_date = $val;
    }
    
    public function setInspection_status($val){
        $this->inspection_status = $val;
    }

    public function setItems_inspected($val){
        $this->items_inspected = $val;
    }

    public function setItems_compliant($val){
        $this->items_compliant = $val;
    }

    public function setPending_caps($val){
        $this->pending_caps = $val;
    }

    public function setDepartment_id($val){
        $this->department_id = $val;
    }
    
    public function setDepartment_name($val){
        $this->department_name = $val;
    }

    public function getIs_rad(){ return $this->is_rad; }
    public function setIs_rad($val){ $this->is_rad = $val; }
    public function getBio_hazards_present() { return $this->bio_hazards_present; }
    public function setBio_hazards_present( $val ) { $this->bio_hazards_present = $val; }

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

    public function getScore(){
        $val = 0;
        if( $this->getItems_inspected() > 0 ){
            $val = ($this->getItems_compliant() / $this->getItems_inspected()) * 100;
        }

        return number_format($val);
    }
}
?>
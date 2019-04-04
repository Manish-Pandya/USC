<?php

/**
 * @author Mitch Martin, GraySail LLC
 */
class InspectionSummaryDto {
    use T_InspectionPresentHazards;

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

    public function getScore(){
        $val = 0;
        if( $this->getItems_inspected() > 0 ){
            $val = ($this->getItems_compliant() / $this->getItems_inspected()) * 100;
        }

        return number_format($val);
    }
}
?>
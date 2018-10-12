<?php

/**
 * @author Mitch Martin, GraySail LLC
 */
class InspectionSummaryDto {
    private $inspection_id;
    private $principal_investigator_name;
    private $principal_investigator_id;
    private $schedule_year;
    private $started_date;
    private $closed_date;
    private $notification_date;
    private $cap_submitted_date;
    private $inspection_status;
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
    
    public function setDepartment_id($val){
        $this->department_id = $val;
    }
    
    public function setDepartment_name($val){
        $this->department_name = $val;
    }
}
?>
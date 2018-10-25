<?php

/**
 * @author Mitch Martin, GraySail LLC
 */
class DepartmentDetailDto {
    private $key_id;
    private $is_active;
    private $name;
    private $specialty_lab;
    private $chair_name;
    private $chair_first_name;
    private $chair_last_name;
    private $chair_id;
    private $chair_email;
    private $availableInspectionYears;
    private $campuses;

    public function getKey_id(){
        return $this->key_id;
    }

    public function setKey_id( $val ){
        $this->key_id = $val;
    }

    public function getIs_active(){
        return $this->is_active;
    }

    public function setIs_active( $val ){
        $this->is_active = $val;
    }

    public function getName(){
        return $this->name;
    }

    public function setName( $val ){
        $this->name = $val;
    }

    public function getSpecialty_lab(){
        return $this->specialty_lab;
    }

    public function setSpecialty_lab( $val ){
        $this->specialty_lab = $val;
    }

    public function getChair_name(){
        return $this->chair_name;
    }

    public function setChair_name( $val ){
        $this->chair_name = $val;
    }

    public function getChair_first_name(){
        return $this->chair_first_name;
    }

    public function setChair_first_name($val){
        $this->chair_first_name = $val;
    }

    public function getChair_last_name(){
        return $this->chair_last_name;
    }

    public function setChair_last_name( $val ){
        $this->chair_last_name = $val;
    }

    public function getChair_id(){
        return $this->chair_id;
    }

    public function setChair_id( $val ){
        $this->chair_id = $val;
    }

    public function getChair_email(){
        return $this->chair_email;
    }

    public function setChair_email( $val ){
        $this->chair_email = $val;
    }

    public function getAvailableInspectionYears(){
        return $this->availableInspectionYears;
    }

    public function setAvailableInspectionYears($val){
        $this->availableInspectionYears = $val;
    }

    public function getCampuses(){
        return $this->campuses;
    }

    public function setCampuses($val){
        $this->campuses = $val;
    }
}
?>
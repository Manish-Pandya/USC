<?php

/**
 * @author Mitch Martin, GraySail LLC
 */
class DepartmentDetailDto {
    private $key_id;
    private $name;
    private $chair_name;
    private $chair_id;
    private $availableInspectionYears;

    public function getKey_id(){
        return $this->key_id;
    }

    public function setKey_id( $val ){
        $this->key_id = $val;
    }

    public function getName(){
        return $this->name;
    }

    public function setName( $val ){
        $this->name = $val;
    }

    public function getChair_name(){
        return $this->chair_name;
    }

    public function setChair_name( $val ){
        $this->chair_name = $val;
    }

    public function getChair_id(){
        return $this->chair_id;
    }

    public function setChair_id( $val ){
        $this->chair_id = $val;
    }

    public function getAvailableInspectionYears(){
        return $this->availableInspectionYears;
    }

    public function setAvailableInspectionYears($val){
        $this->availableInspectionYears = $val;
    }
}
?>
<?php

class InspectionReportMessageContext implements MessageContext {
    public $inspection_id;
    public $inspectionState;
    public $email;

    public function __construct($id = NULL, $state = NULL, $email = NULL){
        $this->inspection_id = $id;
        $this->inspectionState = $state;
        $this->email = $email;
    }

    public function setInspection_id($id){ $this->inspection_id = $id; }
    public function getInspection_id(){ return $this->inspection_id; }

    public function setInspectionState($state){ $this->inspectionState = $state; }
    public function getInspectionState(){ return $this->inspectionState; }

    public function setEmail($email){ $this->email = $email; }
    public function getEmail(){ return $this->email; }
}

?>
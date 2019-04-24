<?php
class InspectionHazardInfoDto {
    use T_InspectionPresentHazards;

    private $inspection_id;

    public function getInspection_id(){ return $this->inspection_id; }
    public function setInspection_id($id){ $this->inspection_id = $id; }
}
?>
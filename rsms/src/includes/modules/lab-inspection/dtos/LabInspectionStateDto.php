<?php
class LabInspectionStateDto {
    public $totals;
    public $pendings;
    public $completes;
    public $correcteds;
    public $uncorrecteds;
    public $unSelectedSumplementals;
    public $noDefs;
    public $noDefIDS;
    public $unselectedIDS;
    public $readyToSubmit;

    public function getTotals(){ return $this->totals; }
    public function setTotals( $val ){ $this->totals = $val; }

    public function getPendings(){ return $this->pendings; }
    public function setPendings( $val ){ $this->pendings = $val; }

    public function getCompletes(){ return $this->completes; }
    public function setCompletes( $val ){ $this->completes = $val; }

    public function getCorrecteds(){ return $this->correcteds; }
    public function setCorrecteds( $val ){ $this->correcteds = $val; }

    public function getUncorrecteds(){ return $this->uncorrecteds; }
    public function setUncorrecteds( $val ){ $this->uncorrecteds = $val; }

    public function getUnSelectedSumplementals(){ return $this->unSelectedSumplementals; }
    public function setUnSelectedSumplementals( $val ){ $this->unSelectedSumplementals = $val; }

    public function getNoDefs(){ return $this->noDefs; }
    public function setNoDefs( $val ){ $this->noDefs = $val; }

    public function getNoDefIDS(){ return $this->noDefIDS; }
    public function setNoDefIDS( $val ){ $this->noDefIDS = $val; }

    public function getUnselectedIDS(){ return $this->unselectedIDS; }
    public function setUnselectedIDS( $val ){ $this->unselectedIDS = $val; }

    public function getReadyToSubmit(){ return $this->readyToSubmit; }
    public function setReadyToSubmit( $val ){ $this->readyToSubmit = $val; }
}
?>
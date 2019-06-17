<?php
class RadModelDto {
	private $user;
	private $authorization;
	private $building;
	private $carboyUseCycle;
	private $carboy;
	private $carboyReadingAmount;
	private $department;
	private $drum;
	private $inspectionWipe;
    private $inspectionWipeTest;
    private $isotope;
    private $miscellaneousWipe;
    private $miscellaneousWipeTest;
    private $otherWaste;
    private $parcel;
    private $parcelUse;
    private $parcelUseAmount;
    private $parcelWipe;
    private $parcelWipeTest;
    private $pIAuthorization;
    private $pickup;
    private $pIQuarterlyInventory;
    private $principalInvestigator;
    private $principalInvestigatorNames;
    private $purchaseOrder;
    private $quarterlInventory;
    private $quarterlyIsotopeAmount;
    private $room;
    private $scintVialCollection;
    private $solidsContainer;
    private $wasteBag;
    private $wasteType;
    private $drumWipe;
    private $drumWipeTest;
    private $miscellaneousWaste;
    private $otherWasteType;

    public function getAuthorization(){
    	return $this->authorization;
    }

    public function setAuthorization($authorization){
    	$this->authorization = $authorization;
    }

    public function getBuilding(){
    	return $this->building;
    }

    public function setBuilding($building){
    	$this->building = $building;
    }

    public function getCarboyUseCycle(){
    	return $this->carboyUseCycle;
    }

    public function setCarboyUseCycle($carboyUseCycle){
    	$this->carboyUseCycle = $carboyUseCycle;
    }

    public function getCarboy(){
    	return $this->carboy;
    }

    public function setCarboy($carboy){
    	$this->carboy = $carboy;
    }

    public function getCarboyReadingAmount(){
    	return $this->carboyReadingAmount;
    }

    public function setCarboyReadingAmount($carboyReadingAmount){
    	$this->carboyReadingAmount = $carboyReadingAmount;
    }

    public function getDepartment(){
    	return $this->department;
    }
    public function setDepartment($department){
    	$this->department = $department;
    }

    public function getDrum(){
    	return $this->drum;
    }

    public function setDrum($drum){
    	$this->drum = $drum;
    }

    public function getInspectionWipe(){
    	return $this->inspectionWipe;
    }

    public function setInspectionWipe($inspectionWipe){
    	$this->inspectionWipe = $inspectionWipe;
    }

    public function getInspectionWipeTest(){
    	return $this->inspectionWipeTest;
    }

    public function setInspectionWipeTest($inspectionWipeTest){
    	$this->inspectionWipeTest = $inspectionWipeTest;
    }

    public function getIsotope(){
    	return $this->isotope;
    }

    public function setIsotope($isotope){
    	$this->isotope = $isotope;
    }

    public function getMiscellaneousWipe(){
    	return $this->miscellaneousWipe;
    }

    public function setMiscellaneousWipe($miscellaneousWipe){
    	$this->miscellaneousWipe = $miscellaneousWipe;
    }

    public function getMiscellaneousWipeTest(){
    	return $this->miscellaneousWipeTest;
    }

    public function setMiscellaneousWipeTest($miscellaneousWipeTest){
    	$this->miscellaneousWipeTest = $miscellaneousWipeTest;
    }

    public function getMiscellaneousWaste(){
    	return $this->miscellaneousWaste;
    }

    public function setMiscellaneousWaste($miscellaneousWaste){
    	$this->miscellaneousWaste = $miscellaneousWaste;
    }


    public function getOtherWaste(){
    	return $this->otherWaste;
    }

    public function setOtherWaste($otherWaste){
    	$this->otherWaste = $otherWaste;
    }

	public function getParcel(){
    	return $this->parcel;
    }

    public function setParcel($parcel){
    	$this->parcel = $parcel;
    }

    public function getParcelUse(){
    	return $this->parcelUse;
    }

    public function setParcelUse($parcelUse){
    	$this->parcelUse = $parcelUse;
    }

    public function getParcelUseAmount(){
    	return $this->parcelUseAmount;
    }

    public function setParcelUseAmount($parcelUseAmount){
    	$this->parcelUseAmount = $parcelUseAmount;
    }

    public function getParcelWipe(){
    	return $this->parcelWipe;
    }

    public function setParcelWipe($parcelWipe){
    	$this->parcelWipe = $parcelWipe;
    }

    public function getParcelWipeTest(){
    	return $this->parcelWipeTest;
    }

    public function setParcelWipeTest($parcelWipeTest){
    	$this->parcelWipeTest = $parcelWipeTest;
    }

    public function getPIAuthorization(){
    	return $this->pIAuthorization;
    }

    public function setPIAuthorization($pIAuthorization){
    	$this->pIAuthorization = $pIAuthorization;
    }

    public function getPickup(){
    	return $this->pickup;
    }

    public function setPickup($pickup){
    	$this->pickup = $pickup;
    }

    public function getPIQuarterlyInventory(){
    	return $this->pIQuarterlyInventory;
    }

    public function setPIQuarterlyInventory($pIQuarterlyInventory){
    	$this->pIQuarterlyInventory = $pIQuarterlyInventory;
    }

    public function getPrincipalInvestigator(){
    	return $this->principalInvestigator;
    }

    public function setPrincipalInvestigator($principalInvestigator){
    	$this->principalInvestigator = $principalInvestigator;
    }

    public function getPrincipalInvestigatorNames(){
        return $this->principalInvestigatorNames;
    }

    public function setPrincipalInvestigatorNames($val){
        $this->principalInvestigatorNames = $val;
    }

    public function getPurchaseOrder(){
    	return $this->purchaseOrder;
    }

    public function setPurchaseOrder($purchaseOrder){
    	$this->purchaseOrder = $purchaseOrder;
    }

    public function getQuarterlInventory(){
    	return $this->quarterlInventory;
    }

    public function setQuarterlInventory($quarterlInventory){
    	$this->quarterlInventory = $quarterlInventory;
    }

    public function getQuarterlyIsotopeAmount(){
    	return $this->quarterlyIsotopeAmount;
    }

    public function setQuarterlyIsotopeAmount($quarterlyIsotopeAmount){
    	$this->quarterlyIsotopeAmount = $quarterlyIsotopeAmount;
    }

    public function getRoom(){
    	return $this->room;
    }

    public function setRoom($room){
    	$this->room = $room;
    }

    public function getScintVialCollection(){
    	return $this->scintVialCollection;
    }

    public function setScintVialCollection($scintVialCollection){
    	$this->scintVialCollection = $scintVialCollection;
    }

    public function getSolidsContainer(){
    	return $this->solidsContainer;
    }

    public function setSolidsContainer($solidsContainer){
    	$this->solidsContainer = $solidsContainer;
    }

    public function getUser(){
    	return $this->user;
    }

    public function setUser($user){
    	$this->user = $user;
    }

    public function getWasteBag(){
    	return $this->wasteBag;
    }

    public function setWasteBag($wasteBag){
    	$this->wasteBag = $wasteBag;
    }

    public function getWasteType(){
    	return $this->wasteType;
    }

    public function setWasteType($wasteType){
    	$this->wasteType = $wasteType;
    }

    public function getDrumWipe(){return $this->drumWipe;}
    public function setDrumWipe($w){$this->drumWipe = $w;}

    public function getDrumWipeTest(){return $this->drumWipeTest;}
    public function setDrumWipeTest($w){$this->drumWipeTest = $w;}

    public function getOtherWasteType(){ return $this->otherWasteType; }
	public function setOtherWasteType($otherWasteType){ $this->otherWasteType = $otherWasteType; }
}
?>
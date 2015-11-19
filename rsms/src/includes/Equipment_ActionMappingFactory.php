<?php
/**
 * Contains action functions specific to the Equipment module.
 *
 * If a non-fatal error occurs, should return an ActionError
 * (or subclass of ActionError) containing information about the error.
 *
 * @author Matt Breeden
 */
class Equipment_ActionManager extends ActionManager {


    /*****************************************************************************\
     *                            Get Functions                                  *
    \*****************************************************************************/
    
    public function getEquipmentModels(){
    	//todo:  create dto for whole model.  
    	$dto = new RadModelDto();
    	
    	$dto->setAuthorization($this->getAllAuthorizations());
    	$dto->setPIAuthorization($this->getAllPIAuthorizations());
    	$dto->setCarboy($this->getAllCarboys());
    	$dto->setCarboyUseCycle($this->getAllCarboyUseCycles());
    	$dto->setDrum($this->getAllDrums());
    	$dto->setInspectionWipe($this->getAllInspectionWipes());
    	$dto->setInspectionWipeTest($this->getAllInspectionWipeTests());
    	$dto->setIsotope($this->getAllIsotopes());
    	$dto->setParcelUseAmount($this->getAllParcelUseAmounts());
    	$dto->setParcelUse($this->getAllParcelUses());
    	$dto->setParcelWipe($this->getAllParcelWipes());
    	$dto->setParcelWipeTest($this->getAllParcelWipeTests());
    	$dto->setParcel($this->getAllParcels());
    	$dto->setPickup($this->getAllPickups());
    	$dto->setPurchaseOrder($this->getAllPurchaseOrders());
    	//$dto->getQuarterlyIsotopeAmount($this->getAllQuarterlyInventories());
    	$dto->setQuarterlInventory($this->getMostRecentInventory());
    	$dto->setScintVialCollection($this->getAllScintVialCollections());
    	$dto->setWasteBag($this->getAllWasteBags());
    	$dto->setSolidsContainer($this->getAllSolidsContainers());
    	$dto->setWasteType($this->getAllWasteTypes());
    	$dto->setUser($this->getAllRadUsers());
    	$dto->setPrincipalInvestigator($this->getAllRadPis());
    	 
    	return $dto;
    	
    }
}

?>

<?php
/**
 * Class that wraps a static accessor that returns all Radiation Safety Action Mappings
 * 
 * @author Perry
 */
class Rad_ActionMappingFactory extends ActionMappingFactory {
	
	public static function readActionConfig() {
		$mappings = new Rad_ActionMappingFactory();
		
		return $mappings->getConfig();
		
	}
	public function getConfig() {
		return array(

			// get functions
			"getIsotopeById" 				=> new ActionMapping("getIsotopeById", "", ""),
			"getCarboyById" 				=> new ActionMapping("getCarboyById", "", ""),
			"getCarboyUseCycleById" 		=> new ActionMapping("getCarboyUseCycleById", "", ""),
			"getDrumById" 					=> new ActionMapping("getDrumById", "", ""),
			"getParcelById" 				=> new ActionMapping("getParcelById", "", ""),
			"getParcelUseById" 				=> new ActionMapping("getParcelUseById", "", ""),
			"getPickupById"    				=> new ActionMapping("getPickupById", "", ""),
			"getPurchaseOrderById"			=> new ActionMapping("getPurchaseOrderById", "", ""),
			"getWasteTypeById"				=> new ActionMapping("getWasteTypeById", "", ""),
			"getWasteBagById"				=> new ActionMapping("getWasteBagById", "", ""),
			"getSolidsContainerById"		=> new ActionMapping("getSolidsContainerById", "", ""),
			"getRadPIById"					=> new ActionMapping("getRadPIById", "", ""),
			"getInspectionWipeTestById"		=> new ActionMapping("getRadPIById", "", ""),
			"getInspectionWipeById"			=> new ActionMapping("getRadPIById", "", ""),
			"getParcelWipeTestById"			=> new ActionMapping("getRadPIById", "", ""),
			"getParcelWipeById"				=> new ActionMapping("getRadPIById", "", ""),
			
			// get entity by relationship functions
			"getAuthorizationsByPIId"		=> new ActionMapping("getAuthorizationsByPIId", "", ""),
			"getWasteBagsByPickupId"		=> new ActionMapping("getWasteBagsByPickupId", "", ""),
			"getResultingDrumsByPickupId" 	=> new ActionMapping("getResultingDrumsByPickupId", "", ""),
			"getParcelUsesByParcelId"		=> new ActionMapping("getParcelUsesByParcelId", "", ""),
			"getParcelUsesFromPISinceDate"  => new ActionMapping("getParcelUsesFromPISinceDate", "", ""),
			"getActiveParcelsFromPIById"	=> new ActionMapping("getActiveParcelsFromPIById", "", ""),
			"getSolidsContainersByRoomId"	=> new ActionMapping("getSolidsContainersByRoomId", "", ""),
				
			// getAll functions
			"getAllAuthorizations"			=> new ActionMapping("getAllAuthorizations", "", ""),
			"getAllCarboys"                 => new ActionMapping("getAllCarboys", "", ""),
			"getAllCarboyUseCycles"			=> new ActionMapping("getAllCarboyUseCycles", "", ""),
			"getAllDrums"					=> new ActionMapping("getAllDrums", "", ""),
			"getAllIsotopes"				=> new ActionMapping("getAllIsotopes", "", ""),
            "getAllParcels"					=> new ActionMapping("getAllParcels", "", ""),
            "getAllParcelUses"				=> new ActionMapping("getAllParcelUses", "", ""),
            "getAllParcelUseAmounts"		=> new ActionMapping("getAllParcelUseAmounts", "", ""),
            "getAllPickups"					=> new ActionMapping("getAllPickups", "", ""),
            "getAllPurchaseOrders"			=> new ActionMapping("getAllPurchaseOrders", "", ""),
			"getAllWasteBags"				=> new ActionMapping("getAllWasteBags", "", ""),
			"getAllWasteTypes"				=> new ActionMapping("getAllWasteTypes", "", ""),
			"getAllSolidsContainers"		=> new ActionMapping("getAllSolidsContainers", "", ""),
			"getAllRadPis"					=> new ActionMapping("getAllRadPis", "", ""),
			"getAllRadUsers"				=> new ActionMapping("getAllRadUsers", "", ""),
			"getAllActivePickups"			=> new ActionMapping("getAllActivePickups", "", ""),
				

			// save functions
			"saveAuthorization" => new ActionMapping("saveAuthorization", "", ""),
			"saveIsotope"		=> new ActionMapping("saveIsotope", "", ""),
			"saveCarboy"		=> new ActionMapping("saveCarboy", "", ""),
			"saveCarboyUseCycle"=> new ActionMapping("saveCarboyUseCycle", "", ""),
			"saveDrum"			=> new ActionMapping("saveDrum", "", ""),
			"saveParcel"		=> new ActionMapping("saveParcel", "", ""),
			"saveParcelUse"		=> new ActionMapping("saveParcelUse", "", ""),
			"savePickup"		=> new ActionMapping("savePickup", "", ""),
			"savePurchaseOrder"	=> new ActionMapping("savePurchaseOrder", "", ""),
			"saveWasteType"		=> new ActionMapping("saveWasteType", "", ""),
			"saveWasteBag"		=> new ActionMapping("saveWasteBag", "", ""),
			"saveSolidsContainer"		=> new ActionMapping("saveSolidsContainer", "", ""),
			"saveSVCollection"	=> new ActionMapping("saveSVCollection", "", ""),
			"saveInspectionWipeTest"	=> new ActionMapping("saveInspectionWipeTest", "", ""),
			"saveInspectionWipe"	=> new ActionMapping("saveInspectionWipe", "", ""),
			"saveParcelWipeTest"	=> new ActionMapping("saveParcelWipeTest", "", ""),
			"saveParcelWipe"	=> new ActionMapping("saveParcelWipe", "", ""),
				
			// other functions
			"getParcelRemainder"			 => new ActionMapping("getParcelRemainder", "", ""),
			"disposeParcelRemainder" 	   	 => new ActionMapping("disposeParcelRemainder", "",""),
			"getWasteAmountsByParcelId"		 => new ActionMapping("getWasteAmountsByParcelId", "", ""),
			"getParcelUseAmountByParcelUseId"=> new ActionMapping("getParcelUseWaste", "", ""),
			"getTotalWasteFromPI"			 => new ActionMapping("getTotalWasteFromPI", "", ""),
			"getWasteFromPISinceDate"        => new ActionMapping("getWastefRomPISinceDate", "", "")
		);
	}
}
?>
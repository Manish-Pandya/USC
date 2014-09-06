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
			"getDisposalLotById" 			=> new ActionMapping("getDisposalLotById", "", ""),
			"getDrumById" 					=> new ActionMapping("getDrumById", "", ""),
			"getParcelById" 				=> new ActionMapping("getParcelById", "", ""),
			"getParcelUseById" 				=> new ActionMapping("getParcelUseById", "", ""),
			"getPickupById"    				=> new ActionMapping("getPickupById", "", ""),
			"getPickupLotById" 				=> new ActionMapping("getPickupLotById", "", ""),
			"getPurchaseOrderById"			=> new ActionMapping("getPurchaseOrderById", "", ""),
			"getWasteTypeById"				=> new ActionMapping("getWasteTypeById", "", ""),
			"getAuthorizationsByPIId"		=> new ActionMapping("getAuthorizationsByPIId", "", ""),
			"getPickupLotsByPickupId"		=> new ActionMapping("getPickupLotsByPickupId", "", ""),
			"getDisposalLotsByPickupLotId" 	=> new ActionMapping("getDisposalLotsByPickupLotId", "", ""),
			"getDisposalLotsByDrumId"		=> new ActionMapping("getDisposalLotsByDrumId", "", ""),
			
			// save functions
			"saveIsotope"		=> new ActionMapping("saveIsotope", "", ""),
			"saveCarboy"		=> new ActionMapping("saveCarboy", "", ""),
			"saveCarboyUseCycle"=> new ActionMapping("saveCarboyUseCycle", "", ""),
			"saveDisposalLot"=> new ActionMapping("saveDisposalLot", "", ""),
			"saveDrum"=> new ActionMapping("saveDrum", "", "")
		);
	}
}
?>
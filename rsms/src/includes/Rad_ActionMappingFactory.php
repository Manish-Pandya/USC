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
				"getIsotopeById" 				=> new ActionMapping("getIsotopeById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getCarboyById" 				=> new ActionMapping("getCarboyById", "", "",$this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getCarboyUseCycleById" 		=> new ActionMapping("getCarboyUseCycleById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getDrumById" 					=> new ActionMapping("getDrumById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getParcelById" 				=> new ActionMapping("getParcelById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getParcelUseById" 				=> new ActionMapping("getParcelUseById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getPickupById"    				=> new ActionMapping("getPickupById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getPurchaseOrderById"			=> new ActionMapping("getPurchaseOrderById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getWasteTypeById"				=> new ActionMapping("getWasteTypeById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getWasteBagById"				=> new ActionMapping("getWasteBagById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getSolidsContainerById"		=> new ActionMapping("getSolidsContainerById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getRadPIById"					=> new ActionMapping("getRadPIById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getInspectionWipeTestById"		=> new ActionMapping("getRadPIById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getInspectionWipeById"			=> new ActionMapping("getRadPIById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getParcelWipeTestById"			=> new ActionMapping("getRadPIById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getParcelWipeById"				=> new ActionMapping("getRadPIById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getRadInspectionById"			=> new ActionMapping("getRadInspectionById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),

				// get entity by relationship functions
				"getAuthorizationsByPIId"		=> new ActionMapping("getAuthorizationsByPIId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getWasteBagsByPickupId"		=> new ActionMapping("getWasteBagsByPickupId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getResultingDrumsByPickupId" 	=> new ActionMapping("getResultingDrumsByPickupId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getParcelUsesByParcelId"		=> new ActionMapping("getParcelUsesByParcelId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getParcelUsesFromPISinceDate"  => new ActionMapping("getParcelUsesFromPISinceDate", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getActiveParcelsFromPIById"	=> new ActionMapping("getActiveParcelsFromPIById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getSolidsContainersByRoomId"	=> new ActionMapping("getSolidsContainersByRoomId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),

				// getAll functions
				"getAllAuthorizations"			=> new ActionMapping("getAllAuthorizations", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllCarboys"                 => new ActionMapping("getAllCarboys", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllCarboyUseCycles"			=> new ActionMapping("getAllCarboyUseCycles", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllDrums"					=> new ActionMapping("getAllDrums", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllIsotopes"				=> new ActionMapping("getAllIsotopes", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllParcels"					=> new ActionMapping("getAllParcels", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllParcelUses"				=> new ActionMapping("getAllParcelUses", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllParcelUseAmounts"		=> new ActionMapping("getAllParcelUseAmounts", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllPickups"					=> new ActionMapping("getAllPickups", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllPurchaseOrders"			=> new ActionMapping("getAllPurchaseOrders", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllWasteBags"				=> new ActionMapping("getAllWasteBags", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllWasteTypes"				=> new ActionMapping("getAllWasteTypes", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllSolidsContainers"		=> new ActionMapping("getAllSolidsContainers", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllRadPis"					=> new ActionMapping("getAllRadPis", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllRadUsers"				=> new ActionMapping("getAllRadUsers", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllActivePickups"			=> new ActionMapping("getAllActivePickups", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllMiscellaneousWipeTests"	=> new ActionMapping("getAllMiscellaneousWipeTests", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getMiscellaneousWipeTests"		=> new ActionMapping("getMiscellaneousWipeTests", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getOpenMiscellaneousWipeTests"	=> new ActionMapping("getOpenMiscellaneousWipeTests", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllSVCollections"			=> new ActionMapping("getAllSVCollections", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),



				// save functions
				"saveAuthorization" 		=> new ActionMapping("saveAuthorization", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveIsotope"				=> new ActionMapping("saveIsotope", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveCarboy"				=> new ActionMapping("saveCarboy", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveCarboyUseCycle"		=> new ActionMapping("saveCarboyUseCycle", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveDrum"					=> new ActionMapping("saveDrum", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveParcel"				=> new ActionMapping("saveParcel", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveParcelUse"				=> new ActionMapping("saveParcelUse", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"savePickup"				=> new ActionMapping("savePickup", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"savePurchaseOrder"			=> new ActionMapping("savePurchaseOrder", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveWasteType"				=> new ActionMapping("saveWasteType", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveWasteBag"				=> new ActionMapping("saveWasteBag", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveSolidsContainer"		=> new ActionMapping("saveSolidsContainer", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveSVCollection"			=> new ActionMapping("saveSVCollection", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveInspectionWipeTest"	=> new ActionMapping("saveInspectionWipeTest", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveInspectionWipe"		=> new ActionMapping("saveInspectionWipe", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"saveInspectionWipes"		=> new ActionMapping("saveInspectionWipes", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"saveParcelWipeTest"		=> new ActionMapping("saveParcelWipeTest", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveParcelWipe"			=> new ActionMapping("saveParcelWipe", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveParcelWipes"			=> new ActionMapping("saveParcelWipes", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveMiscellaneousWipeTest"	=> new ActionMapping("saveMiscellaneousWipeTest", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveMiscellaneousWipe"		=> new ActionMapping("saveMiscellaneousWipe", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveMiscellaneousWipes"	=> new ActionMapping("saveMiscellaneousWipes", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveCarboyReadingAmount"	=> new ActionMapping("saveCarboyReadingAmount", "", "", $this::$ROLE_GROUPS["ADMIN"]),


				// other functions
				"getParcelRemainder"			 => new ActionMapping("getParcelRemainder", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"disposeParcelRemainder" 	   	 => new ActionMapping("disposeParcelRemainder", "","", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"getWasteAmountsByParcelId"		 => new ActionMapping("getWasteAmountsByParcelId", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"getParcelUseAmountByParcelUseId"=> new ActionMapping("getParcelUseWaste", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"getTotalWasteFromPI"			 => new ActionMapping("getTotalWasteFromPI", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"getWasteFromPISinceDate"        => new ActionMapping("getWastefRomPISinceDate", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"getInventoriesByDateRanges"	 => new ActionMapping("getInventoriesByDateRanges", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),


				"createQuarterlyInventories"	 => new ActionMapping("createQuarterlyInventories", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"getMostRecentInventory"	 => new ActionMapping("getMostRecentInventory", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"getPiInventory"				 => new ActionMapping("getPiInventory", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"getCurrentPIInventory"				 => new ActionMapping("getCurrentPIInventory", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"getInventoriesByPiId"		=> new ActionMapping("getInventoriesByPiId","","", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"savePIQuarterlyInventory"	=> new ActionMapping("savePIQuarterlyInventory","","", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
		);
	}
}
?>
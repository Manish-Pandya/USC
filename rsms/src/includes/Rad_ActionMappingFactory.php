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
                "getMiscellaneousWasteById"				=> new ActionMapping("getMiscellaneousWasteById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),

				"getSolidsContainerById"		=> new ActionMapping("getSolidsContainerById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getRadPIById"					=> new ActionMapping("getRadPIById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getInspectionWipeTestById"		=> new ActionMapping("getRadPIById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getInspectionWipeById"			=> new ActionMapping("getRadPIById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getParcelWipeTestById"			=> new ActionMapping("getRadPIById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getParcelWipeById"				=> new ActionMapping("getRadPIById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getRadInspectionById"			=> new ActionMapping("getRadInspectionById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),

				// get entity by relationship functions
				"getAuthorizationById"			=> new ActionMapping("getAuthorizationById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAuthorizationsByPIId"		=> new ActionMapping("getAuthorizationsByPIId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getWasteBagsByPickupId"		=> new ActionMapping("getWasteBagsByPickupId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getResultingDrumsByPickupId" 	=> new ActionMapping("getResultingDrumsByPickupId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getParcelUsesByParcelId"		=> new ActionMapping("getParcelUsesByParcelId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getParcelUsesFromPISinceDate"  => new ActionMapping("getParcelUsesFromPISinceDate", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getActiveParcelsFromPIById"	=> new ActionMapping("getActiveParcelsFromPIById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getSolidsContainersByRoomId"	=> new ActionMapping("getSolidsContainersByRoomId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getPIAuthorizationByPIId"		=> new ActionMapping("getPIAuthorizationByPIId", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getAllCarboyReadingAmounts"	=> new ActionMapping("getAllCarboyReadingAmounts", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getRadConditionById"		    => new ActionMapping("getRadConditionById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),


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
				"getAllScintVialCollections"	=> new ActionMapping("getAllScintVialCollections", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllWasteTypes"				=> new ActionMapping("getAllWasteTypes", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllSolidsContainers"		=> new ActionMapping("getAllSolidsContainers", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllRadPis"					=> new ActionMapping("getAllRadPis", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllRadUsers"				=> new ActionMapping("getAllRadUsers", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllActivePickups"			=> new ActionMapping("getAllActivePickups", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllMiscellaneousWipeTests"	=> new ActionMapping("getAllMiscellaneousWipeTests", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getMiscellaneousWipeTests"		=> new ActionMapping("getMiscellaneousWipeTests", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getOpenMiscellaneousWipeTests"	=> new ActionMapping("getOpenMiscellaneousWipeTests", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllSVCollections"			=> new ActionMapping("getAllSVCollections", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllParcelWipeTests"			=> new ActionMapping("getAllParcelWipeTests", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllParcelWipes"				=> new ActionMapping("getAllParcelWipes", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllPIWipeTests"				=> new ActionMapping("getAllPIWipeTests", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getAllPIWipes"					=> new ActionMapping("getAllPIWipes", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "getAllRadRooms"				=> new ActionMapping("getAllRadRooms", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "removeParcelUseAmountFromPickup"	=> new ActionMapping("removeParcelUseAmountFromPickup", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "getAllRadConditions"		    => new ActionMapping("getAllRadConditions", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),

				"getAllWasteContainersReadyForPickup"
												=> new ActionMapping("getAllWasteContainersReadyForPickup", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),

                "saveDrumWipe"                  => new ActionMapping("saveDrumWipe", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveDrumWipeTest"              => new ActionMapping("saveDrumWipeTest", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveDrumWipesAndChildren"      => new ActionMapping("saveDrumWipesAndChildren", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getAllDrumWipeTests"           => new ActionMapping("getAllDrumWipeTests", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getAllDrumWipes"               => new ActionMapping("getAllDrumWipes", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getDrumWipeTestById"		    => new ActionMapping("getDrumWipeTestById", "", "", $this::$ROLE_GROUPS["ADMIN"]),


				// save functions
				"saveAuthorization" 		=> new ActionMapping("saveAuthorization", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveIsotope"				=> new ActionMapping("saveIsotope", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveCarboy"				=> new ActionMapping("saveCarboy", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveCarboyUseCycle"		=> new ActionMapping("saveCarboyUseCycle", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"saveDrum"					=> new ActionMapping("saveDrum", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveParcel"				=> new ActionMapping("saveParcel", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"saveParcelUse"				=> new ActionMapping("saveParcelUse", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"saveParcelUseAmount"	    => new ActionMapping("saveParcelUseAmount", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"savePickup"				=> new ActionMapping("savePickup", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"deletePickupById"			=> new ActionMapping("deletePickupById", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"savePurchaseOrder"			=> new ActionMapping("savePurchaseOrder", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveWasteType"				=> new ActionMapping("saveWasteType", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveWasteBag"				=> new ActionMapping("saveWasteBag", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"changeWasteBag"			=> new ActionMapping("changeWasteBag", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveSolidsContainer"		=> new ActionMapping("saveSolidsContainer", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveSVCollection"			=> new ActionMapping("saveSVCollection", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"saveInspectionWipeTest"	=> new ActionMapping("saveInspectionWipeTest", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveInspectionWipe"		=> new ActionMapping("saveInspectionWipe", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"saveInspectionWipes"		=> new ActionMapping("saveInspectionWipes", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"saveParcelWipeTest"		=> new ActionMapping("saveParcelWipeTest", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveParcelWipe"			=> new ActionMapping("saveParcelWipe", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveParcelWipes"			=> new ActionMapping("saveParcelWipes", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "savePIWipeTest"		=> new ActionMapping("savePIWipeTest", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"savePIWipe"			=> new ActionMapping("savePIWipe", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"savePIWipes"			=> new ActionMapping("savePIWipes", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"saveParcelWipesAndChildren"=> new ActionMapping("saveParcelWipesAndChildren", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveMiscellaneousWipeTest"	=> new ActionMapping("saveMiscellaneousWipeTest", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveMiscellaneousWipe"		=> new ActionMapping("saveMiscellaneousWipe", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveMiscellaneousWipes"	=> new ActionMapping("saveMiscellaneousWipes", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveCarboyReadingAmount"	=> new ActionMapping("saveCarboyReadingAmount", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"savePIAuthorization"	=> new ActionMapping("savePIAuthorization", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"getAllPIAuthorizations"	=> new ActionMapping("getAllPIAuthorizations", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveMiscellaneousWaste"	=> new ActionMapping("saveMiscellaneousWaste", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getAllMiscellaneousWaste"	=> new ActionMapping("getAllMiscellaneousWaste", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"saveRadCondition"		    => new ActionMapping("saveRadCondition", "", "", $this::$ROLE_GROUPS["RADMIN"]),

				"savePickupNotes"		    => new ActionMapping("savePickupNotes", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),

				"closeWasteContainer"       => new ActionMapping("closeWasteContainer", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),

				// other functions
				"getParcelRemainder"			 => new ActionMapping("getParcelRemainder", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"disposeParcelRemainder" 	   	 => new ActionMapping("disposeParcelRemainder", "","", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getWasteAmountsByParcelId"		 => new ActionMapping("getWasteAmountsByParcelId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getParcelUseAmountByParcelUseId"=> new ActionMapping("getParcelUseWaste", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getTotalWasteFromPI"			 => new ActionMapping("getTotalWasteFromPI", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getWasteFromPISinceDate"        => new ActionMapping("getWastefRomPISinceDate", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getInventoriesByDateRanges"	 => new ActionMapping("getInventoriesByDateRanges", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),


				"createQuarterlyInventories"	 => new ActionMapping("createQuarterlyInventories", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"getMostRecentInventory"	 => new ActionMapping("getMostRecentInventory", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
                "getQuartleryInventoryById"	 => new ActionMapping("getQuartleryInventoryById", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"getPiInventory"				 => new ActionMapping("getPiInventory", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"getPIInventoryById"		 => new ActionMapping("getPIInventoryById", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"getCurrentPIInventory"				 => new ActionMapping("getCurrentPIInventory", "", "", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"getInventoriesByPiId"		=> new ActionMapping("getInventoriesByPiId","","", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"savePIQuarterlyInventory"	=> new ActionMapping("savePIQuarterlyInventory","","", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"updateParcelUse"	=> new ActionMapping("updateParcelUse","","", $this::$ROLE_GROUPS["RADMIN"]),

                "getAllOtherWasteTypes"	=> new ActionMapping("getAllOtherWasteTypes","","", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
                "getOtherWateTypeById"	=> new ActionMapping("getOtherWateTypeById","","", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
                "saveOtherWasteType"	=> new ActionMapping("saveOtherWasteType","","", $this::$ROLE_GROUPS["RSO"]),
                "clearOtherWaste"	    => new ActionMapping("clearOtherWaste","","", $this::$ROLE_GROUPS["RSO"]),
                "assignOtherWasteType"	=> new ActionMapping("assignOtherWasteType","","", $this::$ROLE_GROUPS["RSO"]),
                //        'OtherWasteContainer'  : { getById: "getOtherWasteContainerBiId"   , getAll: "getAllOtherWasteContainers"   , save: "saveOtherWasteContainer" },

                "getAllOtherWasteContainers"	=> new ActionMapping("getAllOtherWasteContainers","","", $this::$ROLE_GROUPS["RSO"]),
                "getOtherWasteContainerBiId"	=> new ActionMapping("getOtherWasteContainerBiId","","", $this::$ROLE_GROUPS["RSO"]),
                "saveOtherWasteContainer"	    => new ActionMapping("saveOtherWasteContainer","","", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "getTotalInventories"	        => new ActionMapping("getTotalInventories","","", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),


				"getRadInventoryReport"	=> new ActionMapping("getRadInventoryReport","","", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"getRadModels"	        => new ActionMapping("getRadModels","","", $this::$ROLE_GROUPS["ALL_RAD_USERS"]),
				"resetRadData"	        => new ActionMapping("resetRadData","","", $this::$ROLE_GROUPS["RADMIN"]),
                "removeFromPickup" 	    => new ActionMapping("removeFromPickup", "", "", $this::$ROLE_GROUPS["EHS"] ),

		);
	}
}
?>
<?php
/**
 * Class that wraps a static accessor that returns all Radiation Safety Action Mappings
 *
 * @author Perry
 */
class Rad_ActionMappingFactory extends ActionMappingFactory {

	public const ROLE_GROUP_ADMIN         = array( 'Admin', RadiationModule::ROLE_ADMIN );
	public const ROLE_GROUP_RSO           = array( 'Admin', RadiationModule::ROLE_ADMIN, RadiationModule::ROLE_INSPECTOR );
	public const ROLE_GROUP_ALL_RAD_USERS = array( 'Admin', RadiationModule::ROLE_ADMIN, 'Safety User', RadiationModule::ROLE_INSPECTOR, RadiationModule::ROLE_CONTACT, RadiationModule::ROLE_USER, LabInspectionModule::ROLE_PI );

	public static function readActionConfig() {
		$mappings = new Rad_ActionMappingFactory();

		return $mappings->getConfig();

	}

	public function getConfig() {
		$radMappings = array(

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
				"getRadPIById"					=> new SecuredActionMapping("getRadPIById", $this::$ROLE_GROUPS["EHS_AND_LAB"], 'RadSecurity::userCanViewRadPI'),
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
				"getPIAuthorizationByPIId"		=> new ActionMapping("getPIAuthorizationByPIId", "", "", self::ROLE_GROUP_ADMIN),
                "getAllCarboyReadingAmounts"	=> new ActionMapping("getAllCarboyReadingAmounts", "", "", self::ROLE_GROUP_ADMIN),
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

                "saveDrumWipe"                  => new ActionMapping("saveDrumWipe", "", "", self::ROLE_GROUP_ADMIN),
                "saveDrumWipeTest"              => new ActionMapping("saveDrumWipeTest", "", "", self::ROLE_GROUP_ADMIN),
                "saveDrumWipesAndChildren"      => new ActionMapping("saveDrumWipesAndChildren", "", "", self::ROLE_GROUP_ADMIN),
                "getAllDrumWipeTests"           => new ActionMapping("getAllDrumWipeTests", "", "", self::ROLE_GROUP_ADMIN),
                "getAllDrumWipes"               => new ActionMapping("getAllDrumWipes", "", "", self::ROLE_GROUP_ADMIN),
                "getDrumWipeTestById"		    => new ActionMapping("getDrumWipeTestById", "", "", self::ROLE_GROUP_ADMIN),


				// save functions
				"saveAuthorization" 		=> new ActionMapping("saveAuthorization", "", "", self::ROLE_GROUP_ADMIN),
				"saveIsotope"				=> new ActionMapping("saveIsotope", "", "", self::ROLE_GROUP_ADMIN),
				"saveCarboy"				=> new ActionMapping("saveCarboy", "", "", self::ROLE_GROUP_ADMIN),
				"saveCarboyUseCycle"		=> new ActionMapping("saveCarboyUseCycle", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"saveDrum"					=> new ActionMapping("saveDrum", "", "", self::ROLE_GROUP_ADMIN),
				"saveParcel"				=> new ActionMapping("saveParcel", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"saveParcelUse"				=> new ActionMapping("saveParcelUse", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"saveParcelUseAmount"	    => new ActionMapping("saveParcelUseAmount", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"savePickup"				=> new ActionMapping("savePickup", "", "", self::ROLE_GROUP_ALL_RAD_USERS),
				"deletePickupById"			=> new ActionMapping("deletePickupById", "", "", self::ROLE_GROUP_ALL_RAD_USERS),
				"savePurchaseOrder"			=> new ActionMapping("savePurchaseOrder", "", "", self::ROLE_GROUP_ADMIN),
				"saveWasteType"				=> new ActionMapping("saveWasteType", "", "", self::ROLE_GROUP_ADMIN),
				"saveWasteBag"				=> new ActionMapping("saveWasteBag", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"changeWasteBag"			=> new ActionMapping("changeWasteBag", "", "", self::ROLE_GROUP_ADMIN),
				"saveSolidsContainer"		=> new ActionMapping("saveSolidsContainer", "", "", self::ROLE_GROUP_ADMIN),
				"saveSVCollection"			=> new ActionMapping("saveSVCollection", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"saveInspectionWipeTest"	=> new ActionMapping("saveInspectionWipeTest", "", "", self::ROLE_GROUP_ADMIN),
				"saveInspectionWipe"		=> new ActionMapping("saveInspectionWipe", "", "", self::ROLE_GROUP_ALL_RAD_USERS),
				"saveInspectionWipes"		=> new ActionMapping("saveInspectionWipes", "", "", self::ROLE_GROUP_ALL_RAD_USERS),
				"saveParcelWipeTest"		=> new ActionMapping("saveParcelWipeTest", "", "", self::ROLE_GROUP_ADMIN),
				"saveParcelWipe"			=> new ActionMapping("saveParcelWipe", "", "", self::ROLE_GROUP_ADMIN),
				"saveParcelWipes"			=> new ActionMapping("saveParcelWipes", "", "", self::ROLE_GROUP_ADMIN),
                "savePIWipeTest"		=> new ActionMapping("savePIWipeTest", "", "", self::ROLE_GROUP_ALL_RAD_USERS),
				"savePIWipe"			=> new ActionMapping("savePIWipe", "", "", self::ROLE_GROUP_ALL_RAD_USERS),
				"savePIWipes"			=> new ActionMapping("savePIWipes", "", "", self::ROLE_GROUP_ALL_RAD_USERS),
				"saveParcelWipesAndChildren"=> new ActionMapping("saveParcelWipesAndChildren", "", "", self::ROLE_GROUP_ADMIN),
				"saveMiscellaneousWipeTest"	=> new ActionMapping("saveMiscellaneousWipeTest", "", "", self::ROLE_GROUP_ADMIN),
				"saveMiscellaneousWipe"		=> new ActionMapping("saveMiscellaneousWipe", "", "", self::ROLE_GROUP_ADMIN),
				"saveMiscellaneousWipes"	=> new ActionMapping("saveMiscellaneousWipes", "", "", self::ROLE_GROUP_ADMIN),
				"saveCarboyReadingAmount"	=> new ActionMapping("saveCarboyReadingAmount", "", "", self::ROLE_GROUP_ADMIN),
				"savePIAuthorization"	=> new ActionMapping("savePIAuthorization", "", "", self::ROLE_GROUP_ADMIN),
				"getAllPIAuthorizations"	=> new ActionMapping("getAllPIAuthorizations", "", "", self::ROLE_GROUP_ADMIN),
                "saveMiscellaneousWaste"	=> new ActionMapping("saveMiscellaneousWaste", "", "", self::ROLE_GROUP_ADMIN),
                "getAllMiscellaneousWaste"	=> new ActionMapping("getAllMiscellaneousWaste", "", "", self::ROLE_GROUP_ADMIN),
				"saveRadCondition"		    => new ActionMapping("saveRadCondition", "", "", self::ROLE_GROUP_ADMIN),

				"savePickupNotes"		    => new ActionMapping("savePickupNotes", "", "", self::ROLE_GROUP_ALL_RAD_USERS),

				"closeWasteContainer"       => new ActionMapping("closeWasteContainer", "", "", self::ROLE_GROUP_ALL_RAD_USERS),

				"removeContainerFromDrum"   => new ActionMapping("removeContainerFromDrum", "", "", self::ROLE_GROUP_ADMIN),
				"saveCarboyDisposalDetails"	=> new ActionMapping("saveCarboyDisposalDetails", "", "", self::ROLE_GROUP_ADMIN),
				"retireCarboy"	            => new ActionMapping("retireCarboy", "", "", self::ROLE_GROUP_ADMIN),
				"recirculateCarboy"	        => new ActionMapping("recirculateCarboy", "", "", self::ROLE_GROUP_ADMIN),

				// other functions
				"getParcelRemainder"			 => new ActionMapping("getParcelRemainder", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"disposeParcelRemainder" 	   	 => new ActionMapping("disposeParcelRemainder", "","", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getWasteAmountsByParcelId"		 => new ActionMapping("getWasteAmountsByParcelId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getParcelUseAmountByParcelUseId"=> new ActionMapping("getParcelUseWaste", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getTotalWasteFromPI"			 => new ActionMapping("getTotalWasteFromPI", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getWasteFromPISinceDate"        => new ActionMapping("getWastefRomPISinceDate", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getInventoriesByDateRanges"	 => new ActionMapping("getInventoriesByDateRanges", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),


				"createQuarterlyInventories"	 => new ActionMapping("createQuarterlyInventories", "", "", self::ROLE_GROUP_ADMIN),
				"getMostRecentInventory"	 => new ActionMapping("getMostRecentInventory", "", "", self::ROLE_GROUP_ALL_RAD_USERS),
                "getQuartleryInventoryById"	 => new ActionMapping("getQuartleryInventoryById", "", "", self::ROLE_GROUP_ALL_RAD_USERS),
				"getPiInventory"				 => new ActionMapping("getPiInventory", "", "", self::ROLE_GROUP_ALL_RAD_USERS),
				"getPIInventoryById"		 => new ActionMapping("getPIInventoryById", "", "", self::ROLE_GROUP_ALL_RAD_USERS),
				"getCurrentPIInventory"				 => new ActionMapping("getCurrentPIInventory", "", "", self::ROLE_GROUP_ALL_RAD_USERS),
				"getInventoriesByPiId"		=> new ActionMapping("getInventoriesByPiId","","", self::ROLE_GROUP_ALL_RAD_USERS),
				"savePIQuarterlyInventory"	=> new ActionMapping("savePIQuarterlyInventory","","", self::ROLE_GROUP_ALL_RAD_USERS),
				"updateParcelUse"	=> new ActionMapping("updateParcelUse","","", self::ROLE_GROUP_ADMIN),

                "getAllOtherWasteTypes"	=> new ActionMapping("getAllOtherWasteTypes","","", self::ROLE_GROUP_ALL_RAD_USERS),
                "getOtherWateTypeById"	=> new ActionMapping("getOtherWateTypeById","","", self::ROLE_GROUP_ALL_RAD_USERS),
                "saveOtherWasteType"	=> new ActionMapping("saveOtherWasteType","","", self::ROLE_GROUP_RSO),
                "clearOtherWaste"	    => new ActionMapping("clearOtherWaste","","", self::ROLE_GROUP_RSO),
                "assignOtherWasteType"	=> new ActionMapping("assignOtherWasteType","","", self::ROLE_GROUP_RSO),
                //        'OtherWasteContainer'  : { getById: "getOtherWasteContainerBiId"   , getAll: "getAllOtherWasteContainers"   , save: "saveOtherWasteContainer" },

                "getAllOtherWasteContainers"	=> new ActionMapping("getAllOtherWasteContainers","","", self::ROLE_GROUP_RSO),
                "getOtherWasteContainerBiId"	=> new ActionMapping("getOtherWasteContainerBiId","","", self::ROLE_GROUP_RSO),
                "saveOtherWasteContainer"	    => new ActionMapping("saveOtherWasteContainer","","", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "getTotalInventories"	        => new ActionMapping("getTotalInventories","","", self::ROLE_GROUP_ALL_RAD_USERS),


				"getRadInventoryReport"	=> new ActionMapping("getRadInventoryReport","","", self::ROLE_GROUP_ALL_RAD_USERS),
				"getRadModels"	        => new ActionMapping("getRadModels","","", self::ROLE_GROUP_ALL_RAD_USERS),
                "removeFromPickup" 	    => new ActionMapping("removeFromPickup", "", "", $this::$ROLE_GROUPS["EHS"] ),

		);

		if( ApplicationConfiguration::get("module.Radiation.zap.enabled", false) ){
			Logger::getLogger(__CLASS__)->trace("Zap Tool is Enabled");
			$radMappings["resetRadData"] = new ActionMapping("resetRadData","","", self::ROLE_GROUP_ADMIN);
		}

		return $radMappings;
	}
}
?>
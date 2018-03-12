<?php
/**
 * Class that wraps a static accessor that returns all Equipment Module Action Mappings
 *
 * @author Matt Breeden
 */
class Equipment_ActionMappingFactory extends ActionMappingFactory {

	public static function readActionConfig() {
		$mappings = new Equipment_ActionMappingFactory();

		return $mappings->getConfig();

	}

	public function getConfig() {
		return array(
                "getAllEquipmentInspections" 	=> new ActionMapping("getAllEquipmentInspections", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "getAllEquipmentPIs" 	        => new ActionMapping("getAllEquipmentPis", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "getAllEquipmentRooms" 	        => new ActionMapping("getAllEquipmentRooms", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),

                "getEquipmentInspectionById" 	=> new ActionMapping("getEquipmentInspectionById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "saveEquipmentInspection" 		=> new ActionMapping("saveEquipmentInspection", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllAutoclaves" 				=> new ActionMapping("getAllAutoclaves", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "getAutoclaveById" 				=> new ActionMapping("getAutoclaveById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "saveAutoclave" 				=> new ActionMapping("saveAutoclave", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllBioSafetyCabinets" 		=> new ActionMapping("getAllBioSafetyCabinets", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getBioSafetyCabinetById" 		=> new ActionMapping("getBioSafetyCabinetById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"saveBioSafetyCabinet" 			=> new ActionMapping("saveBioSafetyCabinet", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllChemFumeHoods" 			=> new ActionMapping("getAllChemFumeHoods", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "getChemFumeHoodById" 			=> new ActionMapping("getChemFumeHoodById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "saveChemFumeHood" 				=> new ActionMapping("saveChemFumeHood", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllLasers" 					=> new ActionMapping("getAllLasers", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "getLaserById" 					=> new ActionMapping("getLaserById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "saveLaser" 					=> new ActionMapping("saveLaser", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllXRays" 					=> new ActionMapping("getAllXRays", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "getXRayById" 					=> new ActionMapping("getXRayById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "saveXRay" 						=> new ActionMapping("saveXRay", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllBuildings" 				=> new ActionMapping("getBuidlingsWithoutRooms", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllRooms"	 				=> new ActionMapping("getRoomsWithoutComposing", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "uploadReportCertDocument"      => new ActionMapping("uploadReportCertDocument", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "uploadReportQuoteDocument"     => new ActionMapping("uploadReportQuoteDocument", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
                "uploadDeconDocument"           => new ActionMapping("uploadDeconDocument", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
		);
	}
}
?>

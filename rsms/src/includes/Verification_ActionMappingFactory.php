<?php

/**
 * Class that wraps a static accessor that returns all Radiation Safety Action Mappings
 *
 * @author Matt Breeden
 */
class Verification_ActionMappingFactory extends HazardInventoryActionMappingFactory {

	public static function readActionConfig() {
		$mappings = new Verification_ActionMappingFactory();

		return $mappings->getConfig();

	}

	/**
	 * Mapping for common groups of roles permitted to do an action
	 *
	 */

	protected static $ROLE_GROUPS = array(
			"ADMIN" 				=> array("Admin", "Radiation Admin"),
			"EHS"					=> array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector"),
			"EHS_AND_LAB"			=> array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector", "Lab Contact", "Principal Investigator", "Radiation User"),
			"ALL_RAD_USERS"			=> array("Admin", "Radiation Admin", "Safety User", "Radiation Inspector"),
			"LAB_PERSONNEL"			=> array("Lab Contact", "Principal Investigator", "Radiation User"),
			"EXCLUDE_READ_ONLY"		=> array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector", "Lab Contact", "Principal Investigator", "Radiation User")
	);

	public function __construct(){
	}
	/**
	 * Retrieves array of ActionMappings
	 *
	 * @return multitype:ActionMapping
	 */
	public function getConfig(){
		return array(

				//ANNUAL VERIFICATION
				"getPIForVerification"=>new ActionMapping("getPIForVerification", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"saveVerification"=>new ActionMapping("saveVerification", "", "", $this::$ROLE_GROUPS["EHS"]),
				"closeVerification"=>new ActionMapping("closeVerification", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getVerificationById"=>new ActionMapping("getVerificationById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "getHazardRoomDtosByPIId" =>new ActionMapping("getHazardRoomDtosByPIId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"savePendingUserChange"=>new ActionMapping("savePendingUserChange", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"savePendingRoomChange"=>new ActionMapping("savePendingRoomChange", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"savePendingHazardDtoChange"=>new ActionMapping("savePendingHazardDtoChange", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"removeHazardFromInventory"=>new ActionMapping("removeHazardFromInventory", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"confirmPendingUserChange"=>new ActionMapping("confirmPendingUserChange", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"confirmPendingRoomChange"=>new ActionMapping("confirmPendingRoomChange", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"confirmPendingHazardChange"=>new ActionMapping("confirmPendingHazardChange", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getVerificationsByYear"=>new ActionMapping("getVerificationsByYear", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"getVerificationYears"=>new ActionMapping("getVerificationYears", "", "", $this::$ROLE_GROUPS["ADMIN"])

		);
	}
}
?>

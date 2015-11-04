<?php

/**
 * Class that wraps a static accessor that returns all Hazard Inventory Action Mappings
 *
 * @author Matt Breeden
 */
class HazardInventoryMappingFactory extends ActionMappingFactory {

	public static function readActionConfig() {
		$mappings = new HazardInventoryMappingFactory();

		return $mappings->getConfig();

	}

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
				"savePendingUserChange"=>new ActionMapping("savePendingUserChange", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"savePendingRoomChange"=>new ActionMapping("savePendingRoomChange", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"savePendingHazardChange"=>new ActionMapping("savePendingHazardChange", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"confirmPendingUserChange"=>new ActionMapping("confirmPendingUserChange", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"confirmPendingRoomChange"=>new ActionMapping("confirmPendingRoomChange", "", "", $this::$ROLE_GROUPS["ADMIN"]),
				"confirmPendingHazardChange"=>new ActionMapping("confirmPendingHazardChange", "", "", $this::$ROLE_GROUPS["ADMIN"])
		);
	}
}
?>

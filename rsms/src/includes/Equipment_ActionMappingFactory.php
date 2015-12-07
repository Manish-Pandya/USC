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
				"getAllBioSafetyCabinets" 		=> new ActionMapping("getAllBioSafetyCabinets", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getBioSafetyCabinetById" 		=> new ActionMapping("getBioSafetyCabinetById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"saveBioSafetyCabinet" 			=> new ActionMapping("saveBioSafetyCabinet", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllBuildings" 				=> new ActionMapping("getBuidlingsWithoutRooms", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllRooms"	 				=> new ActionMapping("getRoomsWithoutComposing", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				
		);
	}
}
?>

<?php
/**
 * Class that wraps a static accessor that returns all Committees Module Action Mappings
 *
 * @author Matt Breeden
 */
class IBC_ActionMappingFactory extends ActionMappingFactory {

	public static function readActionConfig() {
		$mappings = new IBC_ActionMappingFactory();

		return $mappings->getConfig();

	}

	public function getConfig() {
		return array(
				"getAllProtocols" 				=> new ActionMapping("getAllProtocols", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getProtocolById" 				=> new ActionMapping("getProtocolById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"saveProtocol" 					=> new ActionMapping("saveProtocol", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllDepartments" 			=> new ActionMapping("getAllDepartments", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"getAllPIs"	 					=> new ActionMapping("getAllPIs", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				"uploadProtocolDocument"	 	=> new ActionMapping("uploadProtocolDocument", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] )
		);
	}
}
?>

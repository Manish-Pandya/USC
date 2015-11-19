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
				"getIsotopeById" 				=> new ActionMapping("getIsotopeById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"] ),
				
		);
	}
}
?>
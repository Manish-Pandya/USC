<?php
/**
 * Class that wraps a static accessor that returns all Radiation Safety Action Mappings
 * 
 * @author Perry
 */
class Rad_ActionMappingFactory extends ActionMappingFactory {
	
	public static function readActionConfig() {
		$mappings = new Rad_ActionMappingFactory();
		$mappings->LOG->DEBUG("inside Rad_ActionMappingdoohickey");
		
		return $mappings->getConfig();
		
	}
	public function getConfig() {
		return array(
			"getAllIsotopes"=>new ActionMapping("getAllIsotopes", "", "")
		);
	}
}
?>
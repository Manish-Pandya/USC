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
			"getIsotopeById" => new ActionMapping("getIsotopeById", "", ""),
			"getCarboyById" => new ActionMapping("getCarboyById", "", ""),
			"getCarboyUseCycleById" => new ActionMapping("getCarboyUseCycleById", "", ""),
			"getDisposalLotById" => new ActionMapping("getDisposalLotById", "", ""),
			"getDrumById" => new ActionMapping("getDrumById", "", ""),
			"getParcelById" => new ActionMapping("getParcelById", "", ""),
			"getParcelUseById" => new ActionMapping("getParcelUseById", "", "")
		);
	}
}
?>
<?php

/**
 * Class that wraps a static accessor that returns all Action Mappings
 * 
 * @author Mitch
 */
class ActionMappingFactory {
	
	/**
	 * Static accessor method to retrieve action mappings.
	 */
	public static function readActionConfig(){
		$mappings = new ActionMappingFactory();
		
		return $mappings->getConfig();
	}
	
	public function __construct(){ }
	
	/**
	 * Retrieves array of ActionMappings
	 * 
	 * @return multitype:ActionMapping
	 */
	public function getConfig(){
		return array(
				"savePI"=> new ActionMapping(
						"savePIAction",
						"PIHub.php",
						"PIHub.php",
						array("ADMIN")
				),
				
				//TODO
		);
	}
}
?>
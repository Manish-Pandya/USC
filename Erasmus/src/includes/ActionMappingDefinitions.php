<?php

/**
 * Class that wraps a static accessor that returns all Action Mappings
 * 
 * @author Mitch
 */
class ActionMappingDefinitions {
	//Define function to obtain array of Action Mappings
	public static function readActionConfig(){
		return array(
				"savePI"=> new ActionMapping(
						"savePIAction",
						"PIHub.php",
						"PIHub.php",
						"ADMIN"
				),
				
				//TODO
		);
	}
}
?>
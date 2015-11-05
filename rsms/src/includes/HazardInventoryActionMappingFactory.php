<?php

/**
 * Class that wraps a static accessor that returns all Hazard Inventory Action Mappings
 *
 * @author Matt Breeden
 */
class HazardInventoryActionMappingFactory extends ActionMappingFactory {

    public static function readActionConfig() {
        $mappings = new HazardInventoryActionMappingFactory();

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

                //HAZARD INVENTORY
                "getHazardRoomDtosByPIId" =>new ActionMapping("getHazardRoomDtosByPIId", "", "", $this::$ROLE_GROUPS["EHS"]),
                "savePIHazardRoomMappings"=>new ActionMapping("savePIHazardRoomMappings", "", "", $this::$ROLE_GROUPS["EHS"]),
        );
    }
}
?>

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

    public function getConfig(){
        return array(

                //HAZARD INVENTORY
                "getPIDetails" => new SecuredActionMapping("getPIDetails", $this::$ROLE_GROUPS["EHS"]),
                "getHazardRoomDtosByPIId" =>new ActionMapping("getHazardRoomDtosByPIId", "", "", $this::$ROLE_GROUPS["EHS"]),
                "savePIHazardRoomMappings"=>new ActionMapping("savePIHazardRoomMappings", "", "", $this::$ROLE_GROUPS["EHS"]),
        		"savePrincipalInvestigatorHazardRoomRelation"=>new ActionMapping("savePrincipalInvestigatorHazardRoomRelation", "", "", $this::$ROLE_GROUPS["EHS"]),
        		"getBuildingsByPIID"=>new ActionMapping("getBuildingsByPIID", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getPisByRoomIDs"=>new ActionMapping("getPisByRoomIDs", "", "", $this::$ROLE_GROUPS["EHS"]),
        		"getPisByHazardAndRoomIDs"=>new ActionMapping("getPisByHazardAndRoomIDs", "", "", $this::$ROLE_GROUPS["EHS"]),
        		"getCabinetsByPi"=>new ActionMapping("getCabinetsByPi", "", "", $this::$ROLE_GROUPS["EHS"]),

        );
    }
}
?>

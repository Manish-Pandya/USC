<?php

class LabInspection_ActionMappingFactory extends ActionMappingFactory {

	public static function readActionConfig() {
		$mappings = new LabInspection_ActionMappingFactory();

		return $mappings->getConfig();
    }

	public function getConfig() {
        return array(
            // Inspection, step 1 (PI / Room assessment)
            "initiateInspection"=>new ActionMapping("initiateInspection", "", "", $this::$ROLE_GROUPS["EHS"]),
            "saveInspectionRoomRelation"=>new ActionMapping("saveInspectionRoomRelation", "", "", $this::$ROLE_GROUPS["EHS"]),
            "saveInspection"=>new ActionMapping("saveInspection", "", "", $this::$ROLE_GROUPS["EHS"]),
            "submitCAP"=>new SecuredActionMapping("submitCAP", $this::$ROLE_GROUPS["EHS_AND_LAB"], 'LabInspectionSecurity::userCanSaveInspection'),
            "approveCAP"=>new SecuredActionMapping("approveCAP", $this::$ROLE_GROUPS["EHS"], 'LabInspectionSecurity::userCanSaveInspection'),

            "saveNoteForInspection"=>new ActionMapping("saveNoteForInspection", "", "", $this::$ROLE_GROUPS["EHS"]),
            "getSubHazards"=>new ActionMapping("getSubHazards", "", "", $this::$ROLE_GROUPS["EHS"]),
            "getOpenInspectionsByPIId"=>new ActionMapping("getOpenInspectionsByPIId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),

            "getRoomDtoByRoomId"=>new ActionMapping("getRoomDtoByRoomId", "", "", $this::$ROLE_GROUPS["EHS"]),
            "getRoomById"=>new ActionMapping("getRoomById", "", "", $this::$ROLE_GROUPS["EHS"]),
            "getHazardRoomRelations"=>new ActionMapping("getHazardRoomRelations", "", "", $this::$ROLE_GROUPS["EHS"]),
            "getDepartmentById"=>new ActionMapping("getDepartmentById", "", "", $this::$ROLE_GROUPS["EHS"]),
            "getAllDepartments"=>new ActionMapping("getAllDepartments", "", "", $this::$ROLE_GROUPS["EHS"]),
            "getAllActiveDepartments"=>new ActionMapping("getAllActiveDepartments", "", "", $this::$ROLE_GROUPS["EHS"]),
            "getAllBuildings"=>new ActionMapping("getAllBuildings", "", ""),
            "getAllCampuses"=>new ActionMapping("getAllCampuses", "", ""),
            "getBuildingById"=>new ActionMapping("getBuildingById", "", ""),
            "saveRoom"=>new ActionMapping("saveRoom", "", "", $this::$ROLE_GROUPS["ADMIN"]),
            "saveBuilding"=>new ActionMapping("saveBuilding", "", "", $this::$ROLE_GROUPS["ADMIN"]),
            "saveCampus"=>new ActionMapping("saveCampus", "", "", $this::$ROLE_GROUPS["ADMIN"]),
            "getLocationCSV"=>new ActionMapping("getLocationCSV", "", "", $this::$ROLE_GROUPS["EHS"]),


            // Inspection, step 2 (Hazard Assessment)

            "getHazardRoomDtosByPIId"=>new ActionMapping("getHazardRoomDtosByPIId", "", "", $this::$ROLE_GROUPS["EHS"]),
            "getHazardRoomMappingsAsTree"=>new ActionMapping("getHazardRoomMappingsAsTree", "", "", $this::$ROLE_GROUPS["EHS"]),
            "getHazardsInRoom"=>new ActionMapping("getHazardsInRoom", "", "", $this::$ROLE_GROUPS["EHS"]),
            "saveHazardRoomRelations"=>new ActionMapping("saveHazardRoomRelations", "", "", $this::$ROLE_GROUPS["EHS"]),
            "saveHazardRelation"=>new ActionMapping("saveHazardRelation", "", "", $this::$ROLE_GROUPS["EHS"]),
            "resetInspectionRooms"=>new ActionMapping("resetInspectionRooms", "", "", $this::$ROLE_GROUPS["EHS"]),
            "getPiForHazardInventory"=>new ActionMapping("getPiForHazardInventory", "", "", $this::$ROLE_GROUPS["EHS"]),

            // Inspection, step 3 (Checklist)
            "resetChecklists" => new SecuredActionMapping(
                "resetChecklists",
                $this::$ROLE_GROUPS["EHS_AND_LAB"],
                'LabInspectionSecurity::userCanViewInspection'),

            "getDeficiencyById"=>new ActionMapping("getDeficiencyById", "", "", $this::$ROLE_GROUPS["EHS"]),
            "saveResponse"=>new ActionMapping("saveResponse", "", "", $this::$ROLE_GROUPS["EHS"]),
            "removeResponse"=>new ActionMapping("removeResponse", "", "",  $this::$ROLE_GROUPS["EHS"],"200","404"),
            "saveDeficiencySelection"=>new ActionMapping("saveDeficiencySelection", "", "", $this::$ROLE_GROUPS["EHS"]),
            "saveOtherDeficiencySelection"=>new ActionMapping("saveOtherDeficiencySelection", "", "", $this::$ROLE_GROUPS["EHS"]),
            "removeDeficiencySelection"=>new ActionMapping("removeDeficiencySelection", "", "", $this::$ROLE_GROUPS["EHS"]),
            "addCorrectedInInspection"=>new ActionMapping("addCorrectedInInspection", "", "", $this::$ROLE_GROUPS["EHS"]),
            "removeCorrectedInInspection"=>new ActionMapping("removeCorrectedInInspection", "", "", $this::$ROLE_GROUPS["EHS"]),
            "saveCorrectiveAction"=>new SecuredActionMapping("saveCorrectiveAction", $this::$ROLE_GROUPS["EHS_AND_LAB"], 'LabInspectionSecurity::userCanSaveCorrectiveAction'),
            "deleteCorrectiveActionFromDeficiency"=>new SecuredActionMapping("deleteCorrectiveActionFromDeficiency", $this::$ROLE_GROUPS["EHS_AND_LAB"], 'LabInspectionSecurity::userCanDeleteCorrectiveAction'),
            "saveObservationRelation"=>new ActionMapping("saveObservationRelation", "", "", $this::$ROLE_GROUPS["EHS"]),
            "saveRecommendationRelation"=>new ActionMapping("saveRecommendationRelation", "", "", $this::$ROLE_GROUPS["EHS"]),
            "saveSupplementalObservation"=>new ActionMapping("saveSupplementalObservation", "", "", $this::$ROLE_GROUPS["EHS"]),
            "saveSupplementalRecommendation"=>new ActionMapping("saveSupplementalRecommendation", "", "", $this::$ROLE_GROUPS["EHS"]),
            "saveSupplementalDeficiency"=>new ActionMapping("saveSupplementalDeficiency", "", "", $this::$ROLE_GROUPS["EHS"]),
            "getChecklistsForInspection"=>new ActionMapping("getChecklistsForInspection", "", "", $this::$ROLE_GROUPS["EHS"]),
            "getInspectionsByPIId" => new SecuredActionMapping("getInspectionsByPIId", $this::$ROLE_GROUPS["EHS_AND_LAB"], 'LabInspectionSecurity::userCanViewPI'),
            "getArchivedInspectionsByPIId"=>new ActionMapping("getArchivedInspectionsByPIId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
            "getDeficiencySelectionByInspectionIdAndDeficiencyId"=>new ActionMapping("getDeficiencySelectionByInspectionIdAndDeficiencyId", "", "", $this::$ROLE_GROUPS["EHS"]),


            // Inspection, step 4 (Review, deficiency report)
            "getDeficiencySelectionsForResponse"=>new ActionMapping("getDeficiencySelectionsForResponse", "", "",$this::$ROLE_GROUPS["EXCLUDE_READ_ONLY"] ),
            "getRecommendationsForResponse"=>new ActionMapping("getRecommendationsForResponse", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
            "getObservationsForResponse"=>new ActionMapping("getObservationsForResponse", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
            "getObservationById"=>new ActionMapping("getObservationById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),

            // Inspection, step 5 (Details, Full Report)
            "getResponsesForInspection"=>new ActionMapping("getResponsesForInspection", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
            "sendInspectionEmail"=>new ActionMapping("sendInspectionEmail", "", "", $this::$ROLE_GROUPS["EHS"]),

            "getInspectionById" => new SecuredActionMapping(
                "getInspectionById",
                $this::$ROLE_GROUPS["EHS_AND_LAB"],
                'LabInspectionSecurity::userCanViewInspection'),

            "getResponseById"=>new ActionMapping("getResponseById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),

            "getInspectionReportEmail"=>new ActionMapping("getInspectionReportEmail", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"])
        );
    }
}
?>
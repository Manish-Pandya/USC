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
                //TODO: Correct action names
                //TODO: Assign locations
                //TODO: Assign roles
                //TODO: Assign response codes        		
                "loginAction"=>new ActionMapping("loginAction", WEB_ROOT."views/RSMSCenter.php", WEB_ROOT."login.php"),        		
                "logoutAction"=>new ActionMapping("logoutAction", WEB_ROOT."../login.php", WEB_ROOT."../login.php"),
        		
                //Generic
                "activate"=>new ActionMapping("activate", "", ""),
                "deactivate"=>new ActionMapping("deactivate", "", ""),
                "getCurrentUser"=>new ActionMapping("getCurrentUser", "", ""),
                "getCurrentUserRoles"=>new ActionMapping("getCurrentUserRoles", "", ""),

                // Users Hub
                "getAllUsers"=>new ActionMapping("getAllUsers", "", "", array("Admin", "Radiation Admin")),
                "getUserById"=>new ActionMapping("getUserById", "", ""),
                "saveUser"=>new ActionMapping("saveUser", "", "", "", array("Admin", "Radiation Admin")),
                "getAllRoles"=>new ActionMapping("getAllRoles", "", ""),
                "saveUserRoleRelation"=>new ActionMapping("saveUserRoleRelation", "", "", array("Admin", "Radiation Admin")),
                "saveUserRoleRelations"=>new ActionMapping("saveUserRoleRelations", "", "", array("Admin", "Radiation Admin")),
                "lookupUser"=>new ActionMapping("lookupUser", "", "", array("Admin", "Radiation Admin")),
                "saveInspector"=>new ActionMapping("saveInspector", "", "", array("Admin", "Radiation Admin")),
                "getSupervisorByUserId"=>new ActionMapping("getSupervisorByUserId", "", "", array("Admin", "Radiation Admin")),
                "getPIByUserId"=>new ActionMapping("getPIByUserId", "", ""),
                "getUsersForUserHub"=>new ActionMapping("getUsersForUserHub", "", "", array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector")),



                //convenience method to split all usernames into first and last names
                "makeFancyNames"=>new ActionMapping("makeFancyNames", "", ""),



                // PI Hub
                "getAllPIs"=>new ActionMapping("getAllPIs", "", ""),
                "getPisForUserHub"=>new ActionMapping("getPisForUserHub", "", "", array("Admin", "Radiation Admin")),
                "getRoomsByPIId"=>new ActionMapping("getRoomsByPIId", "", ""),
                "getPIById"=>new ActionMapping("getPIById", "", ""),
                "savePIRoomRelation"=>new ActionMapping("savePIRoomRelation", "", "", array("Admin", "Radiation Admin")),
                "savePIContactRelation"=>new ActionMapping("savePIContactRelation", "", "", array("Admin", "Radiation Admin")),
                "savePIDepartmentRelation"=>new ActionMapping("savePIDepartmentRelation", "", "", array("Admin", "Radiation Admin")),
                "savePIDepartmentRelations"=>new ActionMapping("savePIDepartmentRelations", "", "", array("Admin", "Radiation Admin")),
                "savePI"=>new ActionMapping("savePI", "", "", array("Admin", "Radiation Admin")),
                "getAllPrincipalInvestigatorRoomRelations"=>new ActionMapping("getAllPrincipalInvestigatorRoomRelations", "", "", array("Admin", "Radiation Admin")),

                // Checklist Hub
                "getChecklistById"=>new ActionMapping("getChecklistById", "", ""),
                "getChecklistByHazardId"=>new ActionMapping("getChecklistByHazardId", "", ""),
                "getAllQuestions"=>new ActionMapping("getAllQuestions", "", ""),
                "saveChecklist"=>new ActionMapping("saveChecklist", "", "", array("Admin", "Radiation Admin")),
                "saveQuestion"=>new ActionMapping("saveQuestion", "", "", array("Admin", "Radiation Admin")),
                "swapQuestions"=>new ActionMapping("swapQuestions", "", "", array("Admin", "Radiation Admin")),
                "setMasterHazardsForAllChecklists"=>new ActionMapping("setMasterHazardsForAllChecklists", "", "", array("Admin", "Radiation Admin")),

                // Hazards Hub
                "getAllHazards"=>new ActionMapping("getAllHazards", "", ""),
                "getAllHazardsAsTree"=>new ActionMapping("getAllHazardsAsTree", "", ""),
                "getHazardTreeNode"=>new ActionMapping("getHazardTreeNode", "", ""),
                "getHazardById"=>new ActionMapping("getHazardById", "", ""),
                "moveHazardToParent"=>new ActionMapping("moveHazardToParent", "", "", array("Admin", "Radiation Admin")),
                "saveHazard"=>new ActionMapping("saveHazard", "", "", array("Admin", "Radiation Admin")),
                "saveHazardWithoutReturningSubHazards"=>new ActionMapping("saveHazardWithoutReturningSubHazards", "", "", array("Admin", "Radiation Admin")),
                "createOrderIndicesForHazards"=>new ActionMapping("createOrderIndicesForHazards", "", "", array("Admin", "Radiation Admin")),
                "setOrderIndicesForSubHazards"=>new ActionMapping("setOrderIndicesForSubHazards", "", "", array("Admin", "Radiation Admin")),
                "reorderHazards"=>new ActionMapping("reorderHazards", "", "", array("Admin", "Radiation Admin")),

                // Question Hub
                "getQuestionById"=>new ActionMapping("getQuestionById", "", ""),
                "saveQuestionRelation"=>new ActionMapping("saveQuestionRelation", "", ""),
                "saveDeficiencyRelation"=>new ActionMapping("saveDeficiencyRelation", "", "", array("Admin", "Radiation Admin")),
                "saveRecommendationRelation"=>new ActionMapping("saveRecommendationRelation", "", "", array("Admin", "Radiation Admin")),
                "saveDeficiency"=>new ActionMapping("saveDeficiency", "", "", array("Admin", "Radiation Admin")),
                "saveRecommendation"=>new ActionMapping("saveRecommendation", "", "", array("Admin", "Radiation Admin")),
                "saveObservation"=>new ActionMapping("saveObservation", "", "", array("Admin", "Radiation Admin")),

                "getInspector"=>new ActionMapping("getInspector", "", ""),
                "getAllInspectors"=>new ActionMapping("getAllInspectors", "", ""),
                "getAllPIs"=>new ActionMapping("getAllPIs", "", ""),

                // Department Hub
                "saveDepartment"=>new ActionMapping("saveDepartment", "", "", array("Admin", "Radiation Admin")),

                // Inspection, step 1 (PI / Room assessment)
                "getAllRooms"=>new ActionMapping("getAllRooms", "", ""),
                "initiateInspection"=>new ActionMapping("initiateInspection", "", "", array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector")),
                "saveInspectionRoomRelation"=>new ActionMapping("saveInspectionRoomRelation", "", "", array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector")),
                "saveInspection"=>new ActionMapping("saveInspection", "", "", array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector")),
                "saveNoteForInspection"=>new ActionMapping("saveNoteForInspection", "", "", array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector")),
                "getSubHazards"=>new ActionMapping("getSubHazards", "", "", array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector")),
                "getOpenInspectionsByPIId"=>new ActionMapping("getOpenInspectionsByPIId", "", "", array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector")),

                "getRoomDtoByRoomId"=>new ActionMapping("getRoomDtoByRoomId", "", ""),
                "getRoomById"=>new ActionMapping("getRoomById", "", ""),
                "getHazardRoomRelations"=>new ActionMapping("getHazardRoomRelations", "", ""),
                "getDepartmentById"=>new ActionMapping("getDepartmentById", "", ""),
                "getAllDepartments"=>new ActionMapping("getAllDepartments", "", ""),
                "getAllActiveDepartments"=>new ActionMapping("getAllActiveDepartments", "", ""),
                "getAllBuildings"=>new ActionMapping("getAllBuildings", "", ""),
                "getAllCampuses"=>new ActionMapping("getAllCampuses", "", ""),
                "getBuildingById"=>new ActionMapping("getBuildingById", "", ""),
                "saveRoom"=>new ActionMapping("saveRoom", "", "", array("Admin", "Radiation Admin")),
                "saveBuilding"=>new ActionMapping("saveBuilding", "", "", array("Admin", "Radiation Admin")),
                "saveCampus"=>new ActionMapping("saveCampus", "", "", array("Admin", "Radiation Admin")),
                "getLocationCSV"=>new ActionMapping("getLocationCSV", "", "", array("Admin", "Radiation Admin")),


                // Inspection, step 2 (Hazard Assessment)
                "getHazardRoomMappingsAsTree"=>new ActionMapping("getHazardRoomMappingsAsTree", "", ""),
                "getHazardsInRoom"=>new ActionMapping("getHazardsInRoom", "", ""),
                "saveHazardRoomRelations"=>new ActionMapping("saveHazardRoomRelations", "", ""),
                "saveHazardRelation"=>new ActionMapping("saveHazardRelation", "", ""),
                "resetInspectionRooms"=>new ActionMapping("resetInspectionRooms", "", ""),

                // Inspection, step 3 (Checklist)
                "resetChecklists"=>new ActionMapping("resetChecklists","",""),
                "getDeficiencyById"=>new ActionMapping("getDeficiencyById", "", ""),
                "saveResponse"=>new ActionMapping("saveResponse", "", "", array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector")),
                "removeResponse"=>new ActionMapping("removeResponse", "", "","","200","404"),
                "saveDeficiencySelection"=>new ActionMapping("saveDeficiencySelection", "", ""),
                "removeDeficiencySelection"=>new ActionMapping("removeDeficiencySelection", "", ""),
                "addCorrectedInInspection"=>new ActionMapping("addCorrectedInInspection", "", ""),
                "removeCorrectedInInspection"=>new ActionMapping("removeCorrectedInInspection", "", ""),
                "saveCorrectiveAction"=>new ActionMapping("saveCorrectiveAction", "", ""),
                "saveObservationRelation"=>new ActionMapping("saveObservationRelation", "", ""),
                "saveRecommendationRelation"=>new ActionMapping("saveRecommendationRelation", "", ""),
                "saveSupplementalObservation"=>new ActionMapping("saveSupplementalObservation", "", ""),
                "saveSupplementalRecommendation"=>new ActionMapping("saveSupplementalRecommendation", "", ""),
                "getChecklistsForInspection"=>new ActionMapping("getChecklistsForInspection", "", ""),
                "getInspectionsByPIId"=>new ActionMapping("getInspectionsByPIId", "", ""),
                "getDeficiencySelectionByInspectionIdAndDeficiencyId"=>new ActionMapping("getDeficiencySelectionByInspectionIdAndDeficiencyId", "", ""),


                // Inspection, step 4 (Review, deficiency report)
                "getDeficiencySelectionsForResponse"=>new ActionMapping("getDeficiencySelectionsForResponse", "", ""),
                "getRecommendationsForResponse"=>new ActionMapping("getRecommendationsForResponse", "", ""),
                "getObservationsForResponse"=>new ActionMapping("getObservationsForResponse", "", ""),
                "getObservationById"=>new ActionMapping("getObservationById", "", ""),

                // Inspection, step 5 (Details, Full Report)
                "getResponsesForInspection"=>new ActionMapping("getResponsesForInspection", "", ""),
                "sendInspectionEmail"=>new ActionMapping("sendInspectionEmail", "", ""),

                "getInspectionById"=>new ActionMapping("getInspectionById", "", ""),
                "getResponseById"=>new ActionMapping("getResponseById", "", ""),

                // EMERGENCY INFO HUB
                "getPIsByRoomId"=>new ActionMapping("getPIsByRoomId", "", ""),
                "getRoomsByBuildingId"=>new ActionMapping("getRoomsByBuildingId", "", ""),

                //INSPECTION MANAGEMENT HUB
                "getCurrentYear"=>new ActionMapping("getCurrentYear", "", ""),
                "getInspectionSchedule"=>new ActionMapping("getInspectionSchedule", "", ""),
                "scheduleInspection"=>new ActionMapping("scheduleInspection", "", ""),


                "getAllLabLocations"=>new ActionMapping("getAllLabLocations", "", ""),
                "getAllSupplementalObservations"=>new ActionMapping("getAllSupplementalObservations", "", "")
        );
    }
}
?>

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

    /**
     * Mapping for common groups of roles permitted to do an action
     *
     */

    protected static $ROLE_GROUPS = array(
            "ADMIN" 				=> array("Admin", "Radiation Admin"),
            "EHS"					=> array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector"),
            "EHS_AND_LAB"			=> array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector", "Lab Contact", "Principal Investigator", "Radiation User"),
            "ALL_RAD_USERS"			=> array("Admin", "Radiation Admin", "Safety User", "Radiation Inspector"),
            "LAB_PERSONNEL"			=> array("Lab Contact", "Principal Investigator", "Radiation User"),
            "EXCLUDE_READ_ONLY"		=> array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector", "Lab Contact", "Principal Investigator", "Radiation User")
    );

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
                "loginAction"=>new ActionMapping("loginAction", WEB_ROOT."views/RSMSCenter.php", WEB_ROOT."login.php", array(), false),
                "logoutAction"=>new ActionMapping("logoutAction", WEB_ROOT."../login.php", WEB_ROOT."../login.php", array(), false),

                //Generic
                "activate"=>new ActionMapping("activate", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "deactivate"=>new ActionMapping("deactivate", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getCurrentUser"=>new ActionMapping("getCurrentUser", "", ""),
                "getCurrentUserRoles"=>new ActionMapping("getCurrentUserRoles", "", ""),

                // Users Hub
                "getAllUsers"=>new ActionMapping("getAllUsers", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getUserById"=>new ActionMapping("getUserById", "", ""),
                "saveUser"=>new ActionMapping("saveUser", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getAllRoles"=>new ActionMapping("getAllRoles", "", ""),
                "saveUserRoleRelation"=>new ActionMapping("saveUserRoleRelation", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveUserRoleRelations"=>new ActionMapping("saveUserRoleRelations", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "lookupUser"=>new ActionMapping("lookupUser", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveInspector"=>new ActionMapping("saveInspector", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getSupervisorByUserId"=>new ActionMapping("getSupervisorByUserId", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getPIByUserId"=>new ActionMapping("getPIByUserId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "getUsersForUserHub"=>new ActionMapping("getUsersForUserHub", "", "", $this::$ROLE_GROUPS["EHS"]),



                //convenience method to split all usernames into first and last names
                "makeFancyNames"=>new ActionMapping("makeFancyNames", "", "",$this::$ROLE_GROUPS["ADMIN"]),



                // PI Hub
                "getAllPIs"=>new ActionMapping("getAllPIs", "", "", array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector", "Emergency Account")),
                "getPisForUserHub"=>new ActionMapping("getPisForUserHub", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getRoomsByPIId"=>new ActionMapping("getRoomsByPIId", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getPIById"=>new ActionMapping("getPIById", "", ""),
                "savePIRoomRelation"=>new ActionMapping("savePIRoomRelation", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "savePIContactRelation"=>new ActionMapping("savePIContactRelation", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "savePIDepartmentRelation"=>new ActionMapping("savePIDepartmentRelation", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "savePIDepartmentRelations"=>new ActionMapping("savePIDepartmentRelations", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "savePI"=>new ActionMapping("savePI", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getAllPrincipalInvestigatorRoomRelations"=>new ActionMapping("getAllPrincipalInvestigatorRoomRelations", "", "", $this::$ROLE_GROUPS["ADMIN"]),

                // Checklist Hub
                "getChecklistById"=>new ActionMapping("getChecklistById", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getChecklistByHazardId"=>new ActionMapping("getChecklistByHazardId", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getAllQuestions"=>new ActionMapping("getAllQuestions", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveChecklist"=>new ActionMapping("saveChecklist", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveQuestion"=>new ActionMapping("saveQuestion", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "swapQuestions"=>new ActionMapping("swapQuestions", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "setMasterHazardsForAllChecklists"=>new ActionMapping("setMasterHazardsForAllChecklists", "", "", $this::$ROLE_GROUPS["ADMIN"]),

                // Hazards Hub
                "getAllHazards"=>new ActionMapping("getAllHazards", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getAllHazardsAsTree"=>new ActionMapping("getAllHazardsAsTree", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getHazardTreeNode"=>new ActionMapping("getHazardTreeNode", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getHazardById"=>new ActionMapping("getHazardById", "", "", $this::$ROLE_GROUPS["EHS"]),
                "moveHazardToParent"=>new ActionMapping("moveHazardToParent", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveHazard"=>new ActionMapping("saveHazard", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveHazardWithoutReturningSubHazards"=>new ActionMapping("saveHazardWithoutReturningSubHazards", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "createOrderIndicesForHazards"=>new ActionMapping("createOrderIndicesForHazards", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "setOrderIndicesForSubHazards"=>new ActionMapping("setOrderIndicesForSubHazards", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "reorderHazards"=>new ActionMapping("reorderHazards", "", "", $this::$ROLE_GROUPS["ADMIN"]),

                // Question Hub
                "getQuestionById"=>new ActionMapping("getQuestionById", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveQuestionRelation"=>new ActionMapping("saveQuestionRelation", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveDeficiencyRelation"=>new ActionMapping("saveDeficiencyRelation", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveRecommendationRelation"=>new ActionMapping("saveRecommendationRelation", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveDeficiency"=>new ActionMapping("saveDeficiency", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveRecommendation"=>new ActionMapping("saveRecommendation", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveObservation"=>new ActionMapping("saveObservation", "", "", $this::$ROLE_GROUPS["ADMIN"]),

                "getInspector"=>new ActionMapping("getInspector", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getAllInspectors"=>new ActionMapping("getAllInspectors", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getAllPIs"=>new ActionMapping("getAllPIs", "", ""),

                // Department Hub
                "saveDepartment"=>new ActionMapping("saveDepartment", "", "", $this::$ROLE_GROUPS["ADMIN"]),

                // Inspection, step 1 (PI / Room assessment)
                "getAllRooms"=>new ActionMapping("getAllRooms", "", "", $this::$ROLE_GROUPS["EHS"]),
                "initiateInspection"=>new ActionMapping("initiateInspection", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveInspectionRoomRelation"=>new ActionMapping("saveInspectionRoomRelation", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveInspection"=>new ActionMapping("saveInspection", "", "", $this::$ROLE_GROUPS["EHS"]),
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
                "saveRoom"=>new ActionMapping("saveRoom", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveBuilding"=>new ActionMapping("saveBuilding", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveCampus"=>new ActionMapping("saveCampus", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getLocationCSV"=>new ActionMapping("getLocationCSV", "", "", $this::$ROLE_GROUPS["EHS"]),


                // Inspection, step 2 (Hazard Assessment)
                "getHazardRoomMappingsAsTree"=>new ActionMapping("getHazardRoomMappingsAsTree", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getHazardsInRoom"=>new ActionMapping("getHazardsInRoom", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveHazardRoomRelations"=>new ActionMapping("saveHazardRoomRelations", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveHazardRelation"=>new ActionMapping("saveHazardRelation", "", "", $this::$ROLE_GROUPS["EHS"]),
                "resetInspectionRooms"=>new ActionMapping("resetInspectionRooms", "", "", $this::$ROLE_GROUPS["EHS"]),

                // Inspection, step 3 (Checklist)
                "resetChecklists"=>new ActionMapping("resetChecklists","","", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "getDeficiencyById"=>new ActionMapping("getDeficiencyById", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveResponse"=>new ActionMapping("saveResponse", "", "", $this::$ROLE_GROUPS["EHS"]),
                "removeResponse"=>new ActionMapping("removeResponse", "", "",  $this::$ROLE_GROUPS["EHS"],"200","404"),
                "saveDeficiencySelection"=>new ActionMapping("saveDeficiencySelection", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveOtherDeficiencySelection"=>new ActionMapping("saveOtherDeficiencySelection", "", "", $this::$ROLE_GROUPS["EHS"]),
                "removeDeficiencySelection"=>new ActionMapping("removeDeficiencySelection", "", "", $this::$ROLE_GROUPS["EHS"]),
                "addCorrectedInInspection"=>new ActionMapping("addCorrectedInInspection", "", "", $this::$ROLE_GROUPS["EHS"]),
                "removeCorrectedInInspection"=>new ActionMapping("removeCorrectedInInspection", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveCorrectiveAction"=>new ActionMapping("saveCorrectiveAction", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveObservationRelation"=>new ActionMapping("saveObservationRelation", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveRecommendationRelation"=>new ActionMapping("saveRecommendationRelation", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveSupplementalObservation"=>new ActionMapping("saveSupplementalObservation", "", "", $this::$ROLE_GROUPS["EHS"]),
                "saveSupplementalRecommendation"=>new ActionMapping("saveSupplementalRecommendation", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getChecklistsForInspection"=>new ActionMapping("getChecklistsForInspection", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getInspectionsByPIId"=>new ActionMapping("getInspectionsByPIId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
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

                "getInspectionById"=>new ActionMapping("getInspectionById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "getResponseById"=>new ActionMapping("getResponseById", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),

                // EMERGENCY INFO HUB
                "getPIsByRoomId"=>new ActionMapping("getPIsByRoomId", "", ""),
                "getRoomsByBuildingId"=>new ActionMapping("getRoomsByBuildingId", "", ""),

                //INSPECTION MANAGEMENT HUB
                "getCurrentYear"=>new ActionMapping("getCurrentYear", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getInspectionSchedule"=>new ActionMapping("getInspectionSchedule", "", "", $this::$ROLE_GROUPS["EHS"]),
                "scheduleInspection"=>new ActionMapping("scheduleInspection", "", "", $this::$ROLE_GROUPS["ADMIN"]),


                "getAllLabLocations"=>new ActionMapping("getAllLabLocations", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getAllSupplementalObservations"=>new ActionMapping("getAllSupplementalObservations", "", "", $this::$ROLE_GROUPS["EHS"]),

                // GENERIC
                "getPIsByClassInstanceId"=>new ActionMapping("getPIsByClassInstanceId", "", "")
        );
    }
}
?>

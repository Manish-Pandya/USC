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
            "RADMIN" 				=> array("Radiation Admin"),
            "IBC_COMMITTEE" 		=> array("Admin", "Radiation Admin", "IBC Member"),
            "IBC_AND_LAB" 		    => array("Admin", "Radiation Admin", "IBC Member", "Principal Investigator", "Lab Contact"),
            "EHS"					=> array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector"),
            "EHS_AND_LAB"			=> array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector", "Lab Contact", "Lab Personnel", "Principal Investigator", "Radiation User"),
            "ALL_RAD_USERS"			=> array("Admin", "Radiation Admin", "Safety User", "Radiation Inspector", "Principal Investigator"),
            "RSO"			        => array("Admin", "Radiation Admin", "Radiation Inspector"),
            "LAB_PERSONNEL"			=> array("Lab Contact", "Principal Investigator", "Radiation User"),
            "EXCLUDE_READ_ONLY"		=> array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector", "Lab Contact", "Principal Investigator", "Radiation User"),

            "REPORTS_ALL"           => array("Admin", "Department Chair")
    );

    public function __construct(){
    }

    /**
     * Retrieves array of ActionMappings
     *
     * @return array<string,ActionMapping>
     */
    public function getConfig(){
        $mappings = array(
                //TODO: Correct action names
                //TODO: Assign locations
                //TODO: Assign roles
                //TODO: Assign response codes
                "loginAction"=>new ActionMapping("loginAction", "views/RSMSCenter.php", LOGIN_PAGE, array(), false),
                "logoutAction"=>new ActionMapping("logoutAction",LOGIN_PAGE, LOGIN_PAGE, array(), false),
				"getCurrentRoles"=>new ActionMapping("getCurrentRoles","", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getPropertyByName"=>new ActionMapping("getPropertyByName","", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getCurrentRoles"=>new ActionMapping("getCurrentRoles","", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
				"getPropertyByName"=>new ActionMapping("getPropertyByName","", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),

                //Generic
                "activate"=>new ActionMapping("activate", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "deactivate"=>new ActionMapping("deactivate", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getCurrentUser"=>new ActionMapping("getCurrentUser", "", ""),
                "getCurrentUserRoles"=>new ActionMapping("getCurrentUserRoles", "", ""),
        		"getRelationships"=>new ActionMapping("getRelationships", "", "", $this::$ROLE_GROUPS["ADMIN"]),

                // Users Hub
                "getAllUsers"=>new ActionMapping("getAllUsers", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getUserById"=>new ActionMapping("getUserById", "", ""),
                "saveUser"=>new ActionMapping("saveUser", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "assignLabUserToPI"=>new SecuredActionMapping("assignLabUserToPI", $this::$ROLE_GROUPS["ADMIN"]),
                "unassignLabUser"=>new SecuredActionMapping("unassignLabUser", $this::$ROLE_GROUPS["ADMIN"]),
                "getAllRoles"=>new ActionMapping("getAllRoles", "", ""),
                "saveUserRoleRelation"=>new ActionMapping("saveUserRoleRelation", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveUserRoleRelations"=>new ActionMapping("saveUserRoleRelations", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "lookupUser"=>new ActionMapping("lookupUser", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "saveInspector"=>new ActionMapping("saveInspector", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getSupervisorByUserId"=>new ActionMapping("getSupervisorByUserId", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getPIByUserId"=>new ActionMapping("getPIByUserId", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "getUsersForUserHub"=>new ActionMapping("getUsersForUserHub", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getRoomHasHazards"=>new ActionMapping("getRoomHasHazards", "", "",$this::$ROLE_GROUPS["EHS"]),



                //convenience method to split all usernames into first and last names
                "makeFancyNames"=>new ActionMapping("makeFancyNames", "", "",$this::$ROLE_GROUPS["ADMIN"]),
                "setHazardTypes"=>new ActionMapping("setHazardTypes", "", "",$this::$ROLE_GROUPS["ADMIN"]),


                // PI Hub
                "getAllBuildingNames"=>new ActionMapping("getAllBuildingNames", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getAllBuildingRoomNames"=>new ActionMapping("getAllBuildingRoomNames", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getAllPINames"=>new ActionMapping("getAllPINames", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getUsersForPIHub"=>new ActionMapping("getUsersForPIHub", "", "", $this::$ROLE_GROUPS["ADMIN"]),

                "getAllPIDetails"=>new SecuredActionMapping("getAllPIDetails", array("Admin", "Radiation Admin", "Safety Inspector", "Radiation Inspector", "Emergency Account")),
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
        		"setMasterHazardIds"=>new ActionMapping("setMasterHazardIds", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getPisAndRoomsByHazard"=>new ActionMapping("getPisAndRoomsByHazard", "", "",$this::$ROLE_GROUPS["EHS"]),



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

                // Department Hub
                "saveDepartment"=>new ActionMapping("saveDepartment", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "getAllDepartmentsWithCounts"=>new ActionMapping("getAllDepartmentsWithCounts", "", "", $this::$ROLE_GROUPS["ADMIN"]),

                //
                "getAllRooms"=>new ActionMapping("getAllRooms", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getAllRoomDetails"=>new SecuredActionMapping("getAllRoomDetails", $this::$ROLE_GROUPS["EHS"]),

                // EMERGENCY INFO HUB
                "getPIsByRoomId"=>new ActionMapping("getPIsByRoomId", "", ""),
                "getRoomsByBuildingId"=>new ActionMapping("getRoomsByBuildingId", "", ""),

                //INSPECTION MANAGEMENT HUB
                "getCurrentYear"=>new ActionMapping("getCurrentYear", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getInspectionSchedule"=>new ActionMapping("getInspectionSchedule", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getInspectionsByYear"=>new ActionMapping("getInspectionsByYear", "", "", $this::$ROLE_GROUPS["EHS"]),


                "scheduleInspection"=>new ActionMapping("scheduleInspection", "", "", $this::$ROLE_GROUPS["ADMIN"]),


                "getAllLabLocations"=>new ActionMapping("getAllLabLocations", "", "", $this::$ROLE_GROUPS["EHS"]),
                "getAllSupplementalObservations"=>new ActionMapping("getAllSupplementalObservations", "", "", $this::$ROLE_GROUPS["EHS"]),


                //MY LABORATORY
                "getMyLab"=>new SecuredActionMapping("getMyLab", $this::$ROLE_GROUPS["EHS_AND_LAB"], 'LabInspectionSecurity::userCanViewPI'),
                "getMyLabWidgets" => new SecuredActionMapping("getMyLabWidgets", $this::$ROLE_GROUPS["EHS_AND_LAB"]),

                //ANNUAL VERIFICATION
                "saveVerification"=>new ActionMapping("saveVerification", "", "", $this::$ROLE_GROUPS["EHS"]),
                "closeVerification"=>new ActionMapping("closeVerification", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "savePendingUserChange"=>new ActionMapping("savePendingUserChange", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "savePendingRoomChange"=>new ActionMapping("savePendingRoomChange", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "savePendingHazardChange"=>new ActionMapping("savePendingHazardChange", "", "", $this::$ROLE_GROUPS["EHS_AND_LAB"]),
                "confirmPendingUserChange"=>new ActionMapping("confirmPendingUserChange", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "confirmPendingRoomChange"=>new ActionMapping("confirmPendingRoomChange", "", "", $this::$ROLE_GROUPS["ADMIN"]),
                "confirmPendingHazardChange"=>new ActionMapping("confirmPendingHazardChange", "", "", $this::$ROLE_GROUPS["ADMIN"]),

                // GENERIC
                "getPIsByClassInstance"=>new ActionMapping("getPIsByClassInstance", "", ""),
        		"prepareRedirect"=>new ActionMapping("prepareRedirect", "", ""),
        		"sendTestEmail"=>new ActionMapping("sendTestEmail", "", "")

        );

        // Only include Impersonation mappings if the feature is enabled
        if( ApplicationConfiguration::get("module.Core.feature.impersonation", false) ){
            $mappings["impersonateUserAction"] = new ActionMapping("impersonateUserAction", "", "", array("Admin"));
            $mappings["getImpersonatableUsernames"] = new ActionMapping("getImpersonatableUsernames", "", "", array("Admin"));
            $mappings["stopImpersonating"] = new ActionMapping("stopImpersonating", LOGIN_PAGE, LOGIN_PAGE, array(), false);
        }

        return $mappings;
    }
}
?>

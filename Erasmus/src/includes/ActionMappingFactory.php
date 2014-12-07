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
				"loginAction"=>new ActionMapping("loginAction", "RSMSCenter.php", "login.php"),
				"logoutAction"=>new ActionMapping("logoutAction", "login.php", "login.php"),

				//Generic
				"activate"=>new ActionMapping("activate", "", ""),
				"deactivate"=>new ActionMapping("deactivate", "", ""),

				// Users Hub
				"getAllUsers"=>new ActionMapping("getAllUsers", "", ""),
				"getUserById"=>new ActionMapping("getUserById", "", ""),
				"saveUser"=>new ActionMapping("saveUser", "", "", ""),
				"getAllRoles"=>new ActionMapping("getAllRoles", "", ""),
				"saveUserRoleRelation"=>new ActionMapping("saveUserRoleRelation", "", ""),
				"lookupUser"=>new ActionMapping("lookupUser", "", ""),
				"saveInspector"=>new ActionMapping("saveInspector", "", ""),
				"getSupervisorByUserId"=>new ActionMapping("getSupervisorByUserId", "", ""),

				//convenience method to split all usernames into first and last names
				"makeFancyNames"=>new ActionMapping("makeFancyNames", "", ""),



				// PI Hub
				"getAllPIs"=>new ActionMapping("getAllPIs", "", ""),
				"getRoomsByPI"=>new ActionMapping("getRoomsByPI", "", ""),
				"getPIById"=>new ActionMapping("getPIById", "", ""),
				"savePIRoomRelation"=>new ActionMapping("savePIRoomRelation", "", ""),
				"savePIContactRelation"=>new ActionMapping("savePIContactRelation", "", ""),
				"savePIDepartmentRelation"=>new ActionMapping("savePIDepartmentRelation", "", ""),
				"savePI"=>new ActionMapping("savePI", "", ""),

				// Checklist Hub
				"getChecklistById"=>new ActionMapping("getChecklistById", "", ""),
				"getChecklistByHazardId"=>new ActionMapping("getChecklistByHazardId", "", ""),
				"getAllQuestions"=>new ActionMapping("getAllQuestions", "", ""),
				"saveChecklist"=>new ActionMapping("saveChecklist", "", ""),
				"saveQuestion"=>new ActionMapping("saveQuestion", "", ""),
				"setMasterHazardsForAllChecklists"=>new ActionMapping("setMasterHazardsForAllChecklists", "", ""),

				// Hazards Hub
				"getAllHazards"=>new ActionMapping("getAllHazards", "", ""),
				"getAllHazardsAsTree"=>new ActionMapping("getAllHazardsAsTree", "", ""),
				"getHazardTreeNode"=>new ActionMapping("getHazardTreeNode", "", ""),
				"getHazardById"=>new ActionMapping("getHazardById", "", ""),
				"moveHazardToParent"=>new ActionMapping("moveHazardToParent", "", ""),
				"saveHazard"=>new ActionMapping("saveHazard", "", ""),
				"createOrderIndicesForHazards"=>new ActionMapping("createOrderIndicesForHazards", "", ""),
				"setOrderIndicesForSubHazards"=>new ActionMapping("setOrderIndicesForSubHazards", "", ""),
				"reorderHazards"=>new ActionMapping("reorderHazards", "", ""),

				// Question Hub
				"getQuestionById"=>new ActionMapping("getQuestionById", "", ""),
				"saveQuestionRelation"=>new ActionMapping("saveQuestionRelation", "", ""),
				"saveDeficiencyRelation"=>new ActionMapping("saveDeficiencyRelation", "", ""),
				"saveRecommendationRelation"=>new ActionMapping("saveRecommendationRelation", "", ""),
				"saveDeficiency"=>new ActionMapping("saveDeficiency", "", ""),
				"saveRecommendation"=>new ActionMapping("saveRecommendation", "", ""),
				"saveObservation"=>new ActionMapping("saveObservation", "", ""),

				"getInspector"=>new ActionMapping("getInspector", "", ""),
				"getAllInspectors"=>new ActionMapping("getAllInspectors", "", ""),
				"getAllPIs"=>new ActionMapping("getAllPIs", "", ""),

				// Department Hub
				"saveDepartment"=>new ActionMapping("saveDepartment", "", ""),

				// Inspection, step 1 (PI / Room assessment)
				"getAllRooms"=>new ActionMapping("getAllRooms", "", ""),
				"initiateInspection"=>new ActionMapping("initiateInspection", "", ""),
				"saveInspectionRoomRelation"=>new ActionMapping("saveInspectionRoomRelation", "", ""),
				"saveInspection"=>new ActionMapping("saveInspection", "", ""),
				"saveNoteForInspection"=>new ActionMapping("saveNoteForInspection", "", ""),
				"getSubHazards"=>new ActionMapping("getSubHazards", "", ""),

				"getRoomDtoByRoomId"=>new ActionMapping("getRoomDtoByRoomId", "", ""),
				"getRoomById"=>new ActionMapping("getRoomById", "", ""),
				"getHazardRoomRelations"=>new ActionMapping("getHazardRoomRelations", "", ""),
				"getDepartmentById"=>new ActionMapping("getDepartmentById", "", ""),
				"getAllDepartments"=>new ActionMapping("getAllDepartments", "", ""),
				"getAllActiveDepartments"=>new ActionMapping("getAllActiveDepartments", "", ""),
				"getAllBuildings"=>new ActionMapping("getAllBuildings", "", ""),
				"getAllCampuses"=>new ActionMapping("getAllCampuses", "", ""),
				"getBuildingById"=>new ActionMapping("getBuildingById", "", ""),
				"saveRoom"=>new ActionMapping("saveRoom", "", ""),
				"saveBuilding"=>new ActionMapping("saveBuilding", "", ""),
				"saveCampus"=>new ActionMapping("saveCampus", "", ""),

				// Inspection, step 2 (Hazard Assessment)
				"getHazardRoomMappingsAsTree"=>new ActionMapping("getHazardRoomMappingsAsTree", "", ""),
				"getHazardsInRoom"=>new ActionMapping("getHazardsInRoom", "", ""),
				"saveHazardRoomRelations"=>new ActionMapping("saveHazardRoomRelations", "", ""),
				"saveHazardRelation"=>new ActionMapping("saveHazardRelation", "", ""),
				"resetInspectionRooms"=>new ActionMapping("resetInspectionRooms", "", ""),

				// Inspection, step 3 (Checklist)
				"resetChecklists"=>new ActionMapping("resetChecklists","",""),
				"getDeficiencyById"=>new ActionMapping("getDeficiencyById", "", ""),
				"saveResponse"=>new ActionMapping("saveResponse", "", ""),
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

		);
	}
}
?>
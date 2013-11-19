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
				"saveUser"=>new ActionMapping("saveUser", "", ""),
				"getAllRoles"=>new ActionMapping("getAllRoles", "", ""),
				
				// Checklist Hub
				"getChecklist"=>new ActionMapping("getChecklist", "", ""),
				"getQuestions"=>new ActionMapping("getQuestions", "", ""),
				"saveChecklist"=>new ActionMapping("saveChecklist", "", ""),
				"saveQuestion"=>new ActionMapping("saveQuestion", "", ""),
				
				// Hazards Hub
				"getAllHazards"=>new ActionMapping("getAllHazards", "", ""),
				"getHazardById"=>new ActionMapping("getHazardById", "", ""),
				"moveHazardToParent"=>new ActionMapping("moveHazardToParent", "", ""),
				"saveHazard"=>new ActionMapping("saveHazard", "", ""),
				
				// Question Hub
				"getQuestionById"=>new ActionMapping("getQuestionById", "", ""),
				"saveQuestionRelation"=>new ActionMapping("saveQuestionRelation", "", ""),
				"saveDeficiencyRelation"=>new ActionMapping("saveDeficiencyRelation", "", ""),
				"saveRecommendationRelation"=>new ActionMapping("saveRecommendationRelation", "", ""),
				
				// Inspection, step 1 (PI / Room assessment)
				"getPI"=>new ActionMapping("getPI", "", ""),
				"getRooms"=>new ActionMapping("getRooms", "", ""),
				"saveInspection"=>new ActionMapping("saveInspection", "", ""),
				
				// Inspection, step 2 (Hazard Assessment)
				"getHazardsInRoom"=>new ActionMapping("getHazardsInRoom", "", ""),
				"saveHazardRelation"=>new ActionMapping("saveHazardRelation", "", ""),
				"saveRoomRelation"=>new ActionMapping("saveRoomRelation", "", ""),
				
				// Inspection, step 3 (Checklist)
				"getDeficiencyById"=>new ActionMapping("getDeficiencyById", "", ""),
				"saveResponse"=>new ActionMapping("saveResponse", "", ""),
				"saveDeficiencySelection"=>new ActionMapping("saveDeficiencySelection", "", ""),
				"saveRootCause"=>new ActionMapping("saveRootCause", "", ""),
				"saveCorrectiveAction"=>new ActionMapping("saveCorrectiveAction", "", ""),
				
				// Inspection, step 4 (Review, deficiency report)
				"getDeficiencySelectionsForResponse"=>new ActionMapping("getDeficiencySelectionsForResponse", "", ""),
				"getRecommendationsForResponse"=>new ActionMapping("getRecommendationsForResponse", "", ""),
				
				// Inspection, step 5 (Details, Full Report)
				"getResponsesForInspection"=>new ActionMapping("getResponsesForInspection", "", ""),
				
				"getInspectionById"=>new ActionMapping("getInspectionById", "", ""),
				"getResponseById"=>new ActionMapping("getResponseById", "", ""),
		);
	}
}
?>
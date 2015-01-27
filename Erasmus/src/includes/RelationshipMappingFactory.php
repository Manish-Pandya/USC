<?php

/**
 * Class that wraps a static accessor that returns all Relationship Mappings
 * 
 * @author perry
 */
class RelationshipMappingFactory {

	public function getMap() {
		return array(
			new RelationMapping("DeficiencySelection"  , "CorrectiveAction"   , "deficiency_selection_corrective_action", "DeficiencySelectionCorrectiveActionRelation"),
			new RelationMapping("DeficiencySelection"  , "Room"               , "deficiency_selection_room"             , "DeficiencySelectionRoomRelation"),
			new RelationMapping("DeficiencySelection"  , "DeficiencyRootCause", "deficiency_selection_root_cause"       , "DeficiencySelectionRootCauseRelation"),
			new RelationMapping("Hazard"               , "Checklist"          , "hazard_checklist"                      , "HazardChecklistRelation"),
			new RelationMapping("Hazard"			   , "Room" 			  , "hazard_room"  						    , "HazardRoomRelation"),
			new RelationMapping("Checklist"			   , "Inspection"	      , "inspection_checklist"				    , "InspectionChecklistRelation"),
			new RelationMapping("Inspector"			   , "Inspection"		  , "inspection_inspector"				    , "InspectionInspectorRelation"),
			new RelationMapping("Response" 			   , "Inspection"		  , "inspection_response"					, "InspectionResponseRelation"),
			new RelationMapping("Room"				   , "Inspection"		  , "inspection_room"						, "InspectionRoomRelation"),
			new RelationMapping("PrincipalInvestigator", "Department"		  , "principal_investigator_department"  	, "PIDepartmentRelation"   ),
			new RelationMapping("PrincipalInvestigator", "Room"				  , "principal_investigator_room"			, "PIRoomRelation"),
			new RelationMapping("Response"			   , "Observation"		  , "response_observation"					, "ResponseObservationRelation"),
			new RelationMapping("Response"			   , "Recommendation"	  , "response_recommendation"				, "ResponseRecommendationRelation"),
			new RelationMapping("User"				   , "Role"				  , "user_role"								, "UserRoleRelation")
		);
	}
	
	/**
	 * Given two class names, find the name of the table that contains their
	 * Many-to-Many relationship, if any.
	 * 
	 * @param string $classA
	 * @param string $classB
	 * 
	 * @return string name of associated table
	 */
	public function getTableName($classA, $classB) {
		$relationships = $this->getMap();
		
		foreach($relationships as $relation) {
			if( $relation->isPresent($classA, $classB) ) {
				return $relation->getTableName();
			}
		}
		
		// no match found, return error
		return new ActionError("No relationship found between " . $classA . " and " . $classB);
	}

	/**
	 * Given two class names, find the name of the DTO class to contain their
	 * Many-to-Many relationship, if any.
	 *
	 * @param string $classA
	 * @param string $classB
	 *
	 * @return string name of associated table
	 */
	public function getClassName($classA, $classB) {
		$relationships = $this->getMap();
	
		foreach($relationships as $relation) {
			if( $relation->isPresent($classA, $classB) ) {
				return $relation->getClassName();
			}
		}
	
		// no match found, return error
		return new ActionError("No relationship found between " . $classA . " and " . $classB);
	}
}
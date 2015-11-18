<?php

/**
 * Class that wraps a static accessor that returns all Relationship Mappings
 * 
 * @author perry
 */
class RelationshipMappingFactory {

	public function getMap() {
		return array(
			new RelationMapping("DeficiencySelection"  , "CorrectiveAction"   , "deficiency_selection_corrective_action", "deficiency_selection_id"   , "corrective_action_id"),
			new RelationMapping("DeficiencySelection"  , "Room"               , "deficiency_selection_room"             , "deficiency_selection_id"   , "room_id"             ),
			new RelationMapping("Checklist"			   , "Inspection"	      , "inspection_checklist"				    , "inspection_id"             , "checklist_id"        ),
			new RelationMapping("Inspector"			   , "Inspection"		  , "inspection_inspector"				    , "inspection_id"             , "inspector_id"        ),
			new RelationMapping("Room"				   , "Inspection"		  , "inspection_room"						, "inspection_id"             , "room_id"             ),
			new RelationMapping("PrincipalInvestigator", "Department"		  , "principal_investigator_department"  	, "principal_investigator_id" , "department_id"       ),
			new RelationMapping("PrincipalInvestigator", "Room"				  , "principal_investigator_room"			, "principal_investigator_id" , "room_id"             ),
			new RelationMapping("Response"			   , "Observation"		  , "response_observation"					, "response_id"               , "observation_id"      ),
			new RelationMapping("Response"			   , "Recommendation"	  , "response_recommendation"				, "response_id"               , "recommendation_id"   ),
			new RelationMapping("User"				   , "Role"				  , "user_role"								, "user_id"                   , "role_id"             ),
			new RelationMapping("PIAuthorization"	   , "Room"				  , "pi_authorization_room"		            , "pi_authorization_id"       , "room_id"             ),
			new RelationMapping("PIAuthorization"	   , "Department"		  , "pi_authorization_department"  	  		, "pi_authorization_id"       , "department_id"       )
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
	public function getRelationship($classA, $classB) {
		$relationships = $this->getMap();
		
		foreach($relationships as $relation) {
			if( $relation->isPresent($classA, $classB) ) {
				return $relation;
			}
		}
		
		// no match found, return error
		return new ActionError("No relationship found between " . $classA . " and " . $classB);
	}

}
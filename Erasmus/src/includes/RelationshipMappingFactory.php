<?php

/**
 * Class that wraps a static accessor that returns all Relationship Mappings
 * 
 * @author perry
 */
class RelationshipMappingFactory {

	public function getMap() {
		return array(
			new RelationMapping("DeficiencySelection"  , "CorrectiveAction"   , "deficiency_selection_corrective_action"),
			new RelationMapping("DeficiencySelection"  , "Room"               , "deficiency_selection_room"             ),
			new RelationMapping("DeficiencySelection"  , "DeficiencyRootCause", "deficiency_selection_root_cause"       ),
			new RelationMapping("Hazard"               , "Checklist"          , "hazard_checklist"                      ),
			new RelationMapping("Hazard"			   , "Room" 			  , "hazard_room"  						    ),
			new RelationMapping("Checklist"			   , "Inspection"	      , "inspection_checklist"				    ),
			new RelationMapping("Inspector"			   , "Inspection"		  , "inspection_inspector"				    ),
			new RelationMapping("Response" 			   , "Inspection"		  , "inspection_response"					),
			new RelationMapping("Room"				   , "Inspection"		  , "inspection_room"						),
			new RelationMapping("PrincipalInvestigator", "User"				  , "pi_lab_personnel"						),
			new RelationMapping("PrincipalInvestigator", "Department"		  , "principal_investigator_department"     ),
			new RelationMapping("PrincipalInvestigator", "Room"				  , "principal_investigator_room"			),
			new RelationMapping("Response"			   , "Observation"		  , "response_observation"					),
			new RelationMapping("Response"			   , "Recommendation"	  , "respibse_recommendation"				),
			new RelationMapping("User"				   , "Role"				  , "user_role"								)
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
}
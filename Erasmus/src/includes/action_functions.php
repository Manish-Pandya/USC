<?php 
/*
 * This file is responsible for providing functions for Action calls,
 * and should not execute any code upon inclusion.
 * 
 * Because this file merely provides the functions, they are easily testable
 */
?><?php
//TODO: Split these functions up into further includes?
function loginAction(){ };
function logoutAction(){ };

// Users Hub
function getAllUsers(){
	$LOG = Logger::getLogger( 'Action:getAllUsers' );
	$allUsers = array();
	
	for( $i = 0; $i < 10; $i++ ){
		$user = new User();
		$user->setIsActive(TRUE);
		$user->setEmail("user$i@host.com");
		$user->setKeyId($i);
		$user->setName("User #$i");
		$user->setUsername("user$i");
		
		$LOG->info("Defined User: $user");
		
		$allUsers[] = $user;
	}
	
	return $allUsers;
};

function saveUser(){ };
function activateUser(){ };
function deactivateUser(){ };
function getAllRoles(){ };

// Checklist Hub
function getChecklist(){ };
function getQuestions(){ };
function saveChecklist(){ };
function saveQuestion(){ };

// Hazards Hub
function getHazards(){ };
function saveHazards(){ };
//function saveChecklist(){ };	//DUPLICATE FUNCTION

// Question Hub
function getQuestion(){ };
function saveQuestionRelation(){ };
function saveDeficiencyRelation(){ };
function saveRecommendationRelation(){ };

// Inspection, step 1 (PI / Room assessment)
function getPI(){ };
function getRooms(){ };
function saveInspection(){ };

// Inspection, step 2 (Hazard Assessment)
function getHazardsInRoom(){ };
function saveHazardRelation(){ };
function saveRoomRelation(){ };

// Inspection, step 3 (Checklist)
//function getQuestions(){ };	//DUPLICATE FUNCTION
function getDeficiency(){ };
function saveResponse(){ };
function saveDeficiencySelection(){ };
function saveRootCause(){ };
function saveCorrectiveAction(){ };

// Inspection, step 4 (Review, deficiency report)
function getDeficiencySelections(){ };
function getRecommendations(){ };

// Inspection, step 5 (Details, Full Report)
function getResponses(){ };
?>
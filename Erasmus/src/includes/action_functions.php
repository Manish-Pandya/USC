<?php 
/*
 * This file is responsible for providing functions for Action calls,
 * and should not execute any code upon inclusion.
 * 
 * Because this file merely provides the functions, they are easily testable
 * 
 * If an error should occur, Action functions should return either NULL or
 * an instance of ActionError. Returning an ActionError allows the function
 * to provide additional information about the error.
 */
?><?php
//TODO: Split these functions up into further includes?
function loginAction(){ };
function logoutAction(){ };

// Users Hub
function getAllUsers(){
	$LOG = Logger::getLogger( 'Action:getAllUsers' );
	$allUsers = array();
	
	//TODO: Query for Users
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

function getUserById(){
	if( array_key_exists( 'id', $_REQUEST)){
		$keyid = $_REQUEST['id'];
	
		//TODO: query for User with this ID
		$user = new User();
		$user->setIsActive(TRUE);
		$user->setEmail("user$keyid@host.com");
		$user->setName("User #$keyid");
		$user->setUsername("user$keyid");
		$user->setKeyId($keyid);
	
		return $user;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
}

function saveUser(){
	$LOG = Logger::getLogger('Action:saveUser');
	
	//read user JSON from input stream
	$stuff = file_get_contents('php://input');
	
	$LOG->info( 'Read from input stream: ' . $stuff );
	
	//TODO: verify that $stuff is actual data
	if( !empty( $stuff) ){	
		//convert to User object
		$userObject = JsonManager::decode($stuff, new User());
		
		$LOG->info( 'Converted to User: ' . $userObject);
		
		$userObject->setKeyId( 54321 );
		
		return $userObject;
	}
	else{
		return new ActionError('No data read from input stream');
	}
};

function activateUser(){ };
function deactivateUser(){ };

function getAllRoles(){
	return array(
		'Administrator',
	);
};

// Checklist Hub
function getChecklist(){
	if( array_key_exists( 'id', $_REQUEST)){
		$keyid = $_REQUEST['id'];
	
		//TODO: query for Checklist with this ID
		$checklist = new Checklist();
		$checklist->setKeyId($keyid);
	
		return $checklist;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function getQuestions(){ };
function saveChecklist(){ };
function saveQuestion(){ };

// Hazards Hub
function getHazards(){ };
function saveHazards(){ };
//function saveChecklist(){ };	//DUPLICATE FUNCTION

// Question Hub
function getQuestion(){
	if( array_key_exists( 'id', $_REQUEST)){
		$keyid = $_REQUEST['id'];
	
		//TODO: query for Question with this ID
		$question = new Question();
		$question->setKeyId($keyid);
		$question->setText('What?');
		$question->setStandardsAndGuidelines('Because.');
	
		return $question;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function saveQuestionRelation(){ };
function saveDeficiencyRelation(){ };
function saveRecommendationRelation(){ };

// Inspection, step 1 (PI / Room assessment)
function getPI(){
	if( array_key_exists( 'id', $_REQUEST)){
		$keyid = $_REQUEST['id'];
		
		//TODO: query for PI with this ID
		$pi = new PrincipalInvestigator();
		$pi->setKeyId($keyid);
		
		return $pi;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

function getRooms(){ };
function saveInspection(){ };

// Inspection, step 2 (Hazard Assessment)
function getHazardsInRoom(){
	if( array_key_exists( 'id', $_REQUEST)){
		$roomId = $_REQUEST['id'];
		
		//TODO: get Room
		$room = new Room();
		$room->setKeyId($roomId);
		$room->setName("Room $roomId");
		$hazards = array();
		
		for( $i = 0; $i < 5; $i++ ){
			$hazard = new Hazard();
			$hazard->setRooms( array($room) );
			$hazard->setKeyId($i);
			$hazard->setName("Dangerous thing #$i");
			
			$hazards[] = $hazard;
		}
	
		return $hazards;
	}
	else{
		//error
		return new ActionError("No request parameter 'id' was provided");
	}
};

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
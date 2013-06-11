<?php

//Setup logging, autoload, etc
require_once( dirname(__FILE__) . '/Application.php');

$sessionDataSource = array();

//Set default action. BECAUSE, THAT'S WHY!
$actionName = "login";

// Check that there is a SESSION object
if( isset( $_SESSION ) ){
	// Clear our session params
	unset($_SESSION['success']);
	unset($_SESSION['output']);
	unset($_SESSION["errors"]);
	
	//Get name of requested action
	$actionName = $_POST["action"];
	
	$sessionDataSource = $_SESSION;
}

//TODO: additional setup?

// Create Dispatcher (based on $_SESSION)
$actionDispatcher = new ActionDispatcher($sessionDataSource);

// Attempt to dispatch to the requested action
$destinationPage = $actionDispatcher->dispatch($actionName);

// Send to the proper URL
header("location: " . $destinationPage);

//Action functions
//TODO: Include these from other files?

function loginAction(){ };
function logoutAction(){ };

// Users Hub
function getAllUsers(){ };
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
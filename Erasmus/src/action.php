<?php

//Setup logging, autoload, etc
require_once( dirname(__FILE__) . '/Application.php');

// Clear our session params
unset($_SESSION['success']);
unset($_SESSION['output']);
unset($_SESSION["errors"]);

//TODO: additional setup?

//Get name of requested action
$actionName = $_POST["action"];

// Create Dispatcher (based on $_SESSION)
$actionDispatcher = new ActionDispatcher($_SESSION);

// Attempt to dispatch to the requested action
$destinationPage = $actionDispatcher->dispatch($actionName);

// Send to the proper URL
header("location: " . $destinationPage);

//Action functions

////////////////////////////////////////////////////////////////////////////////
//
// 	USER AUTHENTICATION AND AUTHORIZATION
//
////////////////////////////////////////////////////////////////////////////////

//Check session for Admin flag
function isAdminUser(){
	//TODO
}

function securityCheck(){
	//TODO
}

function login($username,$password) {
	//TODO
}

function logout() {
	session_destroy();
	return true;
}

//////////////////////////////////////////////////

function createNewPI(){
	
	//TODO: Get PI information
	//	From session? JSON?
	
	$pi_input = new PrincipalInvestigator();
	
	$validationmanager = new ValidationManager();
	$validator = $validationmanager->getValidator($pi_input);
	
	if( $validator->ValidateForm() ) {
		//TODO: Save PI
		
		//Return successfully
		return true;
	}
	else{
		//TODO: Process errors
		
		//Return failure
		return false;
	}
}

function savePI(){
	
}

?>
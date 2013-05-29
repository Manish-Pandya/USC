<?php

//Setup logging, autoload, etc
require_once( dirname(__FILE__) . '/Application.php');

//TODO: additional setup?

// Begin Dispatcher
$actionDispatcher = new ActionDispatcher($_SESSION);
$actionName = $_POST["action"];
$destinationPage = $actionDispatcher->dispatch($actionName);
// End Dispatcher

//TODO: Forward to $destinationPage

//Action functions

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
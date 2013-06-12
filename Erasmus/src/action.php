<?php
/*
 * This script is responsible for parsing the incoming request/session
 * dispatching the requested action, and forwarding to the appropriate
 * destination.
 */ 
?><?php

//Setup logging, autoload, etc
require_once( dirname(__FILE__) . '/Application.php');

$sessionDataSource = array();

//Set default action to login
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

//TODO: If this is called via AJAX, we probably don't want to forward the location.
// Send to the proper URL
header("location: " . $destinationPage);

//Action functions
// Include these from other file(s)
require_once( dirname(__FILE__) . '/includes/action_functions.php' );

?>
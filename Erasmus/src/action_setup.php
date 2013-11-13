<?php
/*
 * This script is responsible for parsing the incoming request/session
 * dispatching the requested action, and forwarding to the appropriate
 * destination.
 */ 
?><?php

//Setup logging, autoload, etc
require_once( dirname(__FILE__) . '/Application.php');

//Action functions
// Include these from other file(s)
require_once( dirname(__FILE__) . '/includes/action_functions.php' );

$sessionDataSource = array();

//Set default action to login
$actionName = "login";

//FIXME: Validate $_SESSION
// Check that there is a SESSION object
//if( isset( $_SESSION ) ){
	// Clear our session params
	//unset($_SESSION['success']);
	//unset($_SESSION['output']);
	//unset($_SESSION["errors"]);
	
	//Get name of requested action
	$actionName = $_REQUEST["action"];
	
	//$sessionDataSource = $_SESSION;
	$sessionDataSource = $_REQUEST;
//}

//TODO: additional setup?
?>
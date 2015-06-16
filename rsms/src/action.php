<?php
/*
 * This script is responsible for parsing the incoming request/session
 * dispatching the requested action, and forwarding to the appropriate
 * destination.
 * 
 * The request is treated as a standard, synchronous request.
 */ 
?><?php

//Setup basic action data
//	$sessionDataSource and $actionName are defined here
session_start();
require_once( dirname(__FILE__) . '/action_setup.php');

// Create Dispatcher (based on $_REQUEST)
$actionDispatcher = new ActionDispatcher($sessionDataSource);

// Attempt to dispatch to the requested action
$actionResult = $actionDispatcher->dispatch($actionName);
session_start();
//TODO: set $actionResult->actionFunctionResult to session? should action function do this?
if ($actionResult->statusCode != 200){
	header ("HTTP/1.1 " . $actionResult->statusCode . " Action Error");
}
// Send to the proper URL
header("location: $actionResult->destinationPage");

?>
<?php
/*
 * This script is responsible for parsing the incoming request/session
 * dispatching the requested action, and forwarding to the appropriate
 * destination.
 * 
 * The request is treated as an AJAX request whose action result will
 * be displayed
 */ 
?><?php

//Setup basic action data
//	$sessionDataSource and $actionName are defined here
require_once( dirname(__FILE__) . '/action_setup.php');

// Create Dispatcher (based on $_SESSION)
$actionDispatcher = new ActionDispatcher($sessionDataSource);

// Attempt to dispatch to the requested action
$actionResult = $actionDispatcher->dispatch($actionName);

//TODO: option to encode JSON or not?

// JSON-Encode result
$json = JsonManager::encode($actionResult->actionFunctionResult);

// Echo request-param 'callback'
//TODO: Check for callback existence?
echo $_GET["callback"];

// Output JSON
echo $json;
?>
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
session_start();

// Create Dispatcher (based on $_SESSION)
$sessionSource = $_SESSION;

$actionDispatcher = new ActionDispatcher($dataSource, $sessionSource);

// Attempt to dispatch to the requested action
$actionResult = $actionDispatcher->dispatch($actionName);

//TODO: option to encode JSON or not?

// JSON-Encode result
$json = JsonManager::encode($actionResult->actionFunctionResult);
$LOG = Logger::getLogger('json manager result');
//$LOG->debug($json);
$output = $json;

//If a callback function is requested
if( array_key_exists('callback', $_GET) ){
	// Echo request-param 'callback' as function
	$output = $_GET['callback'] . "($json)";
}

// begin output
// TODO: Will we ever need to use a different header?
header('Access-Control-Allow-Origin: *');
header('content-type: application/javascript');

// Set the HTTP status code. ActionResult defaults this to 200
//set_http_response_code( $actionResult->statusCode );
http_response_code( $actionResult->statusCode );
//http_response_code(404);

// Output JSON (with possible callback)
echo $output;
?>
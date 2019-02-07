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

RequestLog::init($actionName, $dataSource);
$LOG = Logger::getLogger('ajaxaction.' . $actionName);

RequestLog::log_start();

// Create Dispatcher (based on $_SESSION)
$sessionSource = $_SESSION;

// Write standard headers before calling action.
//   Some actions (such as attachment download) may trigger sending
//     of headers.
header('Access-Control-Allow-Origin: *');
header('content-type: application/javascript');

$actionDispatcher = new ActionDispatcher($dataSource, $sessionSource);

// Attempt to dispatch to the requested action
$dispatchId = Metrics::start("Dispatch action " . RequestLog::describe());
$actionResult = $actionDispatcher->dispatch($actionName);
Metrics::stop($dispatchId);

//TODO: option to encode JSON or not?

// Extract overrides from request, if any
$entityMappingOverrides = JsonManager::extractEntityMapOverrides($dataSource);

// JSON-Encode result
// Time how long it takes to encode this
$jsonifyId = Metrics::start("Encode response " . RequestLog::describe());
$json = JsonManager::encode($actionResult->actionFunctionResult, $entityMappingOverrides);
Metrics::stop($jsonifyId);

//if the user is not logged in or does not have permissions, the client will redirect to the login page.  prepare a message
if($actionResult->statusCode == 401){
    $LOG->fatal('User is not authenticated');
    $_SESSION['LOGGED_OUT'] = "You have been logged out of the system.  Please log in again to continue";
}else{
    $_SESSION['LOGGED_OUT'] = NULL;
}

// begin output

// Set the HTTP status code. ActionResult defaults this to 200
//set_http_response_code( $actionResult->statusCode );
http_response_code( $actionResult->statusCode );
if($actionResult->statusCode == 302){
    header("location:" . LOGIN_PAGE);
}

//http_response_code(404);

// Output JSON (with possible callback)
RequestLog::log_stop( $actionResult, strlen($json));

//If a callback function is requested
if( array_key_exists('callback', $_GET) ){
    // Echo request-param 'callback' as function
    //    echo instead of concat to avoid large string manipulation
    echo $_GET['callback'];
    echo '(';
    echo $json;
    echo ')';
}
else{
    // Simply echo the json
    echo $json;
}

?>
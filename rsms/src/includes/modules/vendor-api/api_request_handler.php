<?php

require_once( dirname(__FILE__) . '/../../..' . '/Application.php');
require_once( dirname(__FILE__) . '/../../..' . '/includes/http_status.php' );

$apiName = $_REQUEST["api"];
$apiVersion = $_REQUEST["version"];
$endpoint = $_REQUEST["endpoint"];

session_start();

$user_ip = $_SERVER['REMOTE_ADDR'];
if( !isset($_SESSION['ip']) ){
    $_SESSION['ip'] = $user_ip;
}

if( !isset($_SESSION['USER']) ){
    $apiuser = new User();
    $apiuser->setUsername($user_ip);
    $_SESSION['USER'] = $apiuser;
}

$LOG = Logger::getLogger('api_handler');
$response_code = 200;
$json = '';

// Write standard headers before calling action.
//   Some actions (such as attachment download) may trigger sending
//     of headers.
header('content-type: application/javascript');

$actionDispatcher = new ApiRequestDispatcher($_REQUEST, $_SESSION);

// Attempt to dispatch to the requested action
$dispatchId = Metrics::start("Dispatch action " . RequestLog::describe());
try{
    ////////
    // Map Resource to action
    $actionName = $actionDispatcher->parse_api_request($endpoint, $_REQUEST);
    $LOG->debug( $_REQUEST );
    ////////

    RequestLog::init($actionName, $_REQUEST, 'request.api');
    RequestLog::log_start();

    $actionResult = $actionDispatcher->dispatch($actionName);
    $response_code = $actionResult->statusCode;

    // JSON-Encode result
    // Time how long it takes to encode this
    $jsonifyId = Metrics::start("Encode response " . RequestLog::describe());
    $json = JsonManager::encode($actionResult->actionFunctionResult);
    Metrics::stop($jsonifyId);
}
catch(InvalidApiPathException $e){
    $json = $e->getMessage();
    $response_code = 400;
}
catch(ResourceNotFoundException $e){
    $json = $e->getMessage();
    $response_code = 404;
}
catch(Exception $e){
    $LOG->error("Error occurred during API request handling");
    $LOG->error($e);
    $json = 'An error occurred';
    $response_code = 500;
}

// begin output
// Set the HTTP status code. ActionResult defaults this to 200
http_response_code( $response_code );

// Output JSON (with possible callback)
RequestLog::log_stop( $response_code, strlen($json));

// Simply echo the json
echo $json;

Metrics::stop($dispatchId);
?>
<?php

require_once( dirname(__FILE__) . '/Application.php');
require_once( dirname(__FILE__) . '/includes/http_status.php' );

$apiName = $_REQUEST["api"];
$apiVersion = $_REQUEST["version"];
$actionName = $_REQUEST["endpoint"];

// TODO: Split up action name to extract path parameters

$dataSource = $_REQUEST;

RequestLog::init($actionName, $dataSource);
$LOG = Logger::getLogger('api.' . $actionName);

RequestLog::log_start();

// Write standard headers before calling action.
//   Some actions (such as attachment download) may trigger sending
//     of headers.
header('content-type: application/javascript');

$actionDispatcher = new ActionDispatcher($_REQUEST, []);

// Attempt to dispatch to the requested action
$dispatchId = Metrics::start("Dispatch action " . RequestLog::describe());
$actionResult = $actionDispatcher->dispatch($actionName);
Metrics::stop($dispatchId);

// JSON-Encode result
// Time how long it takes to encode this
$jsonifyId = Metrics::start("Encode response " . RequestLog::describe());
$json = JsonManager::encode($actionResult->actionFunctionResult);
Metrics::stop($jsonifyId);

// begin output

// Set the HTTP status code. ActionResult defaults this to 200
http_response_code( $actionResult->statusCode );

// Output JSON (with possible callback)
RequestLog::log_stop( $actionResult, strlen($json));

// Simply echo the json
echo $json;

?>
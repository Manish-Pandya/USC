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
require_once( dirname(__FILE__) . '/action_setup.php');
session_start();
Logger::getRootLogger()->debug("START action.php");
$LOG = Logger::getLogger('action');

//$LOG->debug($_SESSION['DESTINATION']);

// Create Dispatcher (based on $_REQUEST)
$sessionSource = $_SESSION;
$actionDispatcher = new ActionDispatcher($dataSource, $sessionSource);

// Attempt to dispatch to the requested action
    $actionResult = $actionDispatcher->dispatch($actionName);
//TODO: set $actionResult->actionFunctionResult to session? should action function do this?
if ($actionResult->statusCode != 200){
    header ("HTTP/1.1 " . $actionResult->statusCode . " Action Error");
}
if($actionName != "loginAction"){
    // Send to the proper URL
    header("location: $actionResult->destinationPage");
}
//this is a login action.  We handle it differently because we need to store a possible redirect locations in the $_SESSION.
else{
    
    //failed login (ActionManager->loginAction() returned false)
    if ($actionResult->actionFunctionResult != true){
        //preserve the logout message so we can notify users they've been logged out
        if(isset($_SESSION["LOGGED_OUT"])){
            $loggedOut = $_SESSION["LOGGED_OUT"];
        }
        session_destroy();
        session_start();
        if(isset($loggedOut)){
            $_SESSION["LOGGED_OUT"] = $loggedOut;
        }
        $_SESSION['error'] = "The username or password you entered was incorrect.";
        header("location:" . LOGIN_PAGE);
    }
    //successful login (ActionManager->loginAction() returned true)
    else{
        $LOG->debug('action result truthy');
        unset($_SESSION['error']);
		$LOG->debug( $_SESSION['DESTINATION'] );
		$_SESSION['DESTINATION'] = str_replace("rsms/", "", $_SESSION['DESTINATION']);
		$_SESSION['DESTINATION'] = str_replace("//", "", $_SESSION['DESTINATION']);
        $LOG->debug(WEB_ROOT . $_SESSION['DESTINATION'] );
        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        if(isset($_SESSION['REDIRECT'])){
            $LOG->debug(WEB_ROOT . $_SESSION['DESTINATION'] );
            header("location:" . $_SESSION['REDIRECT']);
        }else{
            $LOG->debug( $uri . $_SESSION['DESTINATION'] );

            header("location:"  . $uri  . '/' . $_SESSION['DESTINATION']);
        }
    }
    
}

Logger::getRootLogger()->debug("END action.php");
?>

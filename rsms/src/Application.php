<?php

////////////////////////////////////////////////////////////////////////////////
//
//  Application Constants
//
////////////////////////////////////////////////////////////////////////////////

////////////////////////////////////////////////////////////////////////////////
//
// Bootstrap the application
//
////////////////////////////////////////////////////////////////////////////////
require_once dirname(__FILE__) . '/ApplicationBootstrapper.php';
ApplicationBootstrapper::bootstrap();

//Check session for Admin flag
function isAdminUser(){
	return isset($_SESSION['USER']) && $_SESSION['ADMIN'] == 'Y';
}

function securityCheck(){
	$LOG = Logger::getLogger('security');
	$LOG->fatal("test");

	if (!isset($_SESSION["USER"])){
		$LOG->fatal( $_SERVER['HTTP_COOKIE']);
		//Forward to login page
		header("location:" . LOGIN_PAGE);
	}
	else {
		return true;
	}
}

/** DEPRECATED - use login2 in ActionManager */
function login($username,$password) {
	//TODO: actually authenticate user
	$user = new User();
	$user->setUsername($username);

	$_SESSION['USER'] = $user;
	$_SESSION["ADMIN"] = "Y";

	// return true to indicate success
	return true;
}

function logout() {
	session_destroy();
	return true;
}

//////////////////////////////////////////////////


if( !array_key_exists('HTTP_REFERER', $_SERVER)){
	Logger::getRootLogger()->debug("No HTTP_REFERER. adding empty value");
	$_SERVER['HTTP_REFERER'] = '';
}

//TODO Application functions
?>
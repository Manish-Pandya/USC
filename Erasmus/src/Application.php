<?php

////////////////////////////////////////////////////////////////////////////////
//
//  Application Constants
//
////////////////////////////////////////////////////////////////////////////////

define('DIR_PATH', dirname(__FILE__) );
define('URL_PATH', 'http://localhost');
define('ADMIN_MAIL', 'hoke@graysail.com');
define('LOGIN_PAGE', 'login.php');

if(isProduction()){
	define('WEB_ROOT', '/rsms/');
}else{
	define('WEB_ROOT', '/Erasmus/src/');
}

////////////////////////////////////////////////////////////////////////////////
//
// Set up Logging
//
////////////////////////////////////////////////////////////////////////////////
require_once dirname(__FILE__) . '/logging/Logger.php';
Logger::configure( dirname(__FILE__) . "/includes/conf/log4php-config.php");

/////////////////////////////////////////////////////////////////////////////////
//
// Set local server config
//
////////////////////////////////////////////////////////////////////////////////
require_once dirname(__FILE__) . '/includes/conf/server.php';

////////////////////////////////////////////////////////////////////////////////
//
// Database
//
////////////////////////////////////////////////////////////////////////////////
require_once dirname(__FILE__) . '/includes/conf/connector.php';

///////////////////////////////////////////////////////////////////////////////
//
//  Autoload 
//
////////////////////////////////////////////////////////////////////////////////
require_once(dirname(__FILE__) . '/Autoloader.php');

////////////////////////////////////////////////////////////////////////////////
//
//  Error Handling
//
////////////////////////////////////////////////////////////////////////////////
require_once dirname(__FILE__) . '/includes/ErrorHandler.php';
ErrorHandler::init();

////////////////////////////////////////////////////////////////////////////////
//
// 	USER AUTHENTICATION AND AUTHORIZATION
//
////////////////////////////////////////////////////////////////////////////////

//Check session for Admin flag
function isAdminUser(){
	return isset($_SESSION['USSER']) && $_SESSION['ADMIN'] == 'Y';
}

function securityCheck(){
	if (!isset($_SESSION["USER"])){
		//Forward to login page
		header("location:" . LOGIN_PAGE);
	}
	else {
		return true;
	}
}

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

////////////////////////////////////////////////////////////////////////////////
//
// 	BOOLEAN TO FLAG APPLICATION AS DEV OR PRODUCTION
//
////////////////////////////////////////////////////////////////////////////////

function isProduction(){
	return false;
}

//////////////////////////////////////////////////

//TODO Application functions
?>
<?php

////////////////////////////////////////////////////////////////////////////////
//
//  Application Constants
//
////////////////////////////////////////////////////////////////////////////////

define('DIR_PATH', dirname(__FILE__) );
define('URL_PATH', 'http://localhost');
define('ADMIN_MAIL', 'hoke@graysail.com');

if(isProduction()){
	define('WEB_ROOT', '/rsms/');
	define('LOGIN_PAGE', '/rsms/');
	define('BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR', getcwd().'/biosafety-committees/protocol-documents/');


}else{
	define('WEB_ROOT', '/rsms/src/');
	define('LOGIN_PAGE', 'http://erasmus.graysail.com:9080/rsms/');
	define('BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR', 'D:/git/00_rsms/rsms/src/biosafety-committees/protocol-documents/');

}
define('UPLOAD_DIR_PATH', getcwd());

////////////////////////////////////////////////////////////////////////////////
//
// Set up Logging
//
////////////////////////////////////////////////////////////////////////////////
require_once dirname(__FILE__) . '/logging/Logger.php';
define('RSMS_LOGS', DIR_PATH . '/logs');
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
require_once dirname(__FILE__) . '/DBConnection.php';

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
// USER AUTHENTICATION AND AUTHORIZATION
//
////////////////////////////////////////////////////////////////////////////////

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

////////////////////////////////////////////////////////////////////////////////
//
// BOOLEAN TO FLAG APPLICATION AS DEV OR PRODUCTION
//
////////////////////////////////////////////////////////////////////////////////

function isProduction(){
	if(strstr($_SERVER['HTTP_HOST'], "graysail")){
		return false;
	}
	return true;
}

////////////////////////////////////////////////////////////////////////////////
//
// DETECT WHICH MODULE WE ARE IN
//
////////////////////////////////////////////////////////////////////////////////
function isRadiationEnabled() {
	if(	strstr($_SERVER["HTTP_REFERER"], '/rad/' ) || isset($_GET['rad']) )return true;
	return false;
}

function isVerificationEnabled(){
	if(	strstr($_SERVER["HTTP_REFERER"], '/verification/' ) || isset($_GET['verification']))return true;
	return false;
}

function isHazardInventoryEnabled(){
	if(	strstr($_SERVER["HTTP_REFERER"], '/hazard-inventory/' ) || isset($_GET['hazard-inventory']))return true;
	return false;
}

function isEquipmentEnabled(){
	if(	strstr($_SERVER["HTTP_REFERER"], '/equipment/' ) || isset($_GET['equipment']))return true;
	return false;
}

function isCommitteesEnabled(){
	if(	strstr($_SERVER["HTTP_REFERER"], '/biosafety-committees/' ) || isset($_GET['biosafety-committees']))return true;
	return false;
}


function isIBCEnabled(){
	if(	stristr($_SERVER["HTTP_REFERER"], '/ibc/' ) || isset($_GET['ibc']) || isset($_GET['IBC']))return true;
	return false;
}

//////////////////////////////////////////////////


if( !array_key_exists('HTTP_REFERER', $_SERVER)){
	Logger::getRootLogger()->info("No HTTP_REFERER. adding empty value");
	$_SERVER['HTTP_REFERER'] = '';
}

//TODO Application functions
?>
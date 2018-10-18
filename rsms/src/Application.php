<?php

////////////////////////////////////////////////////////////////////////////////
//
//  Application Constants
//
////////////////////////////////////////////////////////////////////////////////

define('DIR_PATH', dirname(__FILE__) );
define('URL_PATH', 'http://localhost');
define('UPLOAD_DIR_PATH', getcwd());

function getLocalPath($path){
	// If this is not an absolute path, prefix it with our path
	if( $path != NULL && substr($path, 0, 1) !== '/' ){
		return DIR_PATH . "/$path";
	}

	return $path;
}

/////////////////////////////////////////////////////////////////////////////////
//
// Read configuration
//
////////////////////////////////////////////////////////////////////////////////
require_once dirname(__FILE__) . '/ApplicationConfiguration.php';
ApplicationConfiguration::configure();

////////////////////////////////////////////////////////////////////////////////
//
// Set up Logging
//
////////////////////////////////////////////////////////////////////////////////
require_once dirname(__FILE__) . '/logging/Logger.php';
$logs_root = ApplicationConfiguration::get("logging.outputdir", './logs');
define('RSMS_LOGS', getLocalPath($logs_root));
Logger::configure( getLocalPath( ApplicationConfiguration::get("logging.configfile") ));

/////////////////////////////////////////////////////////////////////////////////
//
// Set authentication details
//
/////////////////////////////////////////////////////////////////////////////////

// Load non-sourced script intended for per-instance specification of (LDAP) auth provider
$auth_provider_include = ApplicationConfiguration::get('server.auth.include_script');
if( $auth_provider_include ){
	$authLog = Logger::getLogger('auth_provider');
	if( $authLog->isTraceEnabled()){
		$authLog->trace("Load auth provider script: $auth_provider_include");
	}

    require_once( getLocalPath( $auth_provider_include ));
}

////////////////////////////////////////////////////////////////////////////////
//
//  Application environment-dependent Constants
//
////////////////////////////////////////////////////////////////////////////////

define('ADMIN_MAIL', ApplicationConfiguration::get('server.web.ADMIN_MAIL'));
define('WEB_ROOT', ApplicationConfiguration::get('server.web.WEB_ROOT'));
define('LOGIN_PAGE', ApplicationConfiguration::get('server.web.LOGIN_PAGE'));
define('BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR', ApplicationConfiguration::get('server.web.BISOFATEY_PROTOCOLS_UPLOAD_DATA_DIR'));

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
// DETECT WHICH MODULE WE ARE IN
//
////////////////////////////////////////////////////////////////////////////////

require_once(dirname(__FILE__) . '/includes/ModuleManager.php');
$activeModule = ModuleManager::registerModules();

if( $activeModule != null){
	Logger::getRootLogger()->info("Active module is " . get_class($activeModule));
}

//////////////////////////////////////////////////


if( !array_key_exists('HTTP_REFERER', $_SERVER)){
	Logger::getRootLogger()->debug("No HTTP_REFERER. adding empty value");
	$_SERVER['HTTP_REFERER'] = '';
}

//TODO Application functions
?>
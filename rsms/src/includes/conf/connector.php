<?php
	
/*// Wired to use either PEAR DB (deprecated) or PEAR MDB2 (proper choice for php5, but not available in production env) 
//require_once 'DB.php';
require_once 'MDB2.php';
	$dbString = getDBConnection();
	$mdb2 =& MDB2::connect($dbString);
	
	if (PEAR::isError($mdb2)) {
		//TODO: Log error
   		die("DATABASE CONNECTION ERROR: $mdb2");
	}
	else{
		//TODO: Log success
	}
	
//	$mdb2->setFetchMode(DB_FETCHMODE_OBJECT);
	$mdb2->setFetchMode(MDB2_FETCHMODE_OBJECT);
	
	//Load extended module to allow for autoquery-insert
	$mdb2->loadModule('Extended');

	/** Define variable to hold the autoquery-insert mode. This is helpful if database modules are changed */
	//define('DATABASE_AUTOQUERY_INSERT', MDB2_AUTOQUERY_INSERT);
	
	/** Define variable to hold the autoquery-update mode. This is helpful if database modules are changed */
	//define('DATABASE_AUTOQUERY_UPDATE', MDB2_AUTOQUERY_UPDATE);

//No longer necessary; see DBConnection
//DBConnection::connect();

	?>
<?php
	
// Wired to use either PEAR DB (deprecated) or PEAR MDB2 (proper choice for php5, but not available in production env) 
//require_once 'DB.php';
require_once 'MDB2.php';
	$dbString = getDBConnection();
	$mdb2 =& MDB2::connect($dbString);
	if (PEAR::isError($mdb2)) {
   		die("DATABASE CONNECTION ERROR");
	}
//	$mdb2->setFetchMode(DB_FETCHMODE_OBJECT);
	$mdb2->setFetchMode(MDB2_FETCHMODE_OBJECT);
	
?>
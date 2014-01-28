<?php
//Settings for development server
//require_once('/usr/local/src/csg/classes/ADLDAPV2.php');

function isProductionServer() {
	return false;
}

function getDBConnection() {
	return 'mysql://erasmus:eR@m#682d@localhost/usc_ehs_rsms';
}

?>
<?php
//Settings for development server
//require_once('/usr/local/src/csg/classes/ADLDAPV2.php');

function isProductionServer() {
	return false;
}

function getDBConnection() {
	return 'mysql:host=localhost;dbname=usc_ehs_rsms';
}

function getDBUsername() {
	return 'erasmus';
}

function getDBPassword() {
	return 'eR@m#682d';
}


?>
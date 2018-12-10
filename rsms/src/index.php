<?php
session_start();
if( isset($_GET["REDIRECT"]) ) {
	$_SESSION["REDIRECT"] = str_replace("REDIRECT=", "", $_SERVER['QUERY_STRING']);
}
header( 'Location: login.php' ) ;
?>

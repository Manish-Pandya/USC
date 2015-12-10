<?php
session_start();
if($_GET["REDIRECT"] != NULL){
	$_SESSION["REDIRECT"] = $_SERVER['QUERY_STRING'];
}
header( 'Location: login.php' ) ;
?>

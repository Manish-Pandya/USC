<?php
session_start();
if($_GET["REDIRECT"] != NULL){
	$_SESSION["REDIRECT"] = str_replace("REDIRECT=", "", $_SERVER['QUERY_STRING']);
}
header( 'Location: login.php' ) ;
?>

<?php
require_once('ForwardUserToDefaultPage.php');

// If user is logged in without error, send them along to their destination
if( !isset($_SESSION) || !isset($_SESSION['USER'])){
	header( 'Location: login/' ) ;
}

?>

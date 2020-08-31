<?php
require_once('Application.php');

session_start();

if( isset($_SESSION) && isset($_SESSION['USER']) && !isset($_SESSION['error']) ){
    // There is an active session; redirect user to appropriate place
    if(isset($_SESSION["REDIRECT"])){
        $redirect = $_SESSION["REDIRECT"];

        // Once user has been redirected, clear their session var
        unset($_SESSION['REDIRECT']);
    }
    else {
        // No requested target; default to home page
        $redirect = (new ActionManager())->getUserDefaultPage();
    }

    if( $redirect ){
        header("Location: $redirect");
    }
    else {
        Logger::getRootLogger()->warn("User with active session (" . $_SESSION['USER']->getUsername() . ") is seeing Login page...");
    }
}

?>
<?php
if( !isset($_SESSION) ){
    // No session
    session_start();
}

if(!isset($_SESSION["USER"])){
    // User is not logged in
    Logger::getLogger(__FILE__)->info("User is not logged in");
    header("location:" . LOGIN_PAGE);
    die('User is not logged in');
}
?>
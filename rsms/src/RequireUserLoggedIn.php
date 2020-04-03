<?php
require_once( dirname(__FILE__) . '/Application.php');
session_start();
if(!isset($_SESSION["USER"])){
    // User is not logged in
    Logger::getLogger(__FILE__)->info("User is not logged in");
    header("location:" . LOGIN_PAGE);
    die('User is not logged in');
}
?>
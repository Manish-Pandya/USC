<?php
if(!isset($_SESSION["USER"])){
    // User is not logged in
    header("location:" . LOGIN_PAGE);
    die('User is not logged in');
}
?>
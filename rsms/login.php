<?php
if( stristr( $_SERVER['HTTP_HOST'], "graysail" ) ){
    include('src/Application.php');
}else{
    include('Application.php');
}
session_start();

if( isset($_SESSION) && !isset($_SESSION['error']) ){
	if(isset($_SESSION["REDIRECT"])){
		$redirect = $_SESSION["REDIRECT"];
	}
    //session_destroy();
    $_SESSION["REDIRECT"] = $redirect;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap-responsive.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootmetro.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/bootmetro-tiles.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/bootmetro-charms.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/metro-ui-light.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/icomoon.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/datepicker.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/style.css"/>
</head>
<body>
<div class="container-fluid">
    <h2 style="padding:5px 0;">Welcome to the University of South Carolina Research Safety Management System</h2>
    <form class="form form-horizontal" method="post" action="<?php echo WEB_ROOT?>action.php" style="padding:20px; background:white;">
        <input type="hidden" name="action" value="loginAction">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" name="username" placeholder="Enter Username">
          </div>
         <div class="form-group">
            <label for="exampleInputPassword1">Password</label>
            <input type="password" name="password" class="form-control" id="password" placeholder="Password">
         </div>
         
         <?php if(isset($_SESSION) && isset($_SESSION['error']) && $_SESSION['error'] != NULL) {?>
         <div class="form-group" style="width: 588px;margin-top: 10px;">
             <h3 class="alert alert-danger"><?php echo $_SESSION['error'];?></h3>
         </div>
         <?php } ?>
         <div class="form-group" style="margin-top:20px;">
            <button type="submit" name="submit" class="btn btn-large btn-success" id="login" style="padding:0 20px;">Login</button>
         </div>
    </form>
</div>
</body>


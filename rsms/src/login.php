<?php
include('Application.php');

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width,initial-scale=.9,shrink-to-fit=no">
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap-responsive.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootmetro.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/bootmetro-tiles.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/bootmetro-charms.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/metro-ui-light.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/icomoon.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/datepicker.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/style.css"/>

    <script type="text/javascript">
        function isSupportedBrowser() {
            var ua = window.navigator.userAgent;
            var msie = ua.indexOf('MSIE ');
            if (msie > 0) {
                // IE 10 or older
                return false
            }

            var trident = ua.indexOf('Trident/');
            if (trident > 0) {
                // IE 11
                return false;
            }

            // other browser
            return true;
        }
    </script>

    <style>
        img.usclogo {
            width: 310px;
            height: 53px;
            padding-top: 25px;
            padding-bottom: 25px;
        }

        section {
            margin-left: 10%;
            margin-right: 10%;
        }

        header {
            font-family: "Berlingske Sans", "Arial", sans-serif;
            font-size: 1.6em;
            min-height: 50px;

            padding-left: 10%;
            padding-right: 10%;

            display: flex;
            align-items: center;

            color: white;
        }

        header.main { background-color: black; }
        header.sub { background-color: #73000a; }
    </style>
</head>
<body>
    <header class="main">
        <img class="usclogo" src="<?php echo WEB_ROOT?>img/UofSC_Primary_RGB_REV_G.png"/>
    </header>
    <header class="sub">
        <h2 style="color: white">Research Safety Management System</h2>
    </header>

    <section style="padding:20px; background:white;">
        <form id="loginform" class="form form-horizontal" method="post" action="<?php echo WEB_ROOT?>action.php">
            <input type="hidden" name="action" value="loginAction">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" name="username" placeholder="Enter Username" autocomplete="username">
            </div>
            <div class="form-group">
                <label for="exampleInputPassword1">Password</label>
                <input type="password" name="password" class="form-control" id="password" placeholder="Password" autocomplete="current-password">
            </div>

            <?php if(isset($_SESSION) && isset($_SESSION['error']) && $_SESSION['error'] != NULL) {?>
            <div class="form-group" style="width: 588px;margin-top: 10px;">
                <h3 class="alert alert-danger"><?php echo $_SESSION['error'];?></h3>
            </div>
            <?php } ?>

            <?php if(isset($_SESSION) && isset($_SESSION['LOGGED_OUT']) && $_SESSION['LOGGED_OUT'] != NULL) {?>
            <div class="form-group" style="width: 588px;margin-top: 10px;">
                <h3 class="alert alert-danger"><?php echo $_SESSION['LOGGED_OUT'];?></h3>
            </div>
            <?php } ?>

            <div class="form-group" style="margin-top:20px;">
                <button type="submit" name="submit" class="btn btn-large btn-success" id="login" style="padding:0 20px;">Login</button>
            </div>
        </form>
        <div id="disclaimer-ie" style="display: flex; font-weight:bold; font-size: 1.2em;">
        </div>
    </section>

    <section>
        <div id="disclaimers">
            <div id="disclaimer-network">
                <span>This system can only be accessed from a secured University network (such as <b>uscfacstaff</b> or <b>uscstudent</b>) using your <a href="https://www.sc.edu/about/offices_and_divisions/university_technology_services/services/student/logins/networkusername.php">Network Username</a>.</span>
            </div>
        </div>
    </section>

    <script>
        if( !isSupportedBrowser() ){
            // Disallow login
            let form = document.getElementById('loginform');
            form.parentNode.removeChild(form);

            // Add warning
            document.getElementById('disclaimer-ie').innerHTML = '<i class="red icon-warning" style="padding-right: 10px;"></i>'
                + '<span>The browser you are using is not supported by RSMS. Please use a supported browser such as'
                + '&nbsp;<a target="_blank" href="http://www.google.com/chrome/">Google Chrome</a>,'
                + '&nbsp;<a target="_blank" href="https://www.mozilla.org/firefox/">Mozilla Firefox</a>,'
                + '&nbsp;<a target="_blank" href="https://www.apple.com/safari/">Apple Safari</a>,'
                + '&nbsp;or'
                + '&nbsp;<a target="_blank" href="https://www.microsoft.com/windows/microsoft-edge">Microsoft Edge</a>'
                + '</span>';
        }
    </script>
</body>


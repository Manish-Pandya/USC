<?php
/*
TODO: Manage Access Requests:

- User attempts login with USC creds

- LDAP authentication succeeds
- RSMS authorization fails
-- Begin new 
- Candidate-User authorization matches existing Request
-- Continue candidate-user session

- Begin/Continue candidate-user session
-- Session begins as a Candidate User with their already-authenticated username
-- Display username, request status
-- User selects Department
-- User selects supervising Principal Investigator
-- User confirms request
-- System stores the Access Request: [status, username, principal_investiagtor_id]

- Principal Investigator Approves request
- System creates new User from Access Request details
-- Update request status to APPROVED

- Principal Investigator Denies request
- Update request status to DENIED
*/

require_once('../Application.php');
require_once('../ForwardUserToDefaultPage.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- TODO: REVIEW THESE; COPIED FROM LOGIN -->

    <meta name="viewport" content="width=device-width,initial-scale=.9,shrink-to-fit=no">
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap-responsive.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootmetro.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/bootmetro-tiles.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/bootmetro-charms.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/metro-ui-light.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/icomoon.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/datepicker.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/select.min.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/style.css"/>

    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/rsms-style-theme.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/rsms-style-struct.css"/>
    <link type="text/css" rel="stylesheet" href="auth-styles.css" />

    <script src="<?php echo WEB_ROOT?>js/lib/jQuery.3.1.1/Content/Scripts/jquery-3.1.1.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/promise.min.js"></script>

    <script src="<?php echo WEB_ROOT?>js/constants.js"></script>

    <script src="<?php echo WEB_ROOT?>js/lib/angular.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-route.min.js"></script>

    <script src="<?php echo WEB_ROOT?>js/lib/ui-bootstrap-custom-tpls-0.4.0.js"></script>

    <script src="<?php echo WEB_ROOT?>js/convenienceMethodsModule.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-once.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular.filter.js"></script>

    <script src="<?php echo WEB_ROOT?>js/modalPosition.js"></script>

    <script src="<?php echo WEB_ROOT?>js/lib/angular-animate.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-busy.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-ui-router.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/cycle.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/select.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-sanitize.min.js"></script>

    <script src="<?php echo WEB_ROOT?>js/roleBased.js"></script>

    <!-- app -->
    <script src="scripts/AuthApp.js"></script>

    <!-- business logic-->

    <!-- controllers -->

    <!-- framework -->
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/XHR.js"></script>

    <!-- models -->

    <script>
        var GLOBAL_WEB_ROOT = '<?php echo WEB_ROOT?>';

        var user_access_request = <?php
            if( isset($_SESSION['CANDIDATE']) ){
                echo JsonManager::encode($_SESSION['CANDIDATE']);
            }
            else {
                echo 'undefined';
            }?>;

        var auth_errors = <?php
            $errors = [
                $_SESSION['error'] ?? null,
                $_SESSION['LOGGED_OUT'] ?? null
            ];

            echo json_encode( array_filter($errors, function($e){ return $e != null; }) );
        ?>;
    </script>

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
</head>

<body ng-app="ng-AuthApp" ng-controller="AuthAppCtrl">
    <header class="main">
        <img class="usclogo" src="<?php echo WEB_ROOT?>img/UofSC_Primary_RGB_REV_G.png"/>
    </header>
    <header class="sub">
        <h2 style="color: white">Research Safety Management System</h2>
    </header>

    <section style="padding:20px; background:white;" class="even-content">
        <div id="app-wrapper" style="width: 50%;">
            <div id="authapp" ui-view class="noBg authapp"></div>
            <div id="disclaimer-ie" style="display: flex; font-weight:bold; font-size: 1.2em;"></div>
        </div>

        <div id="app-info" style="width: 45%; margin-left: 5%">
            <!-- TODO: Display general information? -->
        <div>
    </section>

    <script>
        if( !isSupportedBrowser() ){
            // Disallow login
            let authapp = document.getElementById('authapp');
            authapp.parentNode.removeChild(authapp);

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

</html>

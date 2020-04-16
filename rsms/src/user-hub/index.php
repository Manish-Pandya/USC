<?php

require_once('../Application.php');

session_start();

// Check that user is logged in
require_once('../RequireUserLoggedIn.php');
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
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/font-awesome.min.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/angular-busy.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/select.min.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/style.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>user-hub/user-hub-styles.css" />

    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/rsms-style-theme.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/rsms-style-struct.css"/>

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

    <script src="<?php echo WEB_ROOT?>js/lib/angular-busy.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-ui-router.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/cycle.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/select.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-sanitize.min.js"></script>

    <script src="<?php echo WEB_ROOT?>js/roleBased.js"></script>

    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ui-mask.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/phone-format.js"></script>

    <!-- app -->
    <script src="scripts/UserHubApp.js"></script>

    <!-- directives -->
    <script src="scripts/directives/UserHubCategoryTable.js"></script>

<?php if( isset($_SESSION["USER"]) ){ ?>
    <script>
        var GLOBAL_SESSION_ROLES = <?php echo json_encode($_SESSION['ROLE']); ?>;
        //grab usable properties from the session user object
        var GLOBAL_SESSION_USER = {
            Name:    '<?php echo $_SESSION['USER']->getName(); ?>',
            Key_id: '<?php echo $_SESSION['USER']->getKey_id(); ?>'
        }
        var GLOBAL_WEB_ROOT = '<?php echo WEB_ROOT?>';
    </script>
<?php } ?>

    <style>
    .modal-body .controls .ui-select-container{
        /* Override ui-select-container floating in user hub modals */
        float:unset;
    }
    </style>

    <style>
    #userHub .hub-banner .banner-nav li {
        max-width: 200px;
        text-align: center;
    }
</style>

    <!-- Toast API -->
    <script type='text/javascript' src='<?php echo WEB_ROOT?>js/ToastApi.js'></script>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/ToastApi.css"/>

    <script>
        var GLOBAL_WEB_ROOT = '<?php echo WEB_ROOT?>';

        var RoleRequirements = <?php
            $rules = new UserCategoryRules();
            echo JsonManager::encode( $rules->getUserCategoryRules() );
        ?>;
    </script>
</head>

<body class="hub-theme-green">
    <?php require('../views/user_info_bar.php'); ?>

    <div ng-app="rsms-UserHub"
         ng-controller="AppCtrl"
         id="userHub"
         class="container-fluid"
         style="margin-top:25px;">

        <hub-banner-nav
            hub-title="User Hub"
            hub-icon="icon-user-2"
            hub-views="hubNavViews">
        </hub-banner-nav>

        <div class="hub-toolbar">
            <div class="even-content">
                <div class="control-group" style="margin-left: auto;">
                    <a class="btn btn-primary left" ng-click="openUserLookupModal()">
                        Find a User<i class="icon-magnifying-glass"></i>
                    </a>
                </div>
            </div>

            <h2 class="alert alert-danger" ng-if="error">{{error}}</h2>
        </div>

        <div id="userhubapp" ui-view class="noBg"></div>
    </div>
</body>
</html>

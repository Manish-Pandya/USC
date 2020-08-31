<?php
    require_once('../Application.php');

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
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>email-hub/email-hub-styles.css" />

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

    <script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/tinymce.js"></script>

    <script src="<?php echo WEB_ROOT?>js/modalPosition.js"></script>

    <script src="<?php echo WEB_ROOT?>js/lib/angular-busy.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-ui-router.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/cycle.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/select.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-sanitize.min.js"></script>

    <script src="<?php echo WEB_ROOT?>js/roleBased.js"></script>

    <!-- app -->
    <script src="scripts/app.js"></script>

    <!-- business logic-->
    <script src="scripts/actionFunctions.js"></script>

    <!-- controllers -->
    <script src="scripts/controllers/EmailHubHomeCtrl.js"></script>
    <script src="scripts/controllers/EmailHubTemplatesCtrl.js"></script>
    <script src="scripts/controllers/EmailHubQueueCtrl.js"></script>

    <!-- framework -->
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/DataStoreManager.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/InstanceFactory.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/UrlMapping.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/XHR.js"></script>

    <!-- models -->
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/models/FluxCompositerBase.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/models/ViewModelHolder.js"></script>

    <!-- Toast API -->
    <script type='text/javascript' src='<?php echo WEB_ROOT?>js/ToastApi.js'></script>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/ToastApi.css"/>

    <script>
        var GLOBAL_WEB_ROOT = '<?php echo WEB_ROOT?>';
    </script>
</head>

<body>
    <?php require('../views/user_info_bar.php'); ?>

    <div ng-app="ng-EmailHub" ng-controller="AppCtrl" class="container-fluid hub-theme-green-dark" style="margin-top:25px;">
        <div cg-busy="{promise:loading, message:'Loading...', templateUrl:'../busy-templates/full-page-busy.html'}"></div>

        <!-- NAVIGATION -->
        <hub-banner-nav
            hub-title="Email Hub"
            hub-subtitle="Template Management"
            hub-icon="icon-email"
            hub-views="hubNavViews">
        </hub-banner-nav>

        <!-- VIEW NESTING -->
        <div ui-view class="noBg"></div>
    </div>

</body>
</html>

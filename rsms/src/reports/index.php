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
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/metro-ui-light.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/icomoon.css"/>
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/datepicker.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/font-awesome.min.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/10-18-2017-manual-bundle.min.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/angular-busy.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/select.min.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/style.css" />

    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/reports-styles.css" />

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

    <!-- app -->
    <script src="scripts/app.js"></script>

    <!-- business logic-->
    <script src="scripts/actionFunctions.js"></script>

    <!-- controllers -->
    <script src="scripts/controllers/inspection-summary/InspectionsSummaryReportCtrl.js"></script>
    <script src="scripts/controllers/inspection-summary/AvailableInspectionsSummaryReportsCtrl.js"></script>

    <!-- framework -->
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/DataStoreManager.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/InstanceFactory.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/UrlMapping.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/XHR.js"></script>

    <!-- models -->
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/models/FluxCompositerBase.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/models/ViewModelHolder.js"></script>

    <script>
        var GLOBAL_WEB_ROOT = '<?php echo WEB_ROOT?>';
    </script>
</head>

<body>
    <?php require('../views/user_info_bar.php'); ?>

    <div ng-app="ng-Reports" ng-controller="AppCtrl" class="container-fluid" style="margin-top:25px;">
        <div cg-busy="{promise:loading, message:'Loading...', templateUrl:'../busy-templates/full-page-busy.html'}"></div>

        <!-- NAVIGATION -->
        <div class="banner no-print blueBg">
            <h1>
                <i class="title-icon icon-clipboard-2" style="margin: 5px 0 0 5px;"></i>
                Reports
                <a style="float:right;margin: 15px 30px 0 0; color:white" href="<?php echo WEB_ROOT?>views/RSMSCenter.php#/inspections">
                    <i class="icon-home" style="font-size:40px;"></i>
                </a>
            </h1>

            <ul class="banner-nav" ng-if="moduleNavLinks.length">
                <li ng-repeat="link in moduleNavLinks"><a ui-sref="{{link.expression}}" ng-bind="link.text"></a></li>
            </ul>
        </div>

        <!-- VIEW NESTING -->
        <div ui-view class="noBg isr"></div>
    </div>

</body>
</html>

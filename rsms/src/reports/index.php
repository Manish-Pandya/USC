<?php
    require_once('../Application.php');

    session_start();
    // TODO: Check that user is logged in, or have the server do it...
?>
<!DOCTYPE html>
<html lang="en">
<head>

    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/10-18-2017-manual-bundle.min.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/angular-busy.css"/>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/select.min.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/style.css" />

    <link href="<?php echo WEB_ROOT?>js/lib/ng-quick-date/ng-quick-date.css" rel="stylesheet" />

    <!-- included fonts
     <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
    -->
    <!-- included javascript libraries
    -->
    <script src="<?php echo WEB_ROOT?>js/lib/moment.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/lodash.4.17.3/content/Scripts/lodash.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/jQuery.3.1.1/Content/Scripts/jquery-3.1.1.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/promise.min.js"></script>

    <script src="<?php echo WEB_ROOT?>js/constants.js"></script>

    <script src="<?php echo WEB_ROOT?>js/lib/angular.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-route.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/ui-bootstrap-custom-tpls-0.4.0.js"></script>
    <script src="<?php echo WEB_ROOT?>js/convenienceMethodsModule.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/ng-quick-date.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-once.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular.filter.js"></script>
    <script src="<?php echo WEB_ROOT?>js/modalPosition.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-busy.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-ui-router.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/cycle.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/select.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-sanitize.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/roleBased.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/contextMenu.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/ng-quick-date/ng-quick-date.js"></script>


    <!-- Required for the ORM framework -->
    <!-- TODO include everything in certain directories by default -->
    <!-- app -->
    <script src="scripts/app.js"></script>

    <!-- business logic-->

    <!-- controllers -->
    <script src="scripts/controllers/InspectionsSummaryReportCtrl.js"></script>

    <!-- framework -->
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/DataStoreManager.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/InstanceFactory.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/UrlMapping.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/XHR.js"></script>

    <!-- models -->
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/models/FluxCompositerBase.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/models/ViewModelHolder.js"></script>

</head>
<body>

<div ng-app="ng-Reports" ng-controller="AppCtrl" class="container-fluid">
    <div cg-busy="{promise:loading, message:'Loading...', templateUrl:'../busy-templates/full-page-busy.html'}"></div>

    <!-- NAVIGATION -->
    <div class="banner {{bannerClass}} radiation" ng-class="{'dashboard-banner':dashboardView, 'hide': noHead}">
        <h1>{{viewLabel}} <a style="float:right;margin: 11px 128px 0 0; color:black" href="<?php echo WEB_ROOT?>views/RSMSCenter.php#/safety-programs"><i class="icon-home" style="font-size:40px;"></i></a></h1>
    </div>

    <!-- VIEW NESTING -->
    <div ui-view class="noBg isr"></div>
</div>

</body>
</html>

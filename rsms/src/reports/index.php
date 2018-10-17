<?php
    require_once('../Application.php');

    session_start();
    // TODO: Check that user is logged in, or have the server do it...
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
    <script src="scripts/actionFunctions.js"></script>

    <!-- controllers -->
    <script src="scripts/controllers/ReportTypesCtrl.js"></script>
    <script src="scripts/controllers/InspectionsSummaryReportCtrl.js"></script>

    <!-- framework -->
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/DataStoreManager.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/InstanceFactory.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/UrlMapping.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/XHR.js"></script>

    <!-- models -->
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/models/FluxCompositerBase.js"></script>
    <script src="<?php echo WEB_ROOT?>ignorasmus/client-side-framework/models/ViewModelHolder.js"></script>

    <!-- TODO: Extract to file -->
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }

        .banner {
            margin-top: -2px;

            background: white;
            padding: 10px;
            background-repeat: no-repeat !important;
            background-size: 70px !Important;
            padding-left: 80px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.7)
        }

        .banner.dashboard-banner {
            position: fixed;
            width: 100%;
            left: 0;
            z-index: 1040;
        }

        .dashboard {
            padding-top: 75px;
        }

        .title-icon {
            margin-right: 5px;
            font-size: 35px;
            width: auto;
            line-height: 43px;
        }

        .report-detail ul {
            list-style: none;
            font-size: 25px;
        }

        .report-detail .title {
            font-weight: bold;
        }

        .report-detail ul li {
            padding: 5px;
        }

        .report-detail th.sortable {
            cursor: pointer;
        }

        .report-detail tr:hover {
            color: #49afcd !important;
        }

        /* Inspection Status-based Styles */
        .report-detail tr.overdue {
            color: black;
        }

        .report-detail tr.overdue:hover {
            color: black !important;
        }

        .report-detail tr.overdue.overdue-cap {
            background-color: #ffff00 !important;
            border-color: #ffff00 !important;
        }

        .report-detail tr.inspection-completed {
            font-style: italic;
            color: #777;
        }
    </style>
</head>

<body>
    <?php if($_SESSION['USER'] != NULL){ ?>
        <div class="user-info no-print" ng-controller="roleBasedCtrl">
            <div>
                Signed in as <?php echo $_SESSION['USER']->getName(); ?>
                <a style="float:right;" href="<?php echo WEB_ROOT?>action.php?action=logoutAction">Sign Out</a>
            </div>
        </div>
    <?php }?>

    <div ng-app="ng-Reports" ng-controller="AppCtrl" class="container-fluid" style="margin-top:25px;">
        <div cg-busy="{promise:loading, message:'Loading...', templateUrl:'../busy-templates/full-page-busy.html'}"></div>

        <!-- NAVIGATION -->
        <div class="no-print blueBg">
            <h1>
                <i class="title-icon icon-clipboard-2"></i>
                Reports
                <a style="float:right;margin: 11px 28px 0 0; color:white" href="<?php echo WEB_ROOT?>views/RSMSCenter.php#/inspections">
                    <i class="icon-home" style="font-size:40px;"></i>
                </a>
            </h1>
        </div>

        <!-- VIEW NESTING -->
        <div ui-view class="noBg isr"></div>
    </div>

</body>
</html>

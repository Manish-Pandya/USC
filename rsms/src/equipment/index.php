<?php
if(stristr($_SERVER['REQUEST_URI'],'/RSMScenter')){
    require_once('../Application.php');
}elseif(stristr($_SERVER['REQUEST_URI'],'/login')){
    require_once('Application.php');
}else{
    require_once('../Application.php');
}
session_start();


?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- stylesheets -->
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap-responsive.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/ui-lightness/jquery-ui-1.10.3.custom.min.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootmetro.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/bootmetro-tiles.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/bootmetro-charms.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/metro-ui-light.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/icomoon.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/datepicker.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/jqtree.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/font-awesome.min.css"/>

<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/ng-mobile-menu.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/angular-busy.css">
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/style.css" />

<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/equipment-styles.css">
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/temp-file-upload.css">
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/select.min.css"/>

<!-- included fonts
 <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
-->
<!-- included javascript libraries-->
<script src="../js/lib/jQuery.3.1.1/Content/Scripts/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/jquery-ui.js"></script>
<script src="../js/lib/lodash.4.17.3/content/Scripts/lodash.min.js"></script>
<script src="../js/lib/promise.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/constants.js"></script>
    <script src="../js/lib/moment.js"></script>
<script src="<?php echo WEB_ROOT?>js/lib/jquery.mjs.nestedSortable.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/scrollDisabler.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular.js"></script>
<script src="<?php echo WEB_ROOT?>js/lib/angular-route.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ui-bootstrap-custom-tpls-0.4.0.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/jquery-ui-1.10.3.custom.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ng-mobile-menu.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/convenienceMethodsModule.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ng-quick-date.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ng-infinite-scroll.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular-once.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/modalPosition.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular-busy.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular-ui-router.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/cycle.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ui-mask.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/select.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular-sanitize.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/roleBased.js"></script>
<script src="../js/lib/ng-quick-date.js"></script>

<!-- Required for the ORM framework -->
<!-- TODO include everything in certain directories by default -->

<!-- app -->
<script type="text/javascript" src="./scripts/app.js"></script>

<!-- business logic-->
<script type="text/javascript" src="../client-side-framework/rootApplicationController.js"></script>
<script type="text/javascript" src="./scripts/applicationController.js"></script>

<!-- controllers -->
<script type="text/javascript" src="./scripts/controllers/MainCtrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/AutoclavesCtrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/BioSafetyCabinetsCtrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/ChemFumeHoodsCtrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/LasersCtrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/X-RayCtrl.js"></script>

<!-- directives -->
<!--script type="text/javascript" src="./scripts/directives/someDirective.js"></script-->
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/scrolltable.js"></script>
<script type="text/javascript" src="scripts/directives/side-nav.js"></script>
<script src="../js/uploadContainer.js"></script>
<!-- filters -->
<script type="text/javascript" src="../client-side-framework/filters/filtersApp.js"></script>
<script type="text/javascript" src="scripts/filters/equipmentFilters.js"></script>
<script type="text/javascript" src="../js/lib/angular.filter.js"></script>

<!-- new framework -->
<script src="../ignorasmus/client-side-framework/DataStoreManager.js"></script>
<script src="../ignorasmus/client-side-framework/InstanceFactory.js"></script>
<script src="../ignorasmus/client-side-framework/UrlMapping.js"></script>
<script src="../ignorasmus/client-side-framework/XHR.js"></script>
<script src="../ignorasmus/client-side-framework/models/FluxCompositerBase.js"></script>
<script src="../ignorasmus/client-side-framework/models/ViewModelHolder.js"></script>


<!-- models -->
<script src="./scripts/models/Autoclave.js"></script>
<script src="./scripts/models/BioSafetyCabinet.js"></script>
<script src="./scripts/models/ChemFumeHood.js"></script>
<script src="./scripts/models/Laser.js"></script>
<script src="./scripts/models/XRay.js"></script>
<script src="./scripts/models/Building.js"></script>
<script src="./scripts/models/Room.js"></script>
<script src="scripts/models/PrincipalInvestigator.js"></script>
<script src="scripts/models/EquipmentInspection.js"></script>
<script src="scripts/models/Campus.js"></script>
<script src="scripts/models/User.js"></script>
<script src="scripts/models/Role.js"></script>

    <!-- Toast API -->
    <script type='text/javascript' src='<?php echo WEB_ROOT?>js/ToastApi.js'></script>
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/ToastApi.css"/>

    <?php require_once '../includes/modules/lab-inspection/js/room-type-constants.js.php'; ?>
</head>
<body>

    <!--user-info...-->
    <?php require('../views/user_info_bar.php'); ?>

    <div ng-app="EquipmentModule" ng-controller="NavCtrl" class="container-fluid" style="margin-top:25px;">
    <div cg-busy="{promise:loading,message:'Loading...',templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
    <!-- NAVIGATION -->
      <div class="banner {{bannerClass | splitAtPeriod}} equipment" ng-class="{'dashboard-banner':dashboardView, 'hide': noHead}">
        <h1 style="color:white;">Lab Equipment <a style="float:right;margin: 11px 128px 0 0; color:white" href="<?php echo WEB_ROOT?>"><i class="icon-home" style="font-size:40px;"></i></a></h1>
      </div>
    <!-- VIEW NESTING -->
        <div ui-view class="noBg"></div>
    </div>
</body>

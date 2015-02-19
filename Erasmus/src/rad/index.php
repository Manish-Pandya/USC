<?php
if(stristr($_SERVER['REQUEST_URI'],'/RSMScenter')){
	require_once('../Application.php');
}elseif(stristr($_SERVER['REQUEST_URI'],'/login')){
	require_once('Application.php');
}else{
	require_once('../Application.php');
}

echo '<script type="text/javascript">
var isProductionServer;';
if($_SERVER['HTTP_HOST'] != 'erasmus.graysail.com'){
  echo 'isProductionServer = true;';
}
?>
</script>
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
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/style.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/jqtree.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/font-awesome.min.css"/>

<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/ng-mobile-menu.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/jquery-ui.css">
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/angular-busy.css">

<!-- included fonts 
 <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
-->
<!-- included javascript libraries 
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.0.7/angular.js"></script>-->
<script type='text/javascript' src='<?php echo WEB_ROOT?>js/lib/jquery-1.9.1.js'></script>
  
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/jquery-ui.js"></script>
<!--
<script type='text/javascript' src="http://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.2/jquery.ui.touch-punch.min.js"></script>
-->
<script src="<?php echo WEB_ROOT?>js/lib/jquery.mjs.nestedSortable.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/scrollDisabler.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular.js"></script>
<script src="<?php echo WEB_ROOT?>js/lib/angular-route.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ui-bootstrap-custom-tpls-0.4.0.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/jquery-1.10.0.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/jquery-ui-1.10.3.custom.min.js"></script>
  <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ng-mobile-menu.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/convenienceMethodsModule.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ng-quick-date.js"></script>
<!--<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ng-infinite-scroll.min.js"></script>-->
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ng-infinite-scroll.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular-once.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/modalPosition.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular-busy.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular-ui-router.min.js"></script>

<!-- Required for the ORM framework -->
<!-- TODO include everything in certain directories by default -->

<!-- app -->
<script type="text/javascript" src="./scripts/app.js"></script>

<!-- business logic-->
<script type="text/javascript" src="./scripts/actionFunctions.js"></script>

<!-- controllers -->
<script type="text/javascript" src="./scripts/controllers/generic-modal-controller.js"></script>

<script type="text/javascript" src="./scripts/controllers/main.js"></script>
<script type="text/javascript" src="./scripts/controllers/about.js"></script>
<script type="text/javascript" src="./scripts/controllers/users.js"></script>
<script type="text/javascript" src="./scripts/controllers/hazardHub.js"></script>
<script type="text/javascript" src="./scripts/controllers/hazardInventory.js"></script>
<script type="text/javascript" src="./scripts/controllers/testCtrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/admin/radmin.js"></script>
<script type="text/javascript" src="./scripts/controllers/admin/pi.js"></script>


<!-- directives -->
<script type="text/javascript" src="./scripts/directives/hazardHubDirectives.js"></script>

<!-- filters -->
<script type="text/javascript" src="./scripts/filters/dateToIso.js"></script>
<script type="text/javascript" src="./scripts/filters/splitAtPeriod.js"></script>
<script type="text/javascript" src="./scripts/filters/carboyIsAvailable.js"></script>


<!-- framework -->
<script src="./scripts/genericModel/inheritance.js"></script>
<script src="./scripts/genericModel/genericModel.js"></script>
<script src="./scripts/genericModel/genericAPI.js"></script>
<script src="./scripts/genericModel/modelInflator.js"></script>
<script src="./scripts/genericModel/urlMapper.js"></script>
<script src="./scripts/dataStore/dataStore.js"></script>
<script src="./scripts/dataStore/dataStoreManager.js"></script>
<script src="./scripts/dataStore/dataSwitch.js"></script>
<script src="./scripts/dataStore/dataLoader.js"></script>


<!-- models -->
<script src="./scripts/models/Authorization.js"></script>
<script src="./scripts/models/Carboy.js"></script>
<script src="./scripts/models/CarboyUseCycle.js"></script>
<script src="./scripts/models/Drum.js"></script>
<script src="./scripts/models/Hazard.js"></script>
<script src="./scripts/models/Isotope.js"></script>
<script src="./scripts/models/Parcel.js"></script>
<script src="./scripts/models/ParcelUse.js"></script>
<script src="./scripts/models/ParcelUseAmount.js"></script> <!-- this may not be needed on the frontend, think about that later -->
<script src="./scripts/models/Pickup.js"></script>
<script src="./scripts/models/PrincipalInvestigator.js"></script>
<script src="./scripts/models/PurchaseOrder.js"></script>
<script src="./scripts/models/SolidsContainer.js"></script>
<script src="./scripts/models/User.js"></script>
<script src="./scripts/models/WasteBag.js"></script>
<script src="./scripts/models/WasteType.js"></script>
<script src="./scripts/models/Room.js"></script>


<script>
  $(function() {
    $( ".sortable" ).sortable({
      placeholder: "ui-state-highlight"
    });
    $( ".sortable" ).disableSelection();
  });
</script>
</head>
<body>

<div ng-app="00RsmsAngularOrmApp" ng-controller="NavCtrl" class="container-fluid">
<div cg-busy="{promise:loading,message:'Loading...',templateUrl:'views/busy-templates/full-page-busy.html'}"></div>
<!-- NAVIGATION -->
  <div class="banner {{bannerClass | splitAtPeriod}}" ng-class="{'dashboard-banner':dashboardView}">
    <h1>{{viewLabel}}</h1>
  </div>
<!-- VIEW NESTING -->
	<div ui-view class="noBg"></div>
</div>
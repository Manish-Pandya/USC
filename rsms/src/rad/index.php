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
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/angular-busy.css">
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/rad-styles.css">

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
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/cycle.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ui-mask.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/select.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular-sanitize.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/roleBased.js"></script>


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
<script type="text/javascript" src="./scripts/controllers/admin/admin-pickup-ctrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/admin/wipe-test-ctrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/admin/inventories-ctrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/admin/disposals-ctrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/admin/CarboysCtrl.js"></script>


<script type="text/javascript" src="./scripts/controllers/pi/PiRadHomeCtrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/pi/RecepticalCtrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/pi/UseLogCtrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/pi/ParcelUseLogCtrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/pi/PickupCtrl.js"></script>
<script type="text/javascript" src="./scripts/controllers/pi/QuarterlyInventoryCtrl.js"></script>

<script type="text/javascript" src="./scripts/controllers/inspection/inspectionWipeCtrl.js"></script>


<!-- directives -->
<script type="text/javascript" src="./scripts/directives/hazardHubDirectives.js"></script>
<script type="text/javascript" src="./scripts/directives/dateInput.js"></script>
<script type="text/javascript" src="./scripts/directives/combobox.js"></script>



<!-- filters -->
<script type="text/javascript" src="./scripts/filters/dateToIso.js"></script>
<script type="text/javascript" src="./scripts/filters/splitAtPeriod.js"></script>
<script type="text/javascript" src="./scripts/filters/carboyIsAvailable.js"></script>
    <script type="text/javascript" src="./scripts/filters/carboyHasNoRetireDate.js"></script>
<script type="text/javascript" src="./scripts/filters/parcelParser.js"></script>
<script type="text/javascript" src="./scripts/filters/activePickupFilter.js"></script>
<script type="text/javascript" src="./scripts/filters/needsWipeTest.js"></script>
<script type="text/javascript" src="./scripts/filters/miscWipeTests.js"></script>
<script type="text/javascript" src="./scripts/filters/disposalCycles.js"></script>
<script type="text/javascript" src="./scripts/filters/disposalSolids.js"></script>
<script type="text/javascript" src="./scripts/filters/inventoryStatus.js"></script>

<!-- framework -->
<script src="../client-side-framework/genericModel/inheritance.js"></script>
<script src="../client-side-framework/genericModel/genericModel.js"></script>
<script src="../client-side-framework/genericModel/genericPrincipalInvestigator.js"></script>
<script src="../client-side-framework/genericModel/genericAPI.js"></script>
<script src="../client-side-framework/genericModel/modelInflator.js"></script>
<script src="../client-side-framework/genericModel/urlMapper.js"></script>
<script src="../client-side-framework/dataStore/dataStore.js"></script>
<script src="../client-side-framework/dataStore/dataStoreManager.js"></script>
<script src="../client-side-framework/dataStore/dataSwitch.js"></script>
<script src="../client-side-framework/dataStore/dataLoader.js"></script>


<!-- models -->
<script src="./scripts/models/Authorization.js"></script>
<script src="./scripts/models/Carboy.js"></script>
<script src="./scripts/models/CarboyUseCycle.js"></script>
<script src="./scripts/models/CarboyReadingAmount.js"></script>
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
<script src="./scripts/models/ScintVialCollection.js"></script>
<script src="./scripts/models/Inspection.js"></script>
<script src="./scripts/models/InspectionWipeTest.js"></script>
<script src="./scripts/models/InspectionWipe.js"></script>
<script src="./scripts/models/ParcelWipeTest.js"></script>
<script src="./scripts/models/ParcelWipe.js"></script>
<script src="./scripts/models/MiscellaneousWipeTest.js"></script>
<script src="./scripts/models/MiscellaneousWipe.js"></script>
<script src="./scripts/models/QuarterlyInventory.js"></script>
<script src="./scripts/models/PIQuarterlyInventory.js"></script>

</head>
<body>

<div ng-app="00RsmsAngularOrmApp" ng-controller="NavCtrl" class="container-fluid">
<div cg-busy="{promise:loading,message:'Loading...',templateUrl:'views/busy-templates/full-page-busy.html'}"></div>
<!-- NAVIGATION -->
  <div class="banner {{bannerClass | splitAtPeriod}} radiation" ng-class="{'dashboard-banner':dashboardView, 'hide': noHead}">
    <h1>{{viewLabel}}</h1>
  </div>
<!-- VIEW NESTING -->
    <div ui-view class="noBg"></div>
</div>

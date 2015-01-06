<?php
if(stristr($_SERVER['REQUEST_URI'],'/RSMScenter')){
	require_once('../Application.php');
}elseif(stristr($_SERVER['REQUEST_URI'],'/login')){
	require_once('Application.php');
}else{
	require_once('../../Application.php');
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


<!-- Required for the ORM framework -->

<!-- app -->
<script type="text/javascript" src="../scripts/app.js"></script>

<!-- business logic-->
<script type="text/javascript" src="../scripts/actionFunctions.js"></script>

<!-- controllers -->
<script type="text/javascript" src="../scripts/controllers/main.js"></script>
<script type="text/javascript" src="../scripts/controllers/about.js"></script>
<script type="text/javascript" src="../scripts/controllers/users.js"></script>
<script type="text/javascript" src="../scripts/controllers/hazardHub.js"></script>
<script type="text/javascript" src="../scripts/controllers/hazardInventory.js"></script>

<!-- directives -->
<script type="text/javascript" src="../scripts/directives/hazardHubDirectives.js"></script>

<!-- framework -->
<script src="../scripts/genericModel/inheritance.js"></script>
<script src="../scripts/genericModel/genericModel.js"></script>
<script src="../scripts/genericModel/genericAPI.js"></script>
<script src="../scripts/genericModel/modelContructor.js"></script>
<script src="../scripts/dataStore/dataStore.js"></script>
<script src="../scripts/dataStore/dataManager.js"></script>

<!-- models -->
<script src="../scripts/models/User.js"></script>
<script src="../scripts/models/PrincipalInvestigator.js"></script>
<script src="../scripts/models/Hazard.js"></script>


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
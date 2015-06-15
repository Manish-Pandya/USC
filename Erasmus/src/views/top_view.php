<?php
session_start();

if(stristr($_SERVER['REQUEST_URI'],'/RSMScenter')){
    require_once('../Application.php');
}elseif(stristr($_SERVER['REQUEST_URI'],'/login')){
	require_once('../Erasmus/src/Application.php');
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
<link type="text/css" rel="stylesheet" href="src/css/bootstrap.css"/>
<link type="text/css" rel="stylesheet" href="src/css/bootstrap-responsive.css"/>
<link type="text/css" rel="stylesheet" href="src/css/ui-lightness/jquery-ui-1.10.3.custom.min.css"/>
<link type="text/css" rel="stylesheet" href="src/css/bootmetro.css"/>
<link rel="stylesheet" type="text/css" href="src/css/bootmetro-tiles.css"/>
<link rel="stylesheet" type="text/css" href="src/css/bootmetro-charms.css"/>
<link rel="stylesheet" type="text/css" href="src/css/metro-ui-light.css"/>
<link rel="stylesheet" type="text/css" href="src/css/icomoon.css"/>
<link rel="stylesheet" type="text/css" href="src/css/datepicker.css"/>
<link type="text/css" rel="stylesheet" href="src/stylesheets/style.css"/>
<link type="text/css" rel="stylesheet" href="src/css/jqtree.css"/>
<link type="text/css" rel="stylesheet" href="src/css/font-awesome.min.css"/>

<link type="text/css" rel="stylesheet" href="src/css/ng-mobile-menu.css"/>
<link rel="stylesheet" type="text/css" href="src/css/jquery-ui.css">

<!-- included fonts
 <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
-->
<!-- included javascript libraries
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.0.7/angular.js"></script>-->
<script type='text/javascript' src='src/js/lib/jquery-1.9.1.js'></script>

<script type="text/javascript" src="src/js/lib/jquery-ui.js"></script>
<!--
<script type='text/javascript' src="http://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.2/jquery.ui.touch-punch.min.js"></script>
-->
<script src="src/js/lib/jquery.mjs.nestedSortable.js"></script>
<script type="text/javascript" src="src/js/lib/scrollDisabler.js"></script>
<script type="text/javascript" src="src/js/lib/angular.js"></script>
<script src="src/js/lib/angular-route.min.js"></script>
<script type="text/javascript" src="src/js/lib/ui-bootstrap-custom-tpls-0.4.0.js"></script>
<script type="text/javascript" src="src/js/lib/jquery-1.10.0.min.js"></script>
<script type="text/javascript" src="src/js/lib/jquery-ui-1.10.3.custom.min.js"></script>
  <script type="text/javascript" src="src/js/lib/ng-mobile-menu.js"></script>
<script type="text/javascript" src="src/js/convenienceMethodsModule.js"></script>
<script type="text/javascript" src="src/js/lib/ng-quick-date.js"></script>
<!--<script type="text/javascript" src="src/js/lib/ng-infinite-scroll.min.js"></script>-->
<script type="text/javascript" src="src/js/lib/ng-infinite-scroll.min.js"></script>
<script type="text/javascript" src="src/js/lib/angular-once.js"></script>
<script type="text/javascript" src="src/js/modalPosition.js"></script>
<script type="text/javascript" src="src/js/lib/ui-mask.js"></script>
<script type="text/javascript" src="src/js/roleBased.js"></script>

</head>
<body>

<div class="container-fluid " id="wrapper">

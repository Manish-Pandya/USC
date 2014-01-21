<!DOCTYPE html>
<html lang="en">
<head>
<!-- stylesheets -->
<link type="text/css" rel="stylesheet" href="/Erasmus/src/css/bootstrap.css"/>
<link type="text/css" rel="stylesheet" href="/Erasmus/src/css/bootstrap-responsive.css"/>
<link type="text/css" rel="stylesheet" href="/Erasmus/src/css/ui-lightness/jquery-ui-1.10.3.custom.min.css"/>
<link type="text/css" rel="stylesheet" href="/Erasmus/src/css/bootmetro.css"/>
<link rel="stylesheet" type="text/css" href="/Erasmus/src/css/bootmetro-tiles.css"/>
<link rel="stylesheet" type="text/css" href="/Erasmus/src/css/bootmetro-charms.css"/>
<link rel="stylesheet" type="text/css" href="/Erasmus/src/css/metro-ui-light.css"/>
<link rel="stylesheet" type="text/css" href="/Erasmus/src/css/icomoon.css"/>
<link rel="stylesheet" type="text/css" href="/Erasmus/src/css/datepicker.css"/>
<link type="text/css" rel="stylesheet" href="/Erasmus/src/stylesheets/style.css"/>
<link type="text/css" rel="stylesheet" href="/Erasmus/src/css/jqtree.css"/>
<link type="text/css" rel="stylesheet" href="/Erasmus/src/css/ng-mobile-menu.css"/>
<link rel="stylesheet" type="text/css" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css">

<!-- included fonts 
 <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
-->
<!-- included javascript libraries 
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.0.7/angular.js"></script>-->
<script type='text/javascript' src='//code.jquery.com/jquery-1.9.1.js'></script>
  
<script type="text/javascript" src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
<script type='text/javascript' src="http://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.2/jquery.ui.touch-punch.min.js"></script>

<script src="/Erasmus/src/js/lib/jquery.mjs.nestedSortable.js"></script>

<script type="text/javascript" src="/Erasmus/src/js/lib/angular.js"></script>
<script type="text/javascript" src="/Erasmus/src/js/lib/ui-bootstrap-custom-tpls-0.4.0.js"></script>
<script type="text/javascript" src="/Erasmus/src/js/lib/jquery-1.10.0.min.js"></script>
<script type="text/javascript" src="/Erasmus/src/js/lib/tree.jquery.js"></script>
<script type="text/javascript" src="/Erasmus/src/js/lib/jquery-ui-1.10.3.custom.min.js"></script>
<script type="text/javascript" src="/Erasmus/src/js/lib/bootstrap.js"></script>
<script type="text/javascript" src="/Erasmus/src/js/lib/jquery.cookie.js"></script>
<script type="text/javascript" src="/Erasmus/src/js/lib/jquery.hotkeys.js"></script>
<script type="text/javascript" src="/Erasmus/src/js/lib/ng-mobile-menu.js"></script>
<script type="text/javascript" src="/Erasmus/src/js/convenienceFunctions.js"></script>



<!-- user activation placeholder jQuery script for changing view state of row in users table -->

<script type="text/javascript"> 

$(document).ready(function(){  

	$(document.body).on("click", ".activateRow", function(){
	     $(this).text('Deactivate').addClass('deactivateRow btn-danger').removeClass('activateRow btn-success');
	     $(this).parent().parent().removeClass('error');
	 });
	
	$(document.body).on("click", ".deactivateRow", function(){
	    $(this).text('Activate').removeClass('deactivateRow btn-danger').addClass('activateRow btn-success');
	    $(this).parent().parent().addClass('error');
	    console.log($(this).parent().parent());
 	});
/*
	$(document.body).on("click", ".modalUl", function(){
		console.log($(this).offset());
		var topOffset = $(this).offset().top;
		console.log(topOffset);
		var child = $(this).find("ul.modalUl");
		child.offset({ top: topOffset });
		$(this).offset({ top: topOffset });
		$(this).find('li:first-child').offset({ top: topOffset }).css( "position", "absolute" );
		
	})
*/
/*
	$(window).resize(function() {
		console.log($(window).width());
    });
*/
 })
</script>
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
<!-- main navigation -->


<div class="container-fluid " id="wrapper" >

<div class="navbar" id="nav">
  <div class="navbar-inner">
    <a class=" siteTitle brand" href="/Erasmus/src/views/RSMScenter.php">Research Safety Management System</a>
    <ul class="nav">
      <li class="divider-vertical"></li>
      <li><a href="#">Link</a></li>
      <li class="divider-vertical"></li>
      <li><a href="#">Link</a></li>
    </ul>
  </div>
</div>
<!-- 
	<div class="header">
		<h1 class="siteTitle text-shadow center" title="Research Safety Management System">Research Safety Management System</h1>
	</div>	
-->

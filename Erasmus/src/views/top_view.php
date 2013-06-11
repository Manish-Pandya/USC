<!DOCTYPE html>
<html lang="en">
<head>
<!-- stylesheets -->
<link type="text/css" rel="stylesheet" href="/Erasmus/src/css/bootstrap.css"/>
<link type="text/css" rel="stylesheet" href="/Erasmus/src/css/bootstrap-responsive.css"/>
<link type="text/css" rel="stylesheet" href="/Erasmus/src/css/styles.css"/>

<!-- included fonts -->
 <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>

<!-- included javascript libraries -->
<script type="text/javascript" src="/Erasmus/src/js/lib/jquery-1.10.0.min.js"></script>
<script type="text/javascript" src="/Erasmus/src/js/lib/bootstrap.js"></script>
<script type="text/javascript" src="/Erasmus/src/js/lib/jquery.cookie.js"></script>
<script type="text/javascript" src="/Erasmus/src/js/lib/jquery.hotkeys.js"></script>
<script type="text/javascript" src="/Erasmus/src/js/lib/jquery.jstree.js"></script>


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

 })
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

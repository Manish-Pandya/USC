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

<?php 
session_start();
if(!isset($_SESSION["USER"])){ ?>
<script>
//make sure the user is signed in, if not redirect them to the login page, but save the location they attempted to reach so we can send them there after authentication
//if javascript is enabled, we can capture the full url, including the hash
    var pathArray = window.location.pathname.split( '/' );
    var attemptedPath = "";
    for (i = 0; i < pathArray.length; i++) {
        if(i != 0)attemptedPath += "/";
        attemptedPath += pathArray[i];
    }
    attemptedPath = window.location.protocol + "//" + window.location.host + attemptedPath + window.location.hash;
    //remove the # and replace with %23, the HTTP espace for #, so it makes it to the server
    attemptedPath = attemptedPath.replace("#","%23");
    prepareRedirect(attemptedPath);
    function prepareRedirect(attemptedPath) {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
               if(xmlhttp.status == 200){
                  // alert("Please sign in to view the requested page.  Once you're signed in, you'll be redirected to the page you were trying to reach.");
                   window.location = "<?php echo LOGIN_PAGE;?>";
               }
               else if(xmlhttp.status == 400) {
                  alert('There was an error 400')
               }
               else {
                   alert('something else other than 200 was returned')
               }
            }
        }

        xmlhttp.open("GET", "../ajaxaction.php?action=prepareRedirect&redirect="+attemptedPath, true);
        xmlhttp.send();
    }
</script>
<?php
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- stylesheets
	<link type="text/css" rel="stylesheet" href="../css/bootstrap.css"/>
	<link type="text/css" rel="stylesheet" href="../css/bootstrap-responsive.css"/>
	<link type="text/css" rel="stylesheet" href="../css/bootmetro.css"/>
    <link rel="stylesheet" type="text/css" href="../css/bootmetro-tiles.css" />
    <link rel="stylesheet" type="text/css" href="../css/bootmetro-charms.css" />
    <link rel="stylesheet" type="text/css" href="../css/metro-ui-light.css" />
	<link rel="stylesheet" type="text/css" href="../css/icomoon.css"/>
	<link rel="stylesheet" type="text/css" href="../css/datepicker.css"/>
	<link type="text/css" rel="stylesheet" href="../css/font-awesome.min.css"/>

 -->
    <link href="../css/10-18-2017-manual-bundle.min.css" rel="stylesheet" />
	<link type="text/css" rel="stylesheet" href="../css/angular-busy.css">
	<link type="text/css" rel="stylesheet" href="../css/select.min.css"/>
    <link type="text/css" rel="stylesheet" href="../stylesheets/style.css" />

    <link type="text/css" rel="stylesheet" href="../stylesheets/ibc-styles.css" />

<!-- included fonts
 <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
-->
<!-- included javascript libraries
-->
	<script type="text/javascript" src="../js/lib/moment.js"></script>
    <script src="../js/lib/lodash.4.17.3/content/Scripts/lodash.min.js"></script>
	<script src="../js/lib/jQuery.3.1.1/Content/Scripts/jquery-3.1.1.min.js"></script>
	<script src="../js/lib/promise.min.js"></script>

	<script type="text/javascript" src="../js/constants.js"></script>

	<script type="text/javascript" src="../js/lib/angular.js"></script>
	<script src="../js/lib/angular-route.min.js"></script>
	<script type="text/javascript" src="../js/lib/ui-bootstrap-custom-tpls-0.4.0.js"></script>
	<script type="text/javascript" src="../js/convenienceMethodsModule.js"></script>
	<script type="text/javascript" src="../js/lib/ng-quick-date.js"></script>
	<script type="text/javascript" src="../js/lib/angular-once.js"></script>
    <script src="../js/lib/angular.filter.js"></script>
    <script src="../js/lib/tinymce.js"></script>
	<script type="text/javascript" src="../js/modalPosition.js"></script>
	<script type="text/javascript" src="../js/lib/angular-busy.min.js"></script>
	<script type="text/javascript" src="../js/lib/angular-ui-router.min.js"></script>
	<script type="text/javascript" src="../js/lib/cycle.js"></script>
	<script type="text/javascript" src="../js/lib/select.min.js"></script>
	<script type="text/javascript" src="../js/lib/angular-sanitize.min.js"></script>
    <script src="../js/roleBased.js"></script>


<!-- Required for the ORM framework -->
<!-- TODO include everything in certain directories by default -->

<!-- app -->
	<script type="text/javascript" src="scripts/app.js"></script>


<!-- business logic-->
<!--script type="text/javascript" src="scripts/actionFunctions.js"><script>-->

<!-- controllers -->
	<script src="scripts/controllers/IBCCtrl.js"></script>
	<script src="scripts/controllers/IBCDetailCtrl.js"></script>
	<script src="scripts/controllers/IBCEmailsCtrl.js"></script>

<!-- directives -->
<!--script type="text/javascript" src="./scripts/directives/dateInput.js"><script>-->


<!-- filters -->
<!--script type="text/javascript" src="../client-side-framework/filters/dateToIso.js"></!--script>-->
<script src="scripts/filters/IBCFilters.js"></script>

<!-- directives -->
<script src="scripts/directives/collapsibleCard.js"></script>

<!-- framework -->
    <script src="../ignorasmus/client-side-framework/DataStoreManager.js"></script>
    <script src="../ignorasmus/client-side-framework/InstanceFactory.js"></script>
    <script src="../ignorasmus/client-side-framework/UrlMapping.js"></script>
    <script src="../ignorasmus/client-side-framework/XHR.js"></script>


<!-- models -->
    <script src="../ignorasmus/client-side-framework/models/FluxCompositerBase.js"></script>
    <script src="scripts/models/Department.js"></script>
    <script src="scripts/models/Hazard.js"></script>
    <script src="scripts/models/PrincipalInvestigator.js"></script>
    <script src="scripts/models/Role.js"></script>
    <script src="scripts/models/Room.js"></script>
    <script src="scripts/models/User.js"></script>
    <script src="scripts/models/IBCAnswer.js"></script>
    <script src="scripts/models/IBCProtocol.js"></script>
    <script src="scripts/models/IBCProtocolRevision.js"></script>
    <script src="scripts/models/IBCQuestion.js"></script>
    <script src="scripts/models/IBCSection.js"></script>

</head>
<body>

<div ng-app="ng-IBC" ng-controller="AppCtrl" class="container-fluid">
<div cg-busy="{promise:loading,message:'Loading...',templateUrl:'../busy-templates/full-page-busy.html'}"></div>
<!-- NAVIGATION -->
  <div class="banner {{bannerClass}} radiation" ng-class="{'dashboard-banner':dashboardView, 'hide': noHead}">
    <h1>{{viewLabel}} <a style="float:right;margin: 11px 128px 0 0; color:black" href="../views/RSMSCenter.php#/safety-programs"><i class="icon-home" style="font-size:40px;"></i></a></h1>
  </div>
<!-- VIEW NESTING -->
    <div ui-view class="noBg ibc"></div>
</div>
</body>
</html>

<?php
if(stristr($_SERVER['REQUEST_URI'],'/RSMScenter')){
    require_once('../Application.php');
}elseif(stristr($_SERVER['REQUEST_URI'],'/login')){
    require_once('Application.php');
}else{
    require_once('../Application.php');
}
session_start();

echo '<script type="text/javascript">
var isProductionServer;';
if($_SERVER['HTTP_HOST'] != 'erasmus.graysail.com'){
  echo 'isProductionServer = true;';
}
echo "</script>";

?>
<?php if(!isset($_SESSION["USER"])){ ?>
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

        xmlhttp.open("GET", "<?php echo WEB_ROOT?>ajaxaction.php?action=prepareRedirect&redirect="+attemptedPath, true);
        xmlhttp.send();
    }
</script>
<?php
      }
?>

<!-- init authenticated user's role before we even mess with angular so that we can store the roles in a global var -->
<?php if($_SESSION != NULL){?>
<script>
    var GLOBAL_SESSION_ROLES = <?php echo json_encode($_SESSION['ROLE']); ?>;
    //grab usable properties from the session user object
    var GLOBAL_SESSION_USER = {
        Name:    '<?php echo $_SESSION['USER']->getName(); ?>',
        Key_id: '<?php echo $_SESSION['USER']->getKey_id(); ?>'
    }
    var GLOBAL_WEB_ROOT = '<?php echo WEB_ROOT?>';
</script>
<?php } ?>

</script>
<!DOCTYPE html>
<html lang="en">
<head>
<!-- stylesheets -->
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap-responsive.css"/>
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
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/select.min.css"/>

<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/angular-busy.css">
<link type="text/css" rel="stylesheet" href="stylesheets/verification-styles.css">

<!-- included fonts
 <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
-->
<!-- included javascript libraries
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.0.7/angular.js"></script>-->
<script type='text/javascript' src='<?php echo WEB_ROOT?>js/lib/jquery-1.9.1.js'></script>

<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/jquery-ui.js"></script>

<script src="<?php echo WEB_ROOT?>js/lib/jquery.mjs.nestedSortable.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/scrollDisabler.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular.js"></script>
<script src="<?php echo WEB_ROOT?>js/lib/angular-route.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ui-bootstrap-custom-tpls-0.4.0.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/jquery-1.10.0.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/jquery-ui-1.10.3.custom.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ng-mobile-menu.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/constants.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/convenienceMethodsModule.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ng-quick-date.js"></script>
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
<!-- framework -->
<script src="../client-side-framework/genericModel/inheritance.js"></script>
<script src="../client-side-framework/genericModel/genericModel.js"></script>
<script src="../client-side-framework/genericModel/genericPrincipalInvestigator.js"></script>
<script src="../client-side-framework/genericModel/genericAPI.js"></script>
<script src="../client-side-framework/genericModel/modelInflator.js"></script>
<script src="../client-side-framework/genericModel/urlMapper.js"></script>
<script src="./scripts/verificationUrlMapper.js"></script>
<script src="../client-side-framework/dataStore/dataStore.js"></script>
<script src="../client-side-framework/dataStore/dataStoreManager.js"></script>
<script src="../client-side-framework/dataStore/dataSwitch.js"></script>
<script src="../client-side-framework/dataStore/dataLoader.js"></script>
<script src="../client-side-framework/filters/splitAtPeriod.js"></script>


<!-- app -->
<script type="text/javascript" src="./scripts/app.js"></script>

<!-- business logic-->
<script type="text/javascript" src="../client-side-framework/rootApplicationController.js"></script>
<script type="text/javascript" src="./scripts/applicationController.js"></script>
<script type="text/javascript" src="../js/locationHub.js"></script>
    <script type="text/javascript" src="../js/userHub.js"></script>

<!-- controllers -->
<script type="text/javascript" src="../client-side-framework/genericModalController.js"></script>
<script type="text/javascript" src="scripts/controllers/personnelCtrl.js"></script>
<script type="text/javascript" src="scripts/controllers/emergencyPhoneCtrl.js"></script>
<script type="text/javascript" src="scripts/controllers/locationCtrl.js"></script>
<script type="text/javascript" src="scripts/controllers/confirmationCtrl.js"></script>
<script type="text/javascript" src="scripts/controllers/adminCtrl.js"></script>
<script type="text/javascript" src="scripts/controllers/hazardVerificationCtrl.js"></script>
<script type="text/javascript" src="scripts/controllers/AdminListCtrl.js"></script>


<!-- models -->
<script type="text/javascript" src="scripts/models/Verification.js"></script>
<script type="text/javascript" src="scripts/models/PrincipalInvestigator.js"></script>
<script type="text/javascript" src="scripts/models/PendingChange.js"></script>
<script type="text/javascript" src="scripts/models/PendingUserChange.js"></script>
<script type="text/javascript" src="scripts/models/PendingRoomChange.js"></script>
<script type="text/javascript" src="scripts/models/PendingHazardChange.js"></script>
<script type="text/javascript" src="scripts/models/User.js"></script>
<script type="text/javascript" src="scripts/models/Room.js"></script>
<script type="text/javascript" src="scripts/models/Building.js"></script>
<script type="text/javascript" src="scripts/models/HazardDto.js"></script>

<script type="text/javascript" src="../js/lib/phone-format.js"></script>

<!-- filters -->
<script src="scripts/filters/verificationFilters.js"></script>

</head>
<body>
    
<div ng-app="VerificationApp" ng-controller="NavCtrl" class="container-fluid">
<div cg-busy="{promise:loading,message:'Loading...',templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
<div cg-busy="{promise:saving,message:'Loading...',templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>

<!-- VIEW NESTING -->
    <div ui-view class="noBg"></div>
</div>

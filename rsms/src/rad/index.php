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

        xmlhttp.open("GET", "<?php echo WEB_ROOT?>ajaxaction.php?action=prepareRedirect&redirect="+attemptedPath, true);
        xmlhttp.send();
    }
</script>
<?php
      }
?>
<!-- init authenticated user's role before we even mess with angular so that we can store the roles in a global var -->
<?php if($_SESSION["USER"] != null){ ?>
<script>
    var GLOBAL_SESSION_ROLES = <?php echo json_encode($_SESSION['ROLE']); ?>;
    //grab usable properties from the session user object
    var GLOBAL_SESSION_USER = {
        Name:    '<?php echo $_SESSION['USER']->getName(); ?>',
        Key_id: '<?php echo $_SESSION['USER']->getKey_id(); ?>'
    }
    var GLOBAL_WEB_ROOT = '<?php echo WEB_ROOT?>';
    var isProductionServer;
<?php
          if($_SERVER['HTTP_HOST'] != 'erasmus.graysail.com'){
              echo 'isProductionServer = true;';
          }
      }
?>
</script
<!DOCTYPE html>
<html lang="en">
<head>
<!-- stylesheets -->
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap-responsive.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootmetro.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/bootmetro-tiles.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/metro-ui-light.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/icomoon.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/datepicker.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/font-awesome.min.css"/>

<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/ng-mobile-menu.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/angular-busy.css">
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/rad-styles.css">
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/select.min.css"/>

<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/style.css" />
<link href="../js/lib/ng-quick-date/ng-quick-date.css" rel="stylesheet" />

<!-- included fonts
 <link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
-->
<!-- included javascript libraries
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.0.7/angular.js"></script>-->
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/moment.js"></script>
<script src="../js/lib/lodash.min.js"></script>
<script type='text/javascript' src='<?php echo WEB_ROOT?>js/lib/jquery-1.9.1.js'></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/jquery-ui.js"></script>
<!--
<script type='text/javascript' src="http://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.2/jquery.ui.touch-punch.min.js"></script>
-->

<script type="text/javascript" src="<?php echo WEB_ROOT?>js/constants.js"></script>

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
<script type="text/javascript" src="./scripts/radConvenienceFunctions.js"></script>
<script type="text/javascript" src="./scripts/validationFunctions.js"></script>
<script src="scripts/controllers/admin/rad-admin-ctrls.js"></script>
<script src="scripts/controllers/pi/rad-pi-ctrls.js"></script>
<script type="text/javascript" src="./scripts/controllers/inspection/inspectionWipeCtrl.js"></script>

<!-- directives -->
<script type="text/javascript" src="./scripts/directives/dateInput.js"></script>
<script type="text/javascript" src="./scripts/directives/combobox.js"></script>
<script type="text/javascript" src="scripts/directives/piAuths.js"></script>
<script type="text/javascript" src="scripts/directives/container.js"></script>



<!-- filters -->
<script type="text/javascript" src="../client-side-framework/filters/filtersApp.js"></script>
<script type="text/javascript" src="./scripts/filters/radFilters.js"></script>
<script type="text/javascript" src="../js/lib/angular.filter.js"></script>

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
    <script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/tinymce.js"></script>

<!-- models 
<script src="./scripts/models/Authorization.js"></script>
<script src="./scripts/models/Carboy.js"></script>
<script src="./scripts/models/CarboyUseCycle.js"></script>
<script src="./scripts/models/CarboyReadingAmount.js"></script>
<script src="./scripts/models/Drum.js"></script>
<script src="./scripts/models/Hazard.js"></script>
<script src="./scripts/models/Isotope.js"></script>
<script src="./scripts/models/Parcel.js"></script>
<script src="./scripts/models/ParcelUse.js"></script>
<script src="./scripts/models/ParcelUseAmount.js"></script>
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
<script src="./scripts/models/PIAuthorization.js"></script>
<script src="./scripts/models/PIWipeTest.js"></script>
<script src="./scripts/models/PIWipe.js"></script>
<script src="scripts/models/DrumWipe.js"></script>
<script src="scripts/models/DrumWipeTest.js"></script>
<script src="scripts/models/MiscellaneousWaste.js"></script>
    -->
<script src="scripts/models/rad-models-bundle.js"></script>
    <style>
        ul.piNav {
            margin-right: 160px;
            float: right;
            margin-top: -30px;
        }
            ul.piNav li {
                display:inline-block;
                margin-right:10px;
            }
                ul.piNav li a {
                    font-weight: bold;
                    font-size: 12px;
                    color: rgba(51, 51, 51, 0.7);
                    display: block;
                }
                    ul.piNav li a:hover {
                        color: black;
                    }
    </style>
</head>
    <body>
        <?php if($_SESSION['USER'] != NULL){ ?>
        <div class="user-info" ng-controller="roleBasedCtrl">
            <div>
                Signed in as <?php echo $_SESSION['USER']->getName(); ?>
                <a style="float:right;" href="<?php echo WEB_ROOT?>action.php?action=logoutAction">Sign Out</a>
            </div>
        </div>
        <?php }?>
        <div ng-app="00RsmsAngularOrmApp" ng-controller="NavCtrl" class="container-fluid" style="margin-top:25px;">
            <div cg-busy="{promise:loading,message:'Loading...',templateUrl:'views/busy-templates/full-page-busy.html'}"></div>
            <div cg-busy="{promise:saving,message:'Saving...',templateUrl:'views/busy-templates/full-page-busy.html'}"></div>
        <!-- NAVIGATION -->
        <div class="banner {{bannerClass | splitAtPeriod}} radiation no-print" ng-class="{'dashboard-banner':dashboardView, 'hide': noHead}">
            <h1>{{viewLabel}} <a style="float:right;margin: 11px 128px 0 0; color:black" ui-sref="rad-home()"><i class="icon-home" style="font-size:40px;"></i></a></h1>
            <ul class="piNav" ng-if="showPiNav">
                <li><a ui-sref="pi-orders({ pi: navPi })">Orders</a></li>
                <li><a ui-sref="use-log({ pi: navPi})">Use Logs</a></li>
                <li><a ui-sref="pickups({ pi: navPi})">Pickups</a></li>
                <li><a ui-sref="containers({ pi:navPi})">Waste Containers</a></li>
                <li><a ui-sref="current-inventories({ pi:navPi})">Inventory</a></li>
                <li><a ui-sref="lab-wipes({ pi: navPi})">Wipe Tests</a></li>
                <li><a ui-sref="pi-auths({ pi: navPi})">Authorizations</a></li>
                <li>|</li>
                <li><a ui-sref="pi-rad-management({ pi: navPi })">My Lab</a></li>
            </ul>
        </div>
        <!-- VIEW NESTING -->
        <div ui-view class="noBg"></div>
        </div>
    </body>
</html>

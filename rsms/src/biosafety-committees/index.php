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

    <!-- init authenticated user's role before we even mess with angular so that we can store the roles in a global var -->
    <?php if($_SESSION != NULL){?>
        <script>
            var GLOBAL_SESSION_ROLES = <?php echo json_encode($_SESSION['ROLE']); ?>;
            //grab usable properties from the session user object
            var GLOBAL_SESSION_USER = {
                Name: '<?php echo $_SESSION['USER']->getName(); ?>',
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
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootstrap-responsive.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/bootmetro.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/bootmetro-tiles.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/bootmetro-charms.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/metro-ui-light.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/icomoon.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo WEB_ROOT?>css/datepicker.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/style.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/jqtree.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/font-awesome.min.css" />

    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/ng-mobile-menu.css" />
    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/select.min.css" />

    <link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>css/angular-busy.css">
    <link type="text/css" rel="stylesheet" href="stylesheets/hazard-inventory-styles.css">

    <!-- included fonts
<link href='http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700' rel='stylesheet' type='text/css'>
-->
    <!-- included javascript libraries
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/angularjs/1.0.7/angular.js"></script>-->
    <script type='text/javascript' src='<?php echo WEB_ROOT?>js/lib/jquery-1.9.1.js'></script>

    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-route.min.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ui-bootstrap-custom-tpls-0.4.0.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/jquery-1.10.0.min.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ng-mobile-menu.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/constants.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/convenienceMethodsModule.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ng-quick-date.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular-once.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/modalPosition.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular-busy.min.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular-ui-router.min.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ui-mask.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/select.min.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular-sanitize.min.js"></script>
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/roleBased.js"></script>

    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular.filter.js"></script>

    <!-- Required for the ORM framework -->
    <!-- framework -->
    <script src="../client-side-framework/genericModel/inheritance.js"></script>
    <script src="../client-side-framework/genericModel/genericModel.js"></script>
    <script src="../client-side-framework/genericModel/genericPrincipalInvestigator.js"></script>
    <script src="../client-side-framework/genericModel/genericAPI.js"></script>
    <script src="../client-side-framework/genericModel/modelInflator.js"></script>
    <script src="../client-side-framework/genericModel/urlMapper.js"></script>
    <script src="./scripts/biosafetyCommitteesUrlMapper.js"></script>
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

    <!-- controllers -->
    <script type="text/javascript" src="../client-side-framework/genericModalController.js"></script>
    <script type="text/javascript" src="./scripts/controllers/biosafetyCommitteesCtrl.js"></script>


    <!-- models -->
    <script type="text/javascript" src="scripts/models/Hazard.js"></script>
    <script type="text/javascript" src="scripts/models/PrincipalInvestigator.js"></script>
    <script type="text/javascript" src="scripts/models/Department.js"></script>
    <script type="text/javascript" src="scripts/models/BiosafetyProtocol.js"></script>


    <!-- filters -->
</head>

<body>
    <?php if($_SESSION['USER'] != NULL){ ?>
    <div class="user-info">
        <div>
            Signed in as <?php echo $_SESSION['USER']->getName(); ?>
            <a style="float:right;" href="<?php echo WEB_ROOT?>action.php?action=logoutAction">Sign Out</a>
        </div>
    </div>
    <?php }?>

    <div ng-app="BiosafetyCommittees" ng-controller="BiosafetyCommitteesCtrl" class="container-fluid" style="margin-top:25px;">

        <div cg-busy="{promise:hazardPromise,message:'Loading Hazards',templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:pisPromise,message:'Loading Principal Investigators',templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:HazardDtoSaving,message:'Saving',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:PIHazardRoomDtoSaving,message:'Saving',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:PrincipalInvestigatorSaving,message:'Saving',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:RoomSaving,message:'Saving',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:InspectionSaving,message:'Saving',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>


        <div class="navbar">
            <ul class="nav pageMenu row-fluid redBg">
                <li class="span12">
                    <h2 style="padding: 11px 0 5px 0; font-weight:bold; text-align:center">
                        <img src="../img/hazard-icon.png"  style="height:50px" />
                        Institutional Biosafety Committee
                        <a style="float:right;margin: 11px 28px 0 0;" href="<?php echo WEB_ROOT?>views/RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>
                    </h2>
                </li>
            </ul>
        </div>
        <div class="whiteBg" style="min-height:2000px;">

        </div>
    </div>777u
</body>

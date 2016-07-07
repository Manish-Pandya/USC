<?php
if(stristr($_SERVER['REQUEST_URI'],'/RSMScenter')){
    require_once('/Application.php');
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
                Name: '<?php echo $_SESSION['USER']->getName(); ?>',
                Key_id: '<?php echo $_SESSION['USER']->getKey_id(); ?>',
                Inspector_id: '<?php echo $_SESSION['USER']->getInspector_id(); ?>',
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
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/jquery.hoverIntent.minified.js"></script>
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
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/lodash.min.js"></script>


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

    <!-- directives -->
    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/poptop.js"></script>

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

        <div cg-busy="{promise:init,message:'Loading',templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:BiosafetyProtocolSaving,message:'Saving',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:PIHazardRoomDtoSaving,message:'Saving',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:uploadDocument,message:'Uploading Protocol',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>

        <div class="navbar">
            <ul class="nav pageMenu row-fluid redBg">
                <li class="span12">
                    <h2 style="padding: 11px 0 5px 0; font-weight:bold; text-align:center">
                        Institutional Biosafety Committees
                        <a style="float:right;margin: 11px 28px 0 0;" href="<?php echo WEB_ROOT?>views/RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>
                    </h2>
                </li>
            </ul>
        </div>
        <div class="whiteBg" style="min-height:2000px;">
            <h1>Biosafety Protocols <a class="btn btn-success btn-large left" ng-click="openProtocolModal()"><i class="icon-plus-2"></i>Add</a></h1>
            <h2 ng-if="!protocols">No Protocols saved</h2>
            <table class="table table-striped table-hover table-bordered" ng-if="protocols" style="margin-top:10px;">
                <tr>
                    <th>Edit</th>
                    <th>Protocol #</th>
                    <th>Investigator <input class="span2" ng-model="search.pi" placeholder="Filter by PI"/> </th>
                    <th>Department <input class="span2" ng-model="search.department" placeholder="Filter by Department"/> </th>
                    <th>Project Title</th>
                    <th>Approved</th>
                    <th>Expires</th>
                    <th>Hazard <br><input class="span2" ng-model="search.hazard" placeholder="Filter by Hazard"/> </th>
                    <th>Protocol</th>
                </tr>
                <tr ng-repeat="protocol in protocols | genericFilter:search | orderBy: 'PrincipalInvestigator.User.Name'" ng-class="{'inactive':!protocol.Is_active}">
                    <td style="width:7%">
                        <a class="btn btn-primary" ng-click="openProtocolModal(protocol)"><i class="icon-pencil"></i></a>
                        <a ng-if="protocol.Is_active" ng-click="af.setObjectActiveState(protocol)" class="btn btn-danger"><i class="icon-remove"></i></a>
                        <a ng-if="!protocol.Is_active" ng-click="af.setObjectActiveState(protocol)" class="btn btn-success"><i class="icon-checkmark"></i></a>
                    </td>
                    <td style="width:10%">{{protocol.Protocol_number}}</td>
                    <td style="width:13%">{{protocol.PrincipalInvestigator.User.Name}}</td>
                    <td style="width:15%">{{protocol.Department.Name}}</td>
                    <td style="width:13%">                                
                        <poptop content="protocol.Project_title" label="Project Title" title="Project Title" event="click"/>
                    </td>
                    <td style="width:8%">{{protocol.Approval_date | dateToISO:protocol:'Approval_date':true}}</td>
                    <td style="width:7%">{{protocol.Expiration_date | dateToISO:protocol:'Expiration_date':true}}</td>
                    <td style="padding:10px 0;width:18%">
                        <ul>
                            <li ng-repeat="hazard in constants.PROTOCOL_HAZARDS" ng-if="protocol.Hazards.indexOf(hazard.Name) > -1">{{hazard.Name}}</li>
                        </ul>
                    </td>
                    <td style="width:9%">
                        <a class="btn btn-large btn-success left view-report" ng-if="protocol.Report_path" href="protocol-documents/{{protocol.Report_path}}" target="_blank"><strong><i class="icon-paper-2"></i>View</strong></a>
                        <span ng-if="!protocol.Report_path">N/A</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

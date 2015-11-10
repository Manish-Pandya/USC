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
echo "</script>";
if($_SERVER['HTTP_HOST'] != 'erasmus.graysail.com'){
  echo 'isProductionServer = true;';
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
<script src="./scripts/hazardInventoryUrlMapper.js"></script>
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
<script type="text/javascript" src="./scripts/controllers/hazardInventoryCtrl.js"></script>


<!-- models -->
<script type="text/javascript" src="scripts/models/HazardDto.js"></script>


<!-- filters -->
<script type="text/javascript" src="scripts/filters/isEquipmentFilter.js"></script>
</head>
<body>

<div ng-app="HazardInventory" ng-controller="HazardInventoryCtrl" class="container-fluid">
<div cg-busy="{promise:loading,message:'Loading...',templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
     <div class="navbar">
        <ul class="nav pageMenu row-fluid redBg">
            <li class="span12">
                <h2 style="padding: 11px 0 5px 0; font-weight:bold; text-align:center">
                <img src="../img/hazard-icon.png"  style="height:50px" />
                Laboratory Hazards & Equipment Inventory
                <a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>
            </h2>
            </li>
        </ul>
    </div>
    <div id="editPiForm" class="row-fluid">
        <form class="form">
            <div class="control-group span4">
                <label class="control-label" for="name">
                       <h3>Principal Investigator</h3>
                </label>
                <div class="controls">
                    <span ng-if="!PIs">
                        <input class="span8" style="background:white;border-color:#999"  type="text"  placeholder="Getting PIs..." disabled="disabled">
                        <i class="icon-spinnery-dealie spinner small" style="margin:-6px 0 0 -30px"></i>
                   </span>
                    <span ng-if="PIs">
                        <ui-select ng-if="!PI || selectPI" ng-model="pi.selected" theme="selectize" ng-disabled="disabled" on-select="onSelectPi($item)" class="span8" >
                            <ui-select-match placeholder="Select or search for a PI">{{$select.selected.User.Name}}</ui-select-match>
                            <ui-select-choices repeat="pi in PIs | orderBy:'User.Name' |propsFilter: {User.Name: $select.search}">
                              <div ng-bind-html="pi.User.Name | highlight: $select.search"></div>
                            </ui-select-choices>
                        </ui-select>
                        <h3 style="display:inline" ng-if="PI && !selectPI">{{PI.User.Name}}</h3>
                        <span ng-click="selectPI = !selectPI">
                            <i  ng-if="PI && !selectPI" style="margin: -1px 2px;" class="icon-pencil primary"></i>
                            <i class="icon-cancel danger" ng-if="PI && selectPI" ng-click="selectPI = !selectPI" style="margin: 6px 5px;"></i>
                        </span>
                    </span>
                </div>
                <h3 style="display:block; width:100%; margin-top:12px;" ng-if="!selectPI && PI"><a class="btn btn-info" href="../hubs/PIHub.php#/rooms?pi={{PI.Key_id}}&inspection=true">Manage Data for Selected PI</a></h3>
            </div>
                    <div class="span8" ng-if="PI || pi">
                        <div class="controls">
                            <h3 class="span6">Building(s):</h3>
                            <h3 class="span6">
       Laboratory Rooms:
       </h3>
                            <span ng-if="!buildings.length">
                   <p ng-if="!noRoomsAssigned" style="display: inline-block; margin-top:5px;">
                       Select a Principal Investigator.
                   </p>
                    <P ng-if="noRoomsAssigned" style="display: inline-block; margin-top:5px;">
                    <span once-text="PI.User.Name"></span> has no rooms <a class="btn btn-info" once-href="'../hubs/PIHub.php#/rooms?pi='+PI.Key_id'&inspection=true">Add Rooms</a>
                            </p>
                            </span>

                            <span ng-if="buildings && PI">
                   <ul class="selectedBuildings">
                       <li ng-repeat="(key, building) in buildings">
                       <div class="span6">
                           <h4 ><!--<a class="btn btn-danger btn-mini" style="margin-right:5px;"><i class="icon-cancel-2" ng-click="removeBuilding(building)"></i></a>-->{{building.Name}}</h4>
                       </div>
                       <div class="roomsForBuidling span6">
                           <ul>
                               <li ng-repeat="(key, room) in building.Rooms | orderBy: 'Name'"><a ng-if="room.HasMultiplePIs" ng-click="openMultiplePIsModal(room)">{{room.Name}}</a><span ng-if="!room.HasMultiplePIs">{{room.Name}}</span></li>
                            </ul>
                        </div>
                        </li>
                        </ul>
                        </span>
                    </div>
            </div>
        </form>
    </div>
    <ul class="allHazardList">
        <li class="hazardList" ng-class="{narrow: hazard.hidden}" data-ng-repeat="hazard in hazard.ActiveSubHazards | orderBy: 'Name'" ng-if="hazard.Hazard_name != 'General Safety'">
            <h1 class="hazardListHeader" once-id="'hazardListHeader'+hazard.Key_id" ng-if="hazard.hidden" ng-click="hazard.hidden = !hazard.hidden">&nbsp;</h1>
            <span ng-if="!hazard.hidden">
                <h1 ng-click="hazard.hidden = !hazard.hidden" class="hazardListHeader" once-id="'hazardListHeader'+hazard.Key_id">
                    <span ng-if="hazard.Hazard_name == 'Biological Safety'">Biological Hazards</span><span ng-if="hazard.Hazard_name == 'Chemical/Physical Safety' || hazard.Hazard_name == 'Chemical and Physical Safety'">Chemical/Physical Hazards</span><span ng-if="hazard.Hazard_name == 'Radiation Safety'">Radiation Hazards</span>
                </h1>
            </span>
            <hr>
            <ul class="topChildren" ng-init="hazard.loadSubhazards()">
                <li>
                    <a style="margin-bottom:15px;" class="btn btn-mini btn-info" ng-click="hazard.hideUnselected = !hazard.hideUnselected">
                        <span ng-if="!hazard.hideUnselected">
                            <i style="margin-right:8px !important;" class="icon-collapse"></i>View Only Hazards Present
                        </span>
                        <span ng-if="hazard.hideUnselected">
                            <i style="margin-right:8px !important;" class="icon-full-screen"></i>View All Hazard Categories
                        </span>
                    </a>
                </li>
                <li ng-class="{'yellowed': child.Status == 'Stored Only'}" ng-repeat="(key, child) in hazard.ActiveSubHazards | filter: {Is_equipment: false} | orderBy: 'Hazard_name'" class="hazardLi topChild" id="id-{{hazard.Key_Id}}" ng-if="child.IsPresent || !hazard.hideUnselected">
                    <label class="checkbox inline">
                        <input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
                        <span class="metro-checkbox"></span>
                        <!--<pre>{{child | json}}</pre>-->
                    </label>
                    <span style="font-size: 14px;font-weight: normal;line-height: 20px;">
                        <span class="metro-checkbox targetHaz" ng-if="!room.HasMultiplePIs">
                            {{child.Hazard_name}}
                            <span ng-if="child.Status == 'Stored Only'" class="stored">(Stored Only)</span>
                        </span>
                        <img ng-if="child.IsDirty" class="smallLoading" src="../../img/loading.gif" />
                    </span>
                    <!--</h4>-->
                    <div class="icons">
                        <span ng-if="child.ActiveSubHazards.length || child.HasChildren && child.IsPresent ">
                            <i class="icon-plus-2 modal-trigger-plus-2" ng-click="openSubsModal(child)"></i>
                        </span>
                        <span ng-if="child.IsPresent">
                            <i class="icon-pencil primary" ng-click="openRoomsModal(child)"></i>
                        </span>

                        <span ng-if="child.IsPresent">
                            <i class="icon-info" ng-click="openMultiplePIsModal(child)"></i>
                        </span>
                    </div>
                    <ul ng-if="getShowRooms(child)" class="subRooms">
                        <li>Rooms:</li>
                        <li ng-repeat="(key, room) in child.InspectionRooms | filter: {ContainsHazard: true}" class="" ng-class="{'last':$last}">
                            <a ng-if="room.HasMultiplePIs" ng-click="openMultiplePIsModal(room)">{{room.Name}}</a><span ng-if="!room.HasMultiplePIs">{{room.Name}}</span>
                        </li>
                    </ul>
                    <ul>
                        <li ng-repeat="child in child.ActiveSubHazards" ng-if="child.IsPresent" class="hazardLi" id="id-{{child.Key_Id}}">
                            <span ng-init="child.loadSubHazards()" data-ng-include="'views/sub-hazard.html'"></span>
                        </li>
                    </ul>
                </li>
            </ul>
        </li>
    </ul>
</div>

<?php
if(stristr($_SERVER['REQUEST_URI'],'/RSMScenter')){
    require_once('../Application.php');
}elseif(stristr($_SERVER['REQUEST_URI'],'/login')){
    require_once('Application.php');
}else{
    require_once('../Application.php');
}
session_start();

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
    <?php if($_SESSION != NULL){
        $am = new ActionManager();
        $r = $am->getCurrentUserRoles();
        ?>
        <script>
            var GLOBAL_SESSION_ROLES = <?php echo json_encode($_SESSION['ROLE']); ?>;
            //grab usable properties from the session user object
            var GLOBAL_SESSION_USER = {
                Name: '<?php echo $_SESSION['USER']->getName(); ?>',
                Key_id: '<?php echo $_SESSION['USER']->getKey_id(); ?>',
                Inspector_id: '<?php echo $_SESSION['USER']->getInspector_id(); ?>',
                Roles: '<?php echo json_encode($r["userRoles"]);?>'
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

    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/jquery-ui.js"></script>

    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/angular-route.min.js"></script>

    <script src="../js/lib/lodash.min.js"></script>

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

    <script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/angular.filter.js"></script>

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
    <script src="../client-side-framework/filters/filtersApp.js"></script>


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
    <script type="text/javascript" src="scripts/models/PIHazardRoomDto.js"></script>
    <script type="text/javascript" src="scripts/models/PrincipalInvestigator.js"></script>
    <script type="text/javascript" src="scripts/models/User.js"></script>
    <script type="text/javascript" src="scripts/models/Inspector.js"></script>

    <!-- filters -->
    <script type="text/javascript" src="scripts/filters/hazardInventoryFilters.js"></script>
    <script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
    <script src="<?php echo WEB_ROOT?>js/lib/tinymce.js"></script>
</head>

<body>
    <?php require('../views/user_info_bar.php'); ?>

    <div ng-app="HazardInventory" ng-controller="HazardInventoryCtrl" class="container-fluid" style="margin-top:25px;">

        <div cg-busy="{promise:piPromise,message:'Loading Principal Investigator Details',templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:hazardPromise,message:'Loading Hazards',templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:pisPromise,message:'Loading Principal Investigators',templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:HazardDtoSaving,message:'Saving',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:PIHazardRoomDtoSaving,message:'Saving',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:PrincipalInvestigatorSaving,message:'Saving',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:RoomSaving,message:'Saving',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:InspectionSaving,message:'Saving',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>
        <div cg-busy="{promise:loadingCabs, message:'Loading Cabinets',backdrop:true,templateUrl:'../client-side-framework/busy-templates/full-page-busy.html'}"></div>


        <div class="navbar">
            <ul class="nav pageMenu row-fluid redBg">
                <li class="span12">
                    <h2 style="padding: 11px 0 5px 0; font-weight:bold; text-align:center">
                        <img src="../img/hazard-icon.png"  style="height:50px" />
                        Laboratory Hazards & Equipment Inventory
                        <a style="float:right;margin: 11px 28px 0 0;" href="<?php echo WEB_ROOT?>"><i class="icon-home" style="font-size:40px;"></i></a>
                    </h2>
                </li>
            </ul>
        </div>
        <div class="whiteBg" style="min-height:2000px;">
            <div id="editPiForm" class="row-fluid">
                <form class="form">
                    <div class="control-group span4">
                        <label class="control-label" for="name">
                            <h3 style="height:34px; text-decoration:underline">Principal Investigator</h3>
                        </label>
                        <div class="controls">
                            <span ng-if="!PIs">
            <input class="span8" style="background:white;border-color:#999"  type="text"  placeholder="Getting PIs..." disabled="disabled">
            <i class="icon-spinnery-dealie spinner small" style="margin:-6px 0 0 -30px"></i>
       </span>
        <span ng-if="PIs">
            <ui-select ng-if="!PI || af.selectPI" ng-model="pi.selected" theme="selectize" ng-disabled="disabled" on-select="af.selectPI = false; onSelectPi(pi.selected)" class="span8" >
                <ui-select-match placeholder="Select or search for a PI">{{$select.selected.Name}}</ui-select-match>
                <ui-select-choices repeat="pi in PIs | orderBy:'Name' | propsFilter: {Name: $select.search}">
                  <div ng-class="{'red':!pi.Is_active}" ng-bind-html="pi.Name | highlight: $select.search"></div>
                </ui-select-choices>
            </ui-select>
            <h3 style="display:inline" ng-class="{'red':!PI.Is_active}" ng-if="PI && !af.selectPI">{{PI.Name}} {{!PI.Is_active ? "(Inactive PI)" : ""}}</h3>
            <span ng-click="af.selectPI = !af.selectPI">
                <i ng-if="PI && !af.selectPI" style="margin: -1px 2px;" class="icon-pencil primary"></i>
                <i class="icon-cancel danger" ng-if="PI && af.selectPI"  style="margin: 6px 5px;"></i>
            </span>
        </span>
                        </div>
                        <h3 style="display:block; width:100%; margin-top:12px;" ng-if="!af.selectPI && PI"><a class="btn btn-info" href="<?php echo WEB_ROOT?>views/hubs/PIHub.php#/rooms?pi={{PI.Key_id}}&inspection=true">Manage Data for Selected PI</a></h3>
                    </div>
                    <div class="span8" ng-if="PI || pi">
                        <div class="controls">
                            <h3 class="span6">Building(s):<a style="margin-left:10px; margin-top:-3px" class="btn btn-primary btn-mini left" ng-click="filterRooms()"><i class="icon-search" style="margin: 0 4px 0 0 !important; font-size: 13px !important;"></i>Filter</a></h3>
                            <h3 class="span6">Laboratory Rooms:</h3>
                            <span ng-if="!PI.Rooms">
                                <p ng-if="!noRoomsAssigned" style="display: inline-block; margin-top:5px;">
                                    Select a Principal Investigator.
                                </p>
                                <p ng-if="noRoomsAssigned" style="display: inline-block; margin-top:5px;">
                                    <span once-text="PI.Name"></span> has no rooms <a class="btn btn-info" once-href="'<?php echo WEB_ROOT?>views/hubs/PIHub.php#/rooms?pi='+PI.Key_id'&inspection=true">Add Rooms</a>
                                </p>
                            </span>

                            <span ng-if="PI.Rooms">
                               <ul class="selectedBuildings">
                                   <li ng-repeat="(key, building) in PI.Rooms | groupBy: 'Building.Name'" >
                                       <div class="span6">
                                           <h4 ng-class="{'grayed-out': getGrayed(building)}">{{key}}</h4>
                                       </div>
                                       <div class="roomsForBuidling span6">
                                           <ul>
                                               <li ng-repeat="(key, room) in rooms = (building | activeOnly | orderBy: convenienceMethods.sortAlphaNum('Name'))" ng-class="{'grayed-out': selectedRoomIds.indexOf(room.Key_id) == -1 }"><a ng-if="room.HasMultiplePIs" ng-click="openMultiplePIsModal(null,room)">{{room.Name}}</a><span ng-if="!room.HasMultiplePIs">{{room.Name}}</span></li>
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                            </span>
                        </div>
                    </div>
            </form>
        </div>
        <div class="key" ng-if="hazard">
            <h4 class="other"><i class="icon-users"></i>Used by other PI(s)</h4>
            <h4 class="shared"><i class="icon-users"></i>Used by this PI and other PI(s)</h4>
            <h4 class="stored"><i class="icon-box"></i>Stored only by this PI</h4>
            <h4 class="other"><i class="icon-box"></i>Stored only by other PI(s)</h4>
        </div>
        <ul class="allHazardList">
            <li class="hazardList" ng-class="{narrow: hazard.hidden}" data-ng-repeat="hazard in hazard.ActiveSubHazards | displayableHazards | orderBy: 'Name'">
                <h1 class="hazardListHeader" once-id="'hazardListHeader'+hazard.Key_id" ng-if="hazard.hidden" ng-click="hazard.hidden = !hazard.hidden">&nbsp;</h1>
                <span ng-if="!hazard.hidden">
                <h1 ng-click="hazard.hidden = !hazard.hidden" class="hazardListHeader" once-id="'hazardListHeader'+hazard.Key_id">
                    <span>{{hazard.Hazard_name}}</span>
                </h1>
                </span>
                <hr ng-if="!hazard.hidden">
                <ul ng-if="!hazard.hidden" class="topChildren" ng-init="hazard.loadSubhazards()">
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
                    <li ng-class="{'yellowed': child.Stored_only}" ng-repeat="(key, child) in hazard.ActiveSubHazards | filter: {Is_equipment: false} | orderBy: 'Order_index'" class="hazardLi topChild" id="id-{{hazard.Key_Id}}" ng-if="child.IsPresent || !hazard.hideUnselected">
                        <label class="checkbox inline">
                            <input type="checkbox" ng-model="child.IsPresent" ng-disabled="getDisabled(child)" ng-change="af.handleHazardChecked(child, hazard)" />
                            <span class="metro-checkbox"></span>
                        </label>
                        <span style="font-size: 14px;font-weight: normal;line-height: 20px;">
                            <span class="metro-checkbox targetHaz" ng-if="!room.HasMultiplePIs">
                                {{child.Hazard_name}}
                            </span>
                        </span>
                        <div class="icons">
                            <span ng-if="child.ActiveSubHazards.length && child.IsPresent ">
                                <i class="icon-plus-2 modal-trigger-plus-2" ng-click="openSubsModal(child, hazard)"></i>
                            </span>
                            <span ng-if="child.IsPresent">
                                <i class="icon-pencil primary" ng-click="openRoomsModal(child)"></i>
                            </span>
                            <span ng-if="child.Stored_only" ng-class="{'stored':child.IsPresent, 'other':child.BelongsToOtherPI}"><i class="icon-box"></i></span>
                            <span ng-if="child.BelongsToOtherPI || (child.IsPresent && child.HasMultiplePis)" ng-class="{'other':child.BelongsToOtherPI  && !child.IsPresent, 'shared':child.IsPresent && child.HasMultiplePis}">
                                <i class="icon-users" ng-click="openMultiplePIHazardsModal(child)"></i>
                            </span>
                        </div>
                        <ul class="subRooms hazInvSubRooms" ng-if="getShowRooms(child, room, key)" ng-repeat="(key, rooms) in child.InspectionRooms | groupBy: 'Building_name'">
                            <li>
                                <span ng-show="relevantRooms.length">{{ key }}:</span>
                                <span ng-repeat="room in relevantRooms = ( rooms | relevantRooms | orderBy: convenienceMethods.sortAlphaNum('Room_name'))">

                                    <a ng-click="openMultiplePIHazardsModal(child, room)" ng-class="{'other':room.OtherLab && !room.ContainsHazard, 'shared':room.OtherLab && room.ContainsHazard, 'stored':room.Stored}">
                                        {{ room.Room_name }}
                                        <span ng-if="room.HasMultiplePIs || room.OtherLab"><i class="icon-users" title="{{child.Hazard_name}} is used by more than one lab in room {{room.Room_name}}"></i></span>
                                        <span ng-if="room.Stored" class="stored">
                                            <i class="icon-box" title="{{child.Hazard_name}} is stored by this lab in room {{room.Room_name}}"></i>
                                        </span>
                                    </a>
                                    <span style="margin-right: 1px;margin-left: -4px;" ng-if="!$last">, </span>

                                </span>
                             </li>
                        </ul>
                        <ul>
                            <li ng-class="{'yellowed': child.Stored_only || child.storedOnly}" ng-repeat="child in child.ActiveSubHazards | orderBy: 'Order_index'" ng-if="child.IsPresent || child.BelongsToOtherPI || child.Stored_only" ng-init="child.loadActiveSubHazards()" id="id-{{child.Hazard_id}}" class="hazardLi"><span data-ng-include="'views/sub-hazard.html'"></span></li>
                        </ul>
                    </li>
                </ul>
                <!-- EQUIPMENT LIST HERE -->
                <br ng-if="!hazard.hidden" />
                <br ng-if="!hazard.hidden" />
                <br ng-if="!hazard.hidden" />
                <h1 ng-if="!hazard.hidden" ng-class="{narrow: hazard.hidden}" class="hazardListHeader" once-id="'hazardListHeader'+hazard.Key_id" style="margin-bottom:-12px;">
                    <span once-text="hazard | hazardEquipmentHeaderName"></span>
                </h1>
                <hr style="margin-bottom:4px;" ng-if="!hazard.hidden">
                <ul ng-if="!hazard.hidden" class="topChildren equipment-list" ng-init="hazard.loadSubhazards()">
                    <li ng-class="{'yellowed': child.Stored_only}" ng-repeat="(key, child) in hazard.ActiveSubHazards | filter: {Is_equipment: true} | orderBy: 'Order_index'" class="hazardLi topChild" id="id-{{hazard.Key_Id}}" ng-if="child.IsPresent || !hazard.hideUnselected">
                        <label class="checkbox inline">
                            <input type="checkbox" ng-model="child.IsPresent" ng-disabled="isHazardBiosafetyCabinets(child) || getDisabled(child)" ng-change="af.handleHazardChecked(child, hazard)" />
                            <span class="metro-checkbox"></span>
                        </label>
                        <span style="font-size: 14px;font-weight: normal;line-height: 20px;">
                            <span class="metro-checkbox targetHaz" ng-if="!room.HasMultiplePIs">
                                {{child.Hazard_name}}
                            </span>
                        </span>
                        <!--</h4>-->
                        <div class="icons">
                            <span ng-if="child.ActiveSubHazards.length && child.IsPresent ">
                                <i class="icon-plus-2 modal-trigger-plus-2" ng-click="openSubsModal(child, hazard)"></i>
                            </span>
                            <span ng-if="child.IsPresent">
                                <i class="icon-pencil primary" ng-click="openRoomsModal(child)"></i>
                            </span>
                            <span ng-if="child.Stored_only" ng-class="{'stored':child.IsPresent, 'other':child.BelongsToOtherPI}"><i class="icon-box"></i></span>
                            <span ng-if="child.BelongsToOtherPI || (child.IsPresent && child.HasMultiplePis)" ng-class="{'other':child.BelongsToOtherPI  && !child.IsPresent, 'shared':child.IsPresent && child.HasMultiplePis}">
                                <i class="icon-users" ng-click="openMultiplePIHazardsModal(child)"></i>
                            </span>
                            <span ng-if="child.IsPresent && isHazardBiosafetyCabinets(child)" 
                                  ng-class="{'other':child.BelongsToOtherPI  && !child.IsPresent, 
                                  'shared':child.IsPresent && child.HasMultiplePis}">
                                <i class="icon-info" ng-click="openBiosafetyCabinetInfoModal(PI)"></i>
                            </span>
                        </div>
                        <ul class="subRooms hazInvSubRooms" ng-if="getShowRooms(child, room, key)" ng-repeat="(key, rooms) in child.InspectionRooms | groupBy: 'Building_name'">
                            <li>
                                <span ng-show="relevantRooms.length">{{ key }}:</span>
                                <span ng-repeat="room in relevantRooms = ( rooms | relevantRooms)">

                                    <a ng-click="openMultiplePIHazardsModal(child, room)" ng-class="{'other':room.OtherLab && !room.ContainsHazard, 'shared':room.OtherLab && room.ContainsHazard, 'stored':room.Stored}">
                                        {{ room.Room_name }}
                                        <span ng-if="room.HasMultiplePIs || room.OtherLab"><i class="icon-users" title="{{child.Hazard_name}} is used by more than one lab in room {{room.Room_name}}"></i></span>
                                        <span ng-if="room.Stored" class="stored">
                                            <i class="icon-box" title="{{child.Hazard_name}} is stored by this lab in room {{room.Room_name}}"></i>
                                        </span>
                                    </a>
                                    <span style="margin-right: 1px;margin-left: -4px;" ng-if="!$last">, </span>

                                </span>
                            </li>
                            </ul>
                            <ul>
                                <li ng-class="{'yellowed': child.Stored_only || child.storedOnly}" ng-repeat="child in child.ActiveSubHazards" ng-if="child.IsPresent" ng-init="child.loadActiveSubHazards()" id="id-{{child.Hazard_id}}" class="hazardLi"><span data-ng-include="'views/sub-hazard.html'"></span></li>
                            </ul>

                    </li>
                </ul>
            </li>
        </ul>
        <div id="footer" style="position:fixed; bottom:0; width:100%; background:white; left:0; z-index:1040; box-shadow:0 0 20px rgba(0,0,0,.5)" ng-if="PI">
            <ul class="container-fluid whitebg" style="padding:0 0 0 70px !Important">
                <li>
                    <a ng-click="openPreviousInspections()"><img src="../img/clipboard.png" /><span>Archived Reports</span></a>
                </li>
                <li>
                    <a href="<?php echo WEB_ROOT?>views/hubs/PIHub.php#/personnel?pi={{PI.Key_id}}&inspection=true" target="_blank"><img src="../img/phone.png" /><span>Laboratory Personnel</span></a>
                </li>
                <li>
                    <a ng-click="openNotes()"><img src="../img/speechBubble.png" /><span>Inspection Comments</span></a>
                </li>
                <li>
                    <a ng-click="startInspection()"><img src="../img/checkmarkFooter.png" /><span>Inspect Labs</a></span>
                </li>
            </ul>
        </div>
    </div>
    </div>
</body>

<?php
require_once '../top_view.php';
?>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/piHub.js"></script>

<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>user-hub/user-hub-styles.css" />
<script src="<?php echo WEB_ROOT?>user-hub/scripts/UserHubApp.js"></script>

<script>
    var GLOBAL_WEB_ROOT = '<?php echo WEB_ROOT?>';

    var RoleRequirements = <?php
        $rules = new UserCategoryRules();
        echo JsonManager::encode( $rules->getUserCategoryRules() );
    ?>;

    // Dynamically apply the hub theme to the body so that modal(s) are styled
    (function(){
        $('body').addClass('hub-theme-blue');
    })();
</script>

<script src="<?php echo WEB_ROOT?>js/lib/angular-once.js"></script>
<script src="<?php echo WEB_ROOT?>js/lib/angular.filter.js"></script>
<script src="<?php echo WEB_ROOT?>js/lib/angular-ui-router.min.js"></script>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/lib/ui-mask.js"></script>

<span class="hub-theme-blue" ng-app="piHub" ng-controller="piHubMainController">
<div cg-busy="{promise:userPromise,message:'Loading...',templateUrl:'../../rad/views/busy-templates/full-page-busy.html'}"></div>
<div cg-busy="{promise:saving,message:'Saving...',templateUrl:'../../rad/views/busy-templates/full-page-busy.html'}"></div>

<div class="navbar">
<ul class="nav pageMenu bg-color-blue" style="min-height: 50px; background: #86b32d; color:white !important; padding: 4px 0 0 0; width:100%">
    <li class="span3" style="margin-left:0">
        <img src="<?php echo WEB_ROOT?>img/pi-icon.png" class="pull-left" style="height: 67px;margin-top: -11px;" />
            <h2 style="padding: 11px 0 5px 15px; margin-left:63px;">PI Hub
            <a style="float:right;margin: 11px 28px 0 0;" href="<?php echo WEB_ROOT;?>"><i class="icon-home" style="font-size:40px;"></i></a>
        </h2>
    </li>
    <div style="clear:both; height:0; font-size:0; ">&nbsp;</div>
</ul>
<div class="whitebg" style="padding:70px 70px;">
    <div id="editPiForm" class="">
        <form class="form">
             <div class="control-group">
               <label class="control-label" for="name"><h3 style="font-weight:bold">Select A Principal Investigator</h3></label>
               <div class="controls">
               <span ng-if="!PIs || !buildings">
                    <input class="span4" style="background:white;border-color:#999"  type="text"  placeholder="Getting PIs..." disabled="disabled">
                       <i class="icon-spinnery-dealie spinner small asbolute" style="margin-left:-258px; margin-top:-5px;"></i>
               </span>
               <span ng-if="PIs && buildings" class="span12 nopad no-pad" style="margin-left:0">
                   <span class="span4 nopad no-pad" style="display:block; margin-left:0">
                       <ui-select ng-model="pi.selected" theme="selectize" ng-disabled="disabled" on-select="onSelectPi($item)">
                           <ui-select-match placeholder="Select or search for a PI">{{$select.selected.Name}}</ui-select-match>
                           <ui-select-choices repeat="pi in PIs | propsFilter: {Name: $select.search}">
                               <div ng-bind-html="pi.Name | highlight: $select.search"></div>
                           </ui-select-choices>
                       </ui-select>
                   </span>
                   <br /><br />
                   <ul ng-if="PI && PI.Departments" class="no-list" style="margin-left:0; display:block; width:100%">
                       <li style="display:inline-block; float:left"><h2 class="bold underline">Department:</h2></li>
                       <li style="display:inline-block; float:left; margin-left:5px;" ng-repeat="dept in PI.Departments"><h2 class="bold" style="height:auto">{{dept.Name}}<span ng-if="!$last">,</span></h2></li>
                   </ul>
               </span>
              </div>
             </div>
        </form>
    </div>
    <span ng-if="PI">
        <div class="btn-group" id="piButtons" style="">
            <a ng-click="setRoute('rooms')" id="editPI" class="btn btn-large btn-info left" style="margin-left:0"><i class="icon-enter"></i>PI's Laboratory Rooms</a>
            <a ng-click="setRoute('personnel')" class="btn btn-large btn-success left"><i class="icon-user-2"></i>Manage Lab Personnel</a>
            <a ng-if="inspectionId" class="btn btn-large btn-danger left" href="../../hazard-inventory/#?pi={{PI.Key_id}}">Return To Inspection</a>
        </div>
    </span>

    <ng-view></ng-view>


<?php
require_once '../bottom_view.php';
?>

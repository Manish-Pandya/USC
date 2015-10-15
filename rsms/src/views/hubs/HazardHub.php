<?php
    require_once '../top_view.php';
?>
<!--ng-if="rbf.getHasPermission([ R[Constants.ROLE.NAME.ADMIN], R[Constants.ROLE.NAME.SAFETY_INSPECTOR], R[Constants.ROLE.NAME.RADIATION_ADMIN], R[Constants.ROLE.NAME.RADIATION_INSPECTOR] ])" -->
<script src="../../js/HazardHub.js"></script>
<span ng-app="hazardHub" ng-controller="TreeController">
<div class="navbar">
<ul class="nav pageMenu" style="background: #e67e1d;">
    <li class="">
        <img src="../../img/hazard-icon.png" class="pull-left" style="height:50px" />
        <h2  style="padding: 11px 0 5px 30px;">Hazard Hub
            <a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>
        </h2>
    </li>
</ul>

</div><!-- ui-nested-sortable-stop="update($event, $ui)"
                  ui-nested-sortable-begin="start($event, $ui)"-->

<div class="whitebg" >
    <div>
    <div>
    <div ng-hide="doneLoading" class="container loading" style="margin-left:0px; margin-top:15px;">
      <img class="" src="../../img/loading.gif"/>
      Building Hazard List...
    </div>
    <div class="alert alert-danger" ng-if="error">
      <h1>{{error}}</h1>
    </div>
        <div class="live" ng-hide="!SubHazards.length">
          <select ng-model="hazardFilterSetting.Is_active" style="margin:21px 19px 0;" ng-init="hazardFilterSetting.Is_active = 'active'">
            <option value="active">Display Active Hazards</option>
            <option value="inactive">Display Inactive Hazards</option>
            <option value="both">Display Active & Inactive Hazards</option>
          </select>
          <ol id="hazardTree" style="padding-top:0">
            <li ng-repeat="child in SubHazards | orderBy:['Order_index','Name']" id="hazard{{child.Key_id}}" ng-class="{minimized:child.minimized, inactive: child.Is_active == false, lastSub: child.lastSub == true}" ng-init="child.minimized=true"  buttonGroup>
              <span ng-include src="'hazard-hub-partial.html'" autoscroll></span>
            </li>
          </ol>
        </div>
</span>
<?php
require_once '../bottom_view.php';
?>

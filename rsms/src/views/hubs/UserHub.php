<?php
require_once '../top_view.php';
?>
<script src="../../js/userHub.js"></script>

<div class="navbar fixed">
    <ul class="nav pageMenu" style="min-height: 50px; background: #51a351; color:white !important; padding: 4px 0 0 0; width:100%">
        <li class="">
            <img src="../../img/user-icon.png" class="pull-left" style="height:50px" />
            <h2  style="padding: 11px 0 5px 85px;">User Hub
                <a style="float:right;margin: 11px 28px 0 0;" href="<?php echo WEB_ROOT;?>"><i class="icon-home" style="font-size:40px;"></i></a>
            </h2>
        </li>
    </ul>
    <div class="clearfix"></div>
</div>

<span ng-app="userList"  ng-controller="MainUserListController" style="clear:both; display: block; margin-top:120px">
    <form class="form-horizontal fixed" style="margin: -42px 0 0;background: rgb(221, 221, 221);z-index: 1047;">
      <div class="control-group" style="display:inline-block;">
         <label class="control-label" for="route" style="font-weight:bold; text-align: left; width:auto;">Select User Type:</label>
         <div class="controls" style="margin-left:128px;">
            <select ng-model="selectedRoute" ng-change="setRoute()" id="route">
              <option ng-repeat="view in userHubViews" ng-value="view.route" ng-selected="selectedRoute == view.route">{{view.name}}</option>
           </select>
         </div>
      </div>
      <div class="control-group pull-right" style="display:inline-block;">
        <a class="btn btn-info left" ng-click="openUserLookupModal()">
          Find a User<i class="icon-magnifying-glass"></i>
        </a>
      </div>

    </form>

    <div class="loading" ng-if="!neededUsers" style="z-index:1070; position:absolute">
      <i class="icon-spinnery-dealie spinner large"></i>
      <span>Loading Users...</span>
    </div>
   <h2 class="alert alert-danger fixed" ng-if="error">{{error}}</h2>
   <ng-view></ng-view>
</span>

<?php
require_once '../bottom_view.php';
?>

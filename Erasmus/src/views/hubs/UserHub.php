<?php
require_once '../top_view.php';
?>
<script src="../../js/userHub.js"></script>

<div class="navbar">
	<ul class="nav pageMenu" style="min-height: 50px; background: #51a351; color:white !important; padding: 4px 0 0 0; width:100%">
		<li class="">
			<img src="../../img/user-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">User Hub
				<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
			</h2>	
		</li>
	</ul>
	<div class="clearfix"></div>
</div>

<span ng-app="userList" ng-controller="MainUserListController" style="clear:both">
    <form class="form-horizontal" style="margin: 10px 0 0;">
      <div class="control-group">
         <label class="control-label" for="route" style="font-weight:bold; text-align: left; width:auto;">Select User Type:</label>
         <div class="controls" style="margin-left:128px;">
            <select ng-model="selectedRoute" ng-change="setRoute()" id="route">
			  <option value="/pis">Principal Investigators</option>
			  <option value="/contacts">Laboratory Contacts</option>
			  <option value="/EHSPersonnel">EHS Personnel</option>
  			  <option value="/uncategorized">Uncategorized Users</option>

		   </select>
         </div>
      </div>
    </form>

	<div class="loading" ng-if="!neededUsers">
	  <i class="icon-spinnery-dealie spinner large"></i> 
	  <span>Loading Users...</span>
	</div>
   <h2 class="alert alert-danger" ng-if="error">{{error}}</h2>
   <ng-view></ng-view>

</span>

<?php
require_once '../bottom_view.php';
?>
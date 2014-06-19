<?php
require_once '../top_view.php';
?>
<script src="../../js/users.js"></script>

<div class="navbar">
	<ul class="nav pageMenu" style="min-height: 50px; background: #51a351; color:white !important; padding: 4px 0 0 0; width:100%">
		<li class="">
			<img src="../../img/user-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">User Hub
				<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
			</h2>	
		</li>
	</ul>
</div>

<span ng-app="userList" ng-controller="MainUserListController">
    <form class="form-horizontal" style="margin: 71px 0 -11px -38px;">
      <div class="control-group">
         <label class="control-label" for="route" style="font-weight:bold;">Select User Type:</label>
         <div class="controls">
            <select ng-model="selectedRoute" ng-change="setRoute()" id="route">
			  <option value="/pis">Principal Investigators</option>
			  <option value="/contacts">Laboratory Contacts</option>
			  <option value="/EHSPersonnel">EHS Personnel</option>
		   </select>
         </div>
      </div>
    </form>

   <span class="loading" ng-if="!LabContacts || !pis || !Admins">
		  <img class="" src="<?php echo WEB_ROOT?>img/loading.gif"/>
		  Getting Users...
    </span>
 
   <ng-view></ng-view>

</span>

<?php
require_once '../bottom_view.php';
?>
<?php
require_once '../top_view.php';
?>
<script src="../../js/users.js"></script>

<div class="navbar">
<ul class="nav pageMenu" style="min-height: 50px; background: #51a351; color:white !important; padding: 4px 0 0 0; width:100%">
	<li class="span3">
		<img src="../../img/user-icon.png" class="pull-left" style="height:50px" />
		<h2  style="padding: 11px 0 5px 85px;">User Hub</h2>	
	</li>
	<li>
		
	</li>
</ul>

</div>
<span ng-app="userList" ng-controller="UserListController">
	
	<div class="btn-group" style="margin:15px 0">

     <a href="javascript:void(0);" class="btn btn-large" ng-init="{showInactive = false}" ng-click="showInactive = !showInactive" ng-class="{ 'btn-sinfo' : showInactive, 'btn-susccess': !showInactive }">
	     <span ng-show="showInactive"><i class="icon-minus-3" style="font-size:30px; width: 35px; font-size: 30px; margin-left:-9px"></i>Hide Inactive Users</span>
	     <span ng-hide="showInactive"><i class="icon-plus-3"  style="font-size:30px; width: 35px; font-size: 30px; margin-left:-9px"></i>Show Inactive Users</span>
     </a>
     <a class="btn-large btn btn" ng-click="addUser()" style="padding:10px; margin-left:5px;" data-toggle="modal"  href="#addUser"><img src='../../img/add-user-icon.png'>Add User</a>
   </div>


<table class="userList table table-striped table-hover" ng-class="{'hide-inactive-rows': !showInactive}">
<thead>
	<tr>
		<th>Edit User</th><th>Activate/Deactivate User</th><th>Name</th><th>LDAP ID</th><th>Email</th><th>Role</th>
	</tr>
</thead>

<tbody>
	<tr id="{{user.id}}" ng-repeat="user in users" ng-class="{edit: user.edit, notedit: user.notEdit, updated: user.updated, inactive: !user.IsActive}">
		<td ng-hide="user.edit"><a class="edit btn btn-large btn-primary" ng-click="editUser(user)">Edit</a></td><td ng-show="user.edit"><a class="edit btn btn-large btn-info" ng-click="saveUser(userCopy, user)">Save</a></td>
		<td ng-hide="user.edit">
			<a class="btn btn-danger btn-large DeactivateeRow" ng-click="handleUserActive(user)" ng-show="user.IsActive">Deactivate</a>
			<a class="btn btn-success btn-large DeactivateeRow" ng-click="handleUserActive(user)" ng-hide="user.IsActive">Activate</a>
		</td>

		<td ng-show="user.edit"><a class="edit btn btn-large btn-danger" ng-click="cancelEdits(user)">Cancel</a></td>
		

		<td ng-hide="user.edit">{{user.Name}}</td><td ng-show="user.edit"><input ng-model="userCopy.Name"/></td>
		<td ng-hide="user.edit">{{user.Username}}</td><td ng-show="user.edit"><input ng-model="userCopy.Username"/></td>
		<td ng-hide="user.edit">{{user.Email}}</td><td ng-show="user.edit"><input ng-model="userCopy.Email"/></td>
		<td ng-hide="user.edit">{{user.Roles}}Administrator</td><td ng-show="user.edit"><input ng-init="Administrator" ng-model="userCopy.Roles"/></td>
	</tr>
</tbody>
</table>

</span>

<?php
require_once '../bottom_view.php';
?>
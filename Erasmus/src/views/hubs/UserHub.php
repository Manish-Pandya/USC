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
<span ng-app="userList" ng-controller="UserListController" >
	
	<div class="btn-group" style="margin:15px 0">

     <a class="btn btn-large" ng-click="showInactive = !showInactive">
	     <span ng-show="showInactive"><i class="icon-minus-3" style="font-size:30px; width: 35px; font-size: 30px; margin-left:-9px"></i>Hide Inactive Users</span>
	     <span ng-hide="showInactive"><i class="icon-plus-3"  style="font-size:30px; width: 35px; font-size: 30px; margin-left:-9px"></i>Show Inactive Users</span>
     </a>
     <a class="btn-large btn btn" ng-click="addUser()" style="padding:10px; margin-left:5px;" href="#addUser"><img src='../../img/add-user-icon.png'>Add User</a>
   </div>

<!--“Filter by:” Department, Building--><!--, Department, Office Phone, Building,  Email, Emergency Phone-->
<table class="userList table table-striped table-hover piTable">
	<thead>
		<tr>
			<th colspan="7"><h1 class="blueBg">Principal Investigators</h1></td>
		</tr>
		<tr>
			<th>Edit User</th>
			<!--<th><a ng-click="order = 'Is_active'; reverse=!reverse">Activate/Deactivate User</a></th>-->
			<th><a ng-click="order = 'Name'; reverse=!reverse">Name</a></th>
			<th><a ng-click="order = 'Name'; reverse=!reverse">Department(s)</a></th>
			<th>Office Phone</th>
			<th><a ng-click="order = 'Building'; reverse=!reverse">Building(s)</a></th>
			<th><a ng-click="order = 'Email'; reverse=!reverse">Email</a></th>
			<th>Emergency Phone</th>
			<!--<th>Roles</th>-->
		</tr>
	</thead>
	<tbody>
		<tr ng-repeat="pi in pis | orderBy:order:reverse">

			<td>
				
				<a ng-hide="pi.edit" class="edit btn btn-large btn-primary" ng-click="addUser(pi)">Edit</a>
				<a ng-show="pi.edit" class="edit btn btn-large btn-info" ng-click="saveUser(userCopy, pi)">Save</a>
				<a ng-show="pi.edit" class="edit btn btn-large btn-danger" ng-click="cancelSave(userCopy, pi)">Cancel</a>

			<!--</td>

			<td ng-hide="user.edit">-->
			
				<a class="btn btn-danger btn-large DeactivateeRow" ng-click="handleUserActive(pi)" ng-if="pi.Is_active && !pi.edit">Deactivate</a>
				<a class="btn btn-success btn-large DeactivateeRow" ng-click="handleUserActive(pi)" ng-if="!pi.Is_active && !pi.edit">Activate</a>
				
			</td>

			<td>
				<img ng-show="pi.IsDirty" class="smallLoading" src="../../img/loading.gif" style="margin-left:-25px; margin-top:15px;"/>

				{{pi.User.Name}}
			</td>

			<td>
				<ul>
					<li ng-repeat="department in pi.Departments">{{department.Name}}</li>
				</ul>
			</td>

			<td>
				{{pi.User.Phone}}
			</td>

			<td>
				<ul>
					<li ng-repeat="building in pi.Buildings">{{building.Name}}</li>
				</ul>
			</td>

			<td>
				{{pi.User.Email}}
			</td>

			<td>
				{{pi.User.Emergency_phone}}
			</td>

		</tr>
	</tbody>
</table>

<!--Name, Lab PI, Department, Email, Lab Phone, Emergency Phone
-->
<table class="userList table table-striped table-hover piTable">
	<thead>
		<tr>
			<th colspan="7"><h1 class="greenBg">Lab Contacts</h1></td>
		</tr>
		<tr>
			<th>Edit User</th>
			<!--<th><a ng-click="order = 'Is_active'; reverse=!reverse">Activate/Deactivate User</a></th>-->
			<th><a ng-click="order = 'Name'; reverse=!reverse">Name</a></th>
			<th><a ng-click="order = 'Name'; reverse=!reverse">Lab PI</a></th>
			<th><a ng-click="order = 'Departments'; reverse=!reverse">Department(s)</a></th>
			<th>Lab Phone</th>
			<th><a ng-click="order = 'Email'; reverse=!reverse">Email</a></th>
			<th>Emergency Phone</th>
			<!--<th>Roles</th>-->
		</tr>
	</thead>
	<tbody>
		<tr ng-repeat="contact in LabContacts | orderBy:order:reverse">

			<td>
				<a ng-hide="contact.edit" class="edit btn btn-large btn-primary" ng-click="addUser(contact)">Edit</a>
				<a ng-show="contact.edit" class="edit btn btn-large btn-info" ng-click="saveUser(userCopy, contact)">Save</a>
				<a ng-show="contact.edit" class="edit btn btn-large btn-danger" ng-click="cancelSave(userCopy, contact)">Cancel</a>
			<!--</td>

			<td ng-hide="user.edit">-->

				<a class="btn btn-danger btn-large DeactivateeRow" ng-click="handleUserActive(contact)" ng-if="contact.Is_active && !contact.edit">Deactivate</a>
				<a class="btn btn-success btn-large DeactivateeRow" ng-click="handleUserActive(contact)" ng-if="!contact.Is_active && !contact.edit">Activate</a>
				
			</td>

			<td>
				<img ng-show="contact.IsDirty" class="smallLoading" src="../../img/loading.gif" style="margin-left:-25px; margin-top:15px;"/>
				{{contact.Name}}
			</td>

			<td>
				{{contact.Supervisor.User.Name}}
			</td>

			<td>
				<ul>
					<li ng-repeat="department in contact.Supervisor.Departments">{{department.Name}}</li>
				</ul>
			</td>

			<td>
				{{contact.Phone}}
			</td>

			<td>
				{{contact.Email}}
			</td>

			<td>
				{{contact.Emergency_phone}}
			</td>

		</tr>
	</tbody>
</table>


    <script type="text/ng-template" id="myModalContent.html">
        <div class="modal-header" style="padding:0;">
            <h2 style="padding:5px" ng-show="userCopy.Name" class="blueBg">Editing {{userCopy.Name}}</h2>
        	<h2 style="padding:5px" ng-hide="userCopy.Name" class="blueBg">Create a New User</h2>
        </div>
        <div class="modal-body">
        	<form class=" form">

        	<div class="control-group">
		         <label class="control-label" for="inputEmail">Name</label>
		         <div class="controls">
		            <input type="text" id="inputEmail" ng-model="userCopy.Name" placeholder="Name">
		         </div>
			    </div>

			    <div class="control-group" ng-show="userType.Name == 'Lab Contact'">
		         <label class="control-label" for="inputEmail">Lab PI</label>
		         <div class="controls">
		            <input style="" type="text"  ng-model="userCopy.Supervisor.User.Name" placeholder="Select PI" typeahead="pi as pi.User.Name for pi in pis | filter:$viewValue">
		         </div>
			    </div>

			    <div class="control-group">
		         <label class="control-label" for="inputEmail">Email</label>
		         <div class="controls">
		            <input type="text" id="inputEmail" ng-model="userCopy.Email" placeholder="Email">
		         </div>
			    </div>

			    <div class="control-group">
		         <label ng-show="userType == 'Lab Contact'" class="control-label" for="inputEmail">Lab Phone</label>
		         <label ng-hide="userType == 'Lab Contact'" class="control-label" for="inputEmail">Office Phone</label>
		         <div class="controls">
		            <input type="text" id="inputEmail" ng-model="userCopy.Phone" placeholder="Lab\Office Phone">
		         </div>
			    </div>

			    <div class="control-group">
		         <label class="control-label" for="inputEmail">Emergency Phone</label>
		         <div class="controls">
		            <input type="text" id="inputEmail" ng-model="userCopy.Emergency_phone" placeholder="Name">
		         </div>
			    </div>


			    <div class="well">
				    <div  class="control-group">
			         <label class="control-label" for="inputEmail">Roles(s)</label>
			         <div class="controls">
						<input style="" class="span4" placeholder="Select A Role"  typeahead-on-select='onSelectRole($item, $model, $label)' type="text" ng-model="selectedRole" typeahead="role as role.Name for role in roles | filter:$viewValue" ng-init="">
						<img ng-show="selectedDepartment.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
				    </div>

				    <ul>
				    	<li ng-repeat="role in userCopy.Roles"><a class="btn btn-danger btn-mini" ng-click="removeRole(role)"><i class="icon-cancel"></i></a>{{role.Name}}<img ng-show="role.IsDirty" class="smallLoading" src="../../img/loading.gif"/>			         </div>
						</li>
				    </ul>
			    </div>

			    <div class="well" ng-if="piCopy || userCopy.isPI">
				    <div  class="control-group">
			         <label class="control-label" for="inputEmail">Department(s)</label>
			         <div class="controls">
						<input style="" class="span4" placeholder="Select A Department"  typeahead-on-select='onSelectDepartment($item, $model, $label)' type="text" ng-model="selectedDepartment" typeahead="department as department.Name for department in departments | filter:$viewValue" ng-init="">
						<img ng-show="selectedDepartment.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
				    </div>

				    <ul>
				    	<li ng-repeat="department in piCopy.Departments"><a class="btn btn-danger btn-mini" ng-click="removeDepartment(department)"><i class="icon-cancel"></i></a>{{department.Name}}<img ng-show="department.IsDirty" class="smallLoading" src="../../img/loading.gif"/>			         </div>
</li>
				    </ul>
			    </div>


			    
        	</form>
           
        </div>
        <div class="modal-footer">
        	<img ng-show="userCopy.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
            <a ng-if="!pi && userCopy.Key_id" class="btn btn-success hazardBtn btn-large" ng-click="saveUser(userCopy, items[1])"><i class="icon-checkmark"></i>Save</a>
            <a ng-if="pi && userCopy.Key_id" class="btn btn-success hazardBtn btn-large" ng-click="saveUser(userCopy, piCopy)"><i class="icon-checkmark"></i>Save</a>
            <a ng-if="!userCopy.Key_id" class="btn btn-success hazardBtn btn-large" ng-click="saveNewUser(userCopy)"><i class="icon-checkmark"></i>Save</a>
            <a class="btn btn-danger hazardBtn btn-large" ng-click="cancel()"><i class="icon-cancel"></i>Cancel</a>
        </div>
     </script>

</span>

<?php
require_once '../bottom_view.php';
?>
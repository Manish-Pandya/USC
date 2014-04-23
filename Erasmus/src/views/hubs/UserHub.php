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
   productionserver: {{isProductionServer}}
   <ng-view></ng-view>

    <script type="text/ng-template" id="myModalContent.html">
        <div class="modal-header" style="padding:0;">
            <h2 style="padding:5px" ng-show="userCopy.Key_id" class="blueBg">Editing {{userCopy.Name}}</h2>
        	<h2 style="padding:5px" ng-hide="userCopy.Key_id" class="blueBg">Create a New User</h2>
        </div>
        <div class="modal-body">
        	<form class=" form">

        	<div class="control-group">
		         <label class="control-label" for="inputEmail">Name</label>
		         <div class="controls">
		            <input type="text" id="inputEmail" ng-model="userCopy.Name" placeholder="Name">
		         </div>
			    </div>

			    <div class="control-group">
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
		         <label class="control-label" for="inputEmail">Lab Phone</label>
		         <div class="controls">
		            <input type="text" id="inputEmail" ng-model="userCopy.Lab_phone" placeholder="Lab\Office Phone">
		         </div>
			    </div>

			    <div class="control-group">
		         <label class="control-label" for="inputEmail">Emergency Phone</label>
		         <div class="controls">
		            <input type="text" id="inputEmail" ng-model="userCopy.Emergency_phone" placeholder="Name">
		         </div>
			    </div>


			    <div class="" style="padding:3px 0;">
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

			    <div class=""style="padding:3px 0">
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
            <a ng-if="!userCopy.isPI && userCopy.Key_id" class="btn btn-success hazardBtn btn-large" ng-click="saveUser(userCopy, items[1])"><i class="icon-checkmark"></i>Save</a>
            <a ng-if="userCopy.isPI && userCopy.Key_id" class="btn btn-success hazardBtn btn-large" ng-click="saveUser(userCopy, piCopy)"><i class="icon-checkmark"></i>Save</a>
            <a ng-if="piCopy || userCopy.isPI && !userCopy.Key_id" class="btn btn-success hazardBtn btn-large" ng-click="saveUser(userCopy, piCopy)"><i class="icon-checkmark"></i>Save</a>
            <a ng-if="!pi && !userCopy.Key_id && !piCopy && !userCopy.isPI" class="btn btn-success hazardBtn btn-large" ng-click="saveNewUser(userCopy)"><i class="icon-checkmark"></i>Save</a>
            <a class="btn btn-danger hazardBtn btn-large" ng-click="cancel()"><i class="icon-cancel"></i>Cancel</a>
        </div>
     </script>

</span>

<?php
require_once '../bottom_view.php';
?>
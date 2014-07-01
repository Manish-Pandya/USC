<?php
require_once '../top_view.php';
?>
<script src="../../js/departmentHub.js"></script>

<div class="navbar">
	<ul class="nav pageMenu" style="min-height: 50px; background: #49afcd; color:white !important; padding: 4px 0 0 0; width:100%">
		<li class="">
			<i class="pull-left fa fa-university fa-4x" style="height:50px;margin: 4px 10px 0;"></i>
			<h2  style="padding: 11px 0 5px 85px;">Department Hub
				<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
			</h2>	
		</li>
	</ul>
</div>
<div class="whiteBg" ng-app="departmentHub" ng-controller="departmentHubController" style="padding-top:60px">
	<h2 class="alert alert-danger" ng-if="error">{{error}}</h2>
	<span ng-if="!departments && !error" class="loading">
	   <img style="width:100px"src="<?php echo WEB_ROOT?>img/loading.gif"/>
	  Loading Departments
	</span>
	<div class="span5 center-element center-text bottomMargin">
		<a ng-click="createDepartment()" class="btn btn-success btn-large bottomMargin" ng-if="!creatingDepartment && departments">Add New Department<i class="icon-plus-2 icon-right"></i></a>
		<span ng-if="newDepartment" style="width:100%; display: block;">
			<input style="width:50%" ng-model="newDepartment.Name">
			<span style="width:50%">
				<a class="btn-success btn" ng-click="saveDepartment(newDepartment)">Save<i class="icon-checkmark"></i></a>
				<a class="btn-danger btn" ng-click="cancelEdit(newDepartment)">Cancel<i class="icon-cancel"></i></a>
				<img ng-show="newDepartment.isDirty" class="smallLoading" src="../../img/loading.gif"/>
			</span>
		</span>
	</div>

	<table class="table table-striped center-element table-bordered span9 editTable" ng-if="departments">
		<THEAD>
			<tr>
				<th>Edit</th>
				<th>Departments</th>
			</tr>
		</THEAD>
		<tbody>
			<tr ng-repeat="(key, department) in departments" class="center-block" ng-class="{inactive:!department.Is_active}">
				<td>
					<a class="btn btn-primary" ng-click="editDepartment(department)">Edit<i class="icon-pencil"></i></a>
					<a ng-click="handleActive(department)" class="btn" ng-class="{'btn-danger':department.Is_active,'btn-success':!department.Is_active}">
						<span ng-if="department.Is_active"><i class="icon-remove"></i></span>
						<span ng-if="!department.Is_active"><i class="icon-checkmark-2"></i></span>
					</a>
					<img ng-show="department.isDirty && department.setActive" class="smallLoading" src="../../img/loading.gif"/>
				</td>
				<td>
					<span ng-if="!department.edit" >
						<span once-text="department.Name"></span>
					</span>
					<span ng-if="department.edit">
						<input style="width:100%" ng-model="departmentCopy.Name">
						<span class="absoluteBtns" ng-of="department.edit">
							<a class="btn-success btn" ng-click="saveDepartment(department)">Save<i class="icon-checkmark"></i></a>
							<a class="btn-danger btn" ng-click="cancelEdit(department)">Cancel<i class="icon-cancel"></i></a>
							<img ng-show="department.isDirty" class="smallLoading" src="../../img/loading.gif"/>
						</span>
					</span>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<?php
require_once '../bottom_view.php';
?>
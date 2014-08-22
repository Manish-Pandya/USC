<?php
require_once '../top_view.php';
?>
<script src="../../js/departmentHub.js"></script>

<div class="navbar">
	<ul class="nav pageMenu purpleBg" style="min-height: 50px; color:white !important; padding: 4px 0 0 0; width:100%">
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
		<a ng-click="createDepartment()" class="btn btn-success btn-large" ng-if="!creatingDepartment && departments"><i class="icon-plus-5 icon-right"></i>Add New Department</a>
		<span ng-if="creatingDepartment" style="width:100%; display: block;">
			<input style="width:50%" ng-model="departmentCopy.Name">
			<span style="width:50%">
				<a class="btn-success btn" ng-click="saveDepartment(departmentCopy)"><i class="icon-checkmark"></i>Save</a>
				<a class="btn-danger btn" ng-click="cancelEdit(departmentCopy)"><i class="icon-cancel"></i>Cancel</a>
				<img ng-show="departmentCopy.isDirty" class="smallLoading" src="../../img/loading.gif"/>
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
			<tr ng-repeat="(key, department) in departments | orderBy: 'Name'" class="center-block" ng-class="{inactive:!department.Is_active}">
				<td>
					<a class="btn btn-primary" ng-click="editDepartment(department)"><i class="icon-pencil icon-right"></i>Edit</a>
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
							<a class="btn-success btn" ng-click="saveDepartment(department)"><i class="icon-checkmark"></i>Save</a>
							<a class="btn-danger btn" ng-click="cancelEdit(department)"><i class="icon-cancel"></i>Cancel</a>
							<img ng-show="department.isDirty" class="smallLoading" src="../../img/loading.gif"/>
						</span>
					</span>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="bottomMargin" style="clear:both;">&nbsp;</div>
<?php
require_once '../bottom_view.php';
?>
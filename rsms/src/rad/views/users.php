<?php require_once "top_view.php" ?>
<span ng-app="00RsmsAngularOrmApp" ng-controller="UserCtrl">
<div cg-busy="{promise:UsersBusy,message:'This one will take around 1.4 seconds', backdrop:true}"></div>
<div cg-busy="{promise:UsersLoading,message:'People are really impatient these days, but the data they need keeps getting more complex.  Hopefully, by time you\'re done reading this, your data will be ready.', backdrop:true}"></div>
<table class="table table-striped table-bordered">
	<tr>
		<th>Edit</th>
		<th>Name</th>
		<th>Supervisor</th>
	</tr>
	<tr ng-repeat="user in users">
		<td><a class="btn btn-primary btn-lg" ng-click="af.copy(user)">Edit</a></tf>
		<td><h3>{{user.getName()}}| {{user.Edit}} | {{user.Supervisor_id}} </h3> </td>
		<td><a ng-if="user.Supervisor_id && !user.Supervisor" ng-click="user.getSupervisor();">get PI</a><h3 ng-if="user.Supervisor_id">{{user.Supervisor.User.Name}}</h3></td>
	</tr>
</table>
</span>
<?php require_once "bottom_view.php" ?>
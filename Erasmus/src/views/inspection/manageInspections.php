<?php
require_once '../top_view.php';
?>
<script src="../../js/manageInspections.js"></script>
<div ng-app="manageInspections" ng-controller="manageInspectionCtrl">
<div class="alert savingBox" ng-if="saving">
  <h1>
  	<i style="color:white" class="icon-spinnery-dealie spinner large"></i>
  	<span style="margin-left: 13px;display: inline-block;">Scheduling Inspection...</span>
  </h1>
</div>
<div class="navbar">    		
	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="">
			<img src="../../img/checklist-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 0px;">Manage Inspections
				<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
			</h2>	
		</li>
	</ul>
</div>
<div class="container-fluid whitebg" style="padding-top:80px; padding-bottom:30px;">
	<div class="loading" ng-if="loading">
	  <i class="icon-spinnery-dealie spinner large"></i>
	  <span>Loading...</span>
	</div>
	<select ng-model="selectedYear" ng-change="selectYear()" ng-options="year.Name for year in years">
	      <option value="">-- select year --</option>
  	</select>

	<table ng-if="dtos" class="table table-striped table-bordered userList">
		<tr>
			<th>
				Investigator<br>
				<input class="span2" ng-model="search.pi" placeholder="Filter by PI"/>
			</th>
			<th>
				Campus<br>
				<input class="span2" ng-model="search.campus" placeholder="Filter by Campus"/>
			</th>
			<th>
				Building<br>				
				<input class="span2" ng-model="search.building" placeholder="Filter by Building"/>
			</th>
			<th>
				Lab Room(s)<br>
			</th>
			<th>
				Start Date<br>
				<input class="span2" ng-model="search.date" placeholder="Filter by Date"/>
			</th>
			<th>
				EHS Inspector<br>
				<input class="span2" ng-model="search.inspector" placeholder="Filter by Inspector"/>
			</th>
			<th>
				Status<br>
				<input class="span2" ng-model="search.status" placeholder="Filter by Status"/>
			</th>
		</tr>

		<tr ng-repeat="dto in dtos | genericFilter:search:convenienceMethods" ng-class="{inactive: dto.Inspections.Status.toLowerCase().indexOf('over')>-1}">
			<td><span once-text="dto.Pi_name"></span></td>
			<td><span once-text="dto.Campus_name"></span></td>
			<td><span once-text="dto.Building_name"></span></td>
			<td>
				<ul ng-if="!dto.Inspection_rooms">
					<li ng-repeat="room in dto.Building_rooms"><span once-text="room.Name"></span></li>
				</ul>
				<ul ng-if="dto.Inspection_rooms">
					<li ng-repeat="room in dto.Inspection_rooms"><span once-text="room.Name"></span></li>
				</ul>
			</td>
			<td>
				<span ng-if="dto.Inspection_id">
					<span ng-if="dto.Inspections.Date_started">{{dto.Inspections.Date_started | dateToISO | date:"MMMM d, yyyy"}}</span>
					<select ng-if="!dto.Inspections.Date_started" ng-model="dto.Schedule_month" ng-change="mif.scheduleInspection( dto, selectedYear )" >
		      			<option value="">-- select month --</option>
		      			<option ng-selected="month.val==dto.Inspections.Schedule_month" ng-repeat="month in months" value="{{month.val}}">{{month.string}}</option>
	  				</select>
				</span>

				<select ng-if="!dto.Inspection_id" ng-model="dto.Schedule_month" ng-change="mif.scheduleInspection( dto, selectedYear )" ng-options="month.val as month.string for month in months">
	      			<option value="">-- select month --</option>
  				</select>

			</td>
			<td>
				<select ng-if="dto.Inspections && dto.Inspections.Inspectors.length" ng-model="dto.selectedInspector" ng-change="mif.scheduleInspection( dto, selectedYear, dto.selectedInspector )">
	      			<option value="">--  Add an inspector --</option>
	      			<option ng-repeat="inspector in inspectors" value="inspector">{{inspector.User.Name}}</option>
				</select>

				<select ng-model="dto.selectedInspector" ng-if="!dto.Inspections || !dto.Inspections.Inspectors.length" ng-change="mif.scheduleInspection( dto, selectedYear, dto.selectedInspector )">
	      			<option value="">-- Select inspector --</option>
	      			<option ng-repeat="inspector in inspectors" value="{{$index}}">{{inspector.User.Name}}{{$index}}</option>
				</select>

				<br ng-if="!dto.Inspections.Inspectors.length"><span ng-if="!dto.Inspections.Inspectors.length">NO INSPECTOR ASSIGNED</span>
				<ul ng-if="dto.Inspections.Inspectors">
					<li ng-repeat="inspector in dto.Inspections.Inspectors" once-text="inspector.User.Name"></li>
				</ul>
			</td>
			<td>
				<span ng-if="!dto.Inspection_id">NOT SCHEDULED</span>
				<span ng-if="dto.Inspections.Status">
					<span once-text="dto.Inspections.Status"></span>
					<!--
					<span ng-if="dto.Inspections.Status == 'SCHEDULED'">
						<span>{{dto.Inspections.Schedule_month | getMonthName}} ,{{dto.Inspections.Schedule_year}}</span>
					</span>
					-->
					<span ng-if="dto.Inspections.Status == 'CLOSED'">
						<span>: {{dto.Inspections.Date_closed | dateToISO | date:"MMMM d, yyyy"}}</span>
					</span>
					<span ng-if="dto.Inspections.Status == 'STARTED'">
						<span>: {{dto.Inspections.Date_started | dateToISO | date:"MMMM d, yyyy"}}</span>
					</span>
					<span ng-if="dto.Inspections.Status == 'OVERDUE CAP' || dto.Inspections.Status == 'PENDING EHS APPROVAL'">
						<span><br>(Due Date:{{dto.Inspections.Date_started | getDueDate | date:"MMMM d, yyyy"}})</span>
					</span>
					<span ng-if="dto.Inspections.Status == 'OVERDUE FOR INSPECTION'">
						<span><br>(Scheduled For {{dto.Inspections.Schedule_month | getMonthName}}, {{dto.Inspections.Schedule_year}})</span>
					</span>
				</span>
				<i class="icon-spinnery-dealie spinner small" style="position:absolute;margin: 3px;" ng-if="dto.IsDirty"></i>
			</td>
		</tr>
	</table>
</div>

</div>
<?php
require_once '../top_view.php';
?>
<script src="../../js/emergencyInfoHub.js"></script>

<span id="buildingHub"  ng-app="emergencyInfo" ng-controller="emergencyInfoController">
	<div class="navbar">
		<ul class="nav pageMenu row-fluid" style="background:#002060;">
			<li class="span12">			
				<h2 style="padding: 11px 0 5px 0; font-weight:bold;">
					<img src="../../img/hazard-icon.png"  style="height:50px" />
					Emergency Information
					<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
				</h2>
			</li>
		</ul>
		<div style=>&nbsp;</div>
	</div>
	<div class="whiteBg" style="margin-top:-40px; padding-bottom:15px !important;">
		<span id="emergency-info">

			<div class="center">
				<a class="btn btn-info" ng-click="searchType = 'location'"><h2>Search by Location</h2></a>
				<a class="btn btn-info" ng-click="searchType = 'pi'"><h2>Search by Principal Investigator</h2></a>
			</div>
			<div id="buildings">
				<form class="row form-inline" style="margin-left:0">
					<span ng-if="searchType == 'location'">
						<label>Building Name or Physical Address:</label>
						<input ng-if="buildings" style="width:350px" type="text" typeahead-on-select='eif.onSelectPIOrBuilding($item)' ng-model="selectedBuilding" placeholder="Select a Building" typeahead="building as building.Name for building in buildings | filter:$viewValue">
						<input ng-if="!buildings" style="width:350px" type="text" disabled="disabled" placeholder="Getting buildings...">
				       	<img ng-if="!buildings" class="" style="height: 23px; margin: 15px 0 0 -44px; position: absolute;" src="<?php echo WEB_ROOT?>img/loading.gif"/>

						<label>Room:</label>
						<input ng-if="rooms" style="" type="text" typeahead-on-select='onSelectRoom($item)' ng-model="selectedRoom" placeholder="Select a Room" typeahead="room as room.roomText for room in rooms | filter:$viewValue">
						<input ng-if="!rooms" placeholder="Select a Building" disabled="disabled">
				    </span>

				    <span ng-if="searchType == 'pi'">
						<label>Principal Investigator:</label>
						<input ng-if="pis" style="width:280px" type="text" typeahead-on-select='eif.onSelectPIOrBuilding($item)' ng-model="selectedPi" placeholder="Select a Principal Investigator" typeahead="pi as (pi.User.Name) for pi in pis | filter:$viewValue">
						<input ng-if="!pis" style="width:280px" type="text" disabled="disabled" placeholder="Getting Principal Investigators...">
				       	<img ng-if="!pis" class="" style="height: 23px; margin: 15px 0 0 -44px; position: absolute;" src="<?php echo WEB_ROOT?>img/loading.gif"/>
					
						<label>Location:</label>
						<input ng-if="rooms" style="width:350px" type="text" typeahead-on-select='onSelectRoom($item)' ng-model="selectedRoom" placeholder="Select a Room" typeahead="room as room.roomText for room in rooms | filter:$viewValue">
						<input ng-if="!rooms" style="width:350px" placeholder="Select a Principal Investigator" disabled="disabled">
					</span>

				</form>
					<span ng-if="loading && !error" class="loading">
					   <img style="width:100px"src="<?php echo WEB_ROOT?>img/loading.gif"/>
					  Loading...
					</span>

				<table ng-if="hazards && pisByRoom" class="table table-striped pisTable table-bordered">
					<tr class="blue-tr">
						<th>Principal Investigator</th>
						<th>Emergency Phone</th>
						<th>PI Department</th>
					</tr>
					<tr ng-repeat="pi in pisByRoom">
						<td>{{pi.User.Name}}</td>
						<td><span ng-if="pi.User.Emergency_phone">{{pi.User.Emergency_phone}}</span></td>
						<td>
							<ul>
								<li ng-repeat="dept in pi.Departments">{{dept.Name}}</li>
							</ul>
						</td>
					</tr>
				</table>
				<table ng-if="hazards && pisByRoom" class="table table-striped pisTable table-bordered" style="max-width:700px;">
					<tr class="blue-tr">
						<th>Lab Personnel Contacts</th>
						<th>Emergency Phone</th>
					</tr>
					<tr ng-repeat="contact in personnel">
						<td>{{contact.Name}}</td>
						<td>{{contact.Emergency_phone}}</td>
					</tr>
				</table>

			</div>
		</div>
		
		<h1 class="hazardHeader" ng-if="hazards">LABORATORY HAZARDS</h1>
		<ul class="modalHazardList">
			<li ng-if="hazards" data-ng-repeat="hazard in hazards" class="modalHazard{{hazard.Key_id}}">
				<h1>{{hazard.Name}}</h1>
				<h3 style="margin-left:32px" ng-if="eif.noSubHazardsPresent(hazard)">No {{hazard.Name}} hazards.</h3>
				<ul ng-if="hazard.ActiveSubHazards">
					<div ng-include="'EmergencyInfoList.php'" ng-init="SubHazards = hazard.ActiveSubHazards"></div>
	    		</ul>
			</li>
			<div style='clear:both'>&nbsp;</div>
		</ul>				
		
	</span>
</span>
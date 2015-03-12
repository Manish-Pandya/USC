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

			<div class="center" ng-show="!showingHazards">
				<a class="btn btn-info btn-large" ng-click="searchType = 'location'"><h2>Search by Location</h2></a>
				<a class="btn btn-info btn-large" ng-click="searchType = 'pi'"><h2>Search by Principal Investigator</h2></a>
			</div>
			<div class="center" ng-show="showingHazards">
				<a class="btn btn-info left btn-large"  ng-click="showingHazards = !showingHazards; selectedRoom = null; searchType = null"><i class="icon-redo"></i>Search Again</a>
			</div>
			<div class="spacer large"></div>
			<div class="spacer small"></div>
			<h2 class="alert" ng-if="error">{{error}}</h2>
			<div id="buildings">
				<form class="row form-inline" style="margin-left:0" ng-if="!showingHazards">
					<span ng-if="searchType == 'location'">
						<label>Building Name or Physical Address:</label>
						<input ng-if="buildings" style="width:350px" type="text" typeahead-on-select='eif.onSelectBuilding($item)' ng-model="selectedBuilding" placeholder="Select a Building" typeahead="building as building.Name for building in buildings | filter:$viewValue">
						<input ng-if="!buildings" style="width:350px" type="text" disabled="disabled" placeholder="Getting buildings...">
				       	<i ng-if="!buildings" class="icon-spinnery-dealie spinner small" style="height: 23px; margin: 15px 0 0 -44px; position: absolute;"></i>

						<label>Room:</label>
						<input ng-if="rooms" style="" type="text" typeahead-on-select='onSelectRoom($item)' ng-model="selectedRoom" placeholder="Select a Room" typeahead="room as room.roomText for room in rooms | filter:{roomText: $viewValue}">
						<input ng-if="!rooms && !gettingRooms" placeholder="Select a Building" disabled="disabled">
						<input ng-if="!rooms && gettingRooms" placeholder="Getting rooms..." disabled="disabled">
				       	<i ng-if="!rooms && gettingRooms" class="icon-spinnery-dealie spinner small" style="height: 23px; margin: 15px 0 0 -44px; position: absolute;"></i>
				    </span>

				    <span ng-if="searchType == 'pi'">
						<label>Principal Investigator:</label>
						<input ng-if="pis" style="width:280px" type="text" typeahead-on-select='eif.onSelectPI($item)' ng-model="selectedPi" placeholder="Select a Principal Investigator" typeahead="pi as (pi.User.Name) for pi in pis | filter:$viewValue">
						<input ng-if="!pis" style="width:280px" type="text" disabled="disabled" placeholder="Getting Principal Investigators...">
				       	<i ng-if="!pis" class="icon-spinnery-dealie spinner small" style="height: 23px; margin: 15px 0 0 -44px; position: absolute;"></i>

						<label>Location:</label>
						<input ng-if="rooms && !gettingRoomsForPI" style="width:350px" type="text" typeahead-on-select='onSelectRoom($item)' ng-model="selectedRoom" placeholder="Select a Room" typeahead="room as room.roomText for room in rooms | filter:$viewValue">
						<input ng-if="gettingRoomsForPI" style="width:350px" placeholder="Searching for rooms..." disabled="disabled">
				       	<i ng-if="gettingRoomsForPI" class="icon-spinnery-dealie spinner small" style="height: 23px; margin: 15px 0 0 -44px; position: absolute;"></i>
						<input ng-if="!rooms && !gettingRoomsForPI" style="width:350px" placeholder="Select a Principal Investigator" disabled="disabled">
					</span>

				</form>
				<span ng-if="loading && !error" class="loading" style="margin-left:-140px;">
		       	  <i class="icon-spinnery-dealie spinner large"></i>
				  <span>Loading...</span>
				</span>
				<h2 class="bold" style="margin:-35px 0 10px" ng-if="room && building">Room {{room.Name}}, {{building.Name}}</h2>
				<ul ng-if="hazards && showingHazards" style="font-size:20px; font-weight:bold; list-style:none;">
					<li style="padding:10px"><a target="_blank" href="http://wiser.nlm.nih.gov/">WISER (Wireless Information System for Emergency Responders)</a></li>
					<li style="padding:10px"><a target="_blank" href="http://cameochemicals.noaa.gov/">CAMEO Chemicals (Database of Hazardous Materials)</a></li>
				</ul>

				<table ng-if="hazards && pisByRoom && showingHazards" class="table table-striped pisTable table-bordered">
					<tr class="blue-tr">
						<th>Principal Investigator</th>
						<th>Phone</th>
						<th>Department</th>
					</tr>
					<tr ng-repeat="pi in pisByRoom">
						<td style="width:37%">{{pi.User.Name}}</td>
						<td style="width:18%"><span ng-if="pi.User.Emergency_phone">{{pi.User.Emergency_phone}}</span><span ng-if="!pi.User.Emergency_phone">Unknown</span></td>
						<td style="width:45%">
							<ul style="list-style: none;">
								<li ng-repeat="dept in pi.Departments">{{dept.Name}}</li>
							</ul>
						</td>
					</tr>
				</table>
				<table ng-if="hazards && pisByRoom && showingHazards" class="table table-striped pisTable table-bordered" style="max-width:500px;">
					<tr class="blue-tr">
						<th>Lab Personnel Contacts</th>
						<th>Phone</th>
					</tr>
					<tr ng-repeat="contact in personnel">
						<td>{{contact.Name}}</td>
						<td>{{contact.Emergency_phone}}<span ng-if="!contact.Emergency_phone">Unknown</span></td>
					</tr>
				</table>

			</div>
		</div>

		<span ng-if="showingHazards">
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
</span>
<?php
require_once '../top_view.php';
?>
<script src="../../js/buildingHub.js"></script>

<span class="whiteBg" id="buildingHub"  ng-app="buildingHub" >
	<div class="navbar" style="margin-bottom:0">
		<ul class="nav pageMenu" style=" background: #49afcd; color:white !important; padding: 4px 0 0 0; width:100%">
			<li class="span6">
				<i class="pull-left icon-home-5" style="margin-top: 16px; font-size: 46px; width:55px;"></i>
					<h2  style="padding: 11px 0 5px 15px;">Building Hub</h2>
			</li>
		</ul>
		<div style=>&nbsp;</div>
	</div>
	<div class="whiteBg" style="margin-top:-30px;">
		<span ng-controller="buildingHubController">


			<div id="buildings">
				<div class="row" style="margin-left:0">
					<input style="" type="text" typeahead-on-select='onSelectBuilding($item, $model, $label)' ng-model="selectedBuilding" placeholder="Select a Building" typeahead="building as building.Name for building in Buildings | filter:$viewValue">
					<input ng-if="building" style="" type="text" typeahead-on-select='onSelectRoom($item, $model, $label)' ng-model="selectedRoom" placeholder="Select a Room" typeahead="rooms as room.Name for room in building.Rooms | filter:$viewValue2">
				</div>
				<a class="btn btn-mini btn btn-info" ng-click="showCreateBuilding()">Create or Edit Building<i class="icon-pencil"></i></a>
				<div id="buildingAdmin" ng-hide="!showAdmin">
					<input ng-model="newBuilding.Name" type="text">
					<a class="btn btn-mini btn-success" style="margin:-8px -5px 0 0;" ng-click="createBuilding()"><i class="icon-checkmark"></i>Create Building</a>
					<a class="btn btn-mini btn-primary" ng-click="createBuilding(true)" style="margin:-8px 0 0 5px;"><i class="icon-checkmark"></i>Update Building</a><br>


					<input ng-show="building" ng-model="newRoom" style="" placeholder="Create a new room" type="text">
					
					<input ng-if="!building" disabled="disabled"><br>
					<textarea ng-model="safety_contact_information" row="5" cols="10">{{safety_contact_information}}</textarea><br>
					<a ng-if="building" class="btn btn-mini btn-success" style="-3px 1px 15px 0" ng-click="createRoom()"><i class="icon-checkmark"></i>Save</a>
					
					<h3 ng-if="building">Rooms in {{building.Name}}</h3>
					<p ng-if="!building.Rooms.length && building">{{building.Name}} doesn't have any rooms yet.</p>
					<ul ng-if="building.Rooms.length">
						<li ng-repeat="room in building.Rooms">{{room.Name}}</li>
					</ul>
				</div>

				<div class="roomDisplay" ng-if="room">
					<h2>EMERGENCY INFORMATION for {{building.Name}}, room {{room.Name}}</h2>
					<ul>
						<li ng-repeat="pi in room.PrincipalInvestigators">
							<h3>Principal Investigator: {{pi.User.Name}}   {{pi.User.Emergency_phone}}</h3>
							<ul>
								<li ng-repeat="contact in pi.LabPersonnel">{{contact.Name}} <span ng-if="contact.hazardType">({{contact.hazardType}})</span> {{contact.Emergency_phone}}</li>
							</ul>
						</li>
					</ul>

					<h3 ng-show="bioHazards">Biological Hazards</h3>
					<p ng-if="bioHazards && !bioHazards.length">There are no biohazards in the system for this room.</p>
					<ul ng-show="bioHazards.length">
						<li ng-repeat="hazard in bioHazards"style="color:#c00000;">{{hazard.Name}}</li>
					</ul>

					<h3 ng-show="chemicalHazards">Chemical Hazards</h3>
					<p ng-if="chemicalHazards && !chemicalHazards.length">There are no chemical hazards in the system for this room.</p>
					<ul ng-show="chemicalHazards.length">
						<li ng-repeat="hazard in chemicalHazards" style="color:#7030a0;">{{hazard.Name}}</li>
					</ul>

					<h3 ng-show="radHazards.length">Radiation Hazards</h3>
					<p ng-if="chemicalHazards && !chemicalHazards.length">There are no radiation hazards in the system for this room.</p>
					<ul ng-show="radHazards.length">
						<li ng-repeat="hazard in radHazards" style="color:#0070c0;">{{hazard.Name}}</li>
					</ul>



					<div class="well">{{room.Safety_contact_information}}</div>
					
	</div>		
</span>
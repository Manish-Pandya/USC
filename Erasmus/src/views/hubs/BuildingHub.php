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
			<ul id="buildings">
				<li class="building" ng-class="{'selected': building.showChildren}" ng-repeat="building in buildings" ng-click="reveal(building)">
					<h2>{{building.Name}}</h2>
					<ul ng-show="building.showChildren">
						<li><h3>Rooms:</h3></li>
						<li ng-repeat="room in building.rooms">
							{{room.Name}}
							<ul>
							</ul>
							<ul>
								<li ng-repeat="hazard in room.Hazards">{{hazard.Name}}</li>
							</ul>
						</li>
					</ul>
				</li>
			</ul>

		</span>
	</div>
</span>
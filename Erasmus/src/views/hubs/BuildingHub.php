<?php
require_once '../top_view.php';
?>
<script src="../../js/buildingHub.js"></script>

<span class="whiteBg" id="buildingHub"  ng-app="buildingHub" >
	<div class="navbar" style="margin-bottom:0">
		<ul class="nav pageMenu" style=" background: #49afcd; color:white !important; padding: 4px 0 0 0; width:100%">
			<li class="">
				<i class="pull-left icon-home-5" style="margin-top: 16px; font-size: 46px; width:55px;"></i>
				<h2  style="padding: 11px 0 5px 15px;">Building Hub
					<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
				</h2>
			</li>
		</ul>
		<div style=>&nbsp;</div>
	</div>
	<div class="whiteBg" style="margin-top:-30px;">
		<span ng-controller="buildingHubController">
			<div id="buildings">


				<div class="row" style="margin-left:0">
					<input style="" ng-if="Buildings" type="text" typeahead-on-select='onSelectBuilding($item, $model, $label)' ng-model="selectedBuilding" placeholder="Select a Building" typeahead="building as building.Name for building in Buildings | filter:$viewValue">
					<a style="margin:-11px 0 0 0" ng-if="Buildings" class="btn btn-mini btn btn-info" ng-click="showCreateBuilding()">New Building<i style="margin-left:5px;" class="icon-plus"></i></a>
					<input style="" placeholder="Getting buildings..." ng-if="!Buildings" disabled="disabled" ng-if="buildings">
					<img ng-if="!Buildings" style="height:23px; margin:-1px 0 0 -33px;" src="<?php echo WEB_ROOT?>img/loading.gif"/>
				</div>


				<div id="buildingAdmin" ng-hide="!showAdmin">
					<input ng-model="newBuilding.Name" type="text">
					<a class="btn btn-mini btn-success" style="margin:-8px -5px 0 0;" ng-click="createBuilding()"><i class="icon-checkmark"></i>Create Building</a>
					<img ng-show="newBuilding.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
				</div> <!---->
					
					
					<span ng-if="!building.edit">
						<h3 ng-if="building">Rooms in {{building.Name}}	
						<a ng-click="editBuilding(building)" class="bnt btn-mini btn-primary"><i class="icon-pencil"></i></a>
						</h3>					
						<p ng-if="!building.Rooms.length && building">{{building.Name}} doesn't have any rooms yet.</a>
						</p>
					</span>
					<span ng-if="building.edit">
						<input ng-model="buildingCopy.Name">
						<a class="btn btn-mini btn-success" ng-click="createBuilding(true)" style="margin:-8px 0 0 5px;"><i class="icon-checkmark"></i>Update Building</a>
						<a class="btn btn-mini btn-danger" ng-click="cancelEditBuilding()"><i class="icon-cancel"></i></a>
						<img ng-show="buildingCopy.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
					</span>
					<br>
					<a ng-if="building" class="btn btn-mini btn-info" style="margin:-3px 1px 15px 0" ng-click="setAddNewRoom()"><i class="icon-checkmark"></i>Add Room</a>
					
					<ul>
						<li ng-if="newRoom">
							<input ng-model="roomCopy.Name">
								<a ng-click="cancelNewRoom()" class="bnt btn-mini btn-danger"><i class="icon-cancel"></i></a>
								<a ng-click="createRoom()" class="bnt btn-mini btn-success"><i class="icon-checkmark"></i></a>
								<img ng-show="roomCopy.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
							</span>
						</li>
						<li ng-repeat="room in building.Rooms">
							<span ng-if="!room.edit">
								<h3>{{room.Name}}
									<a ng-click="editRoom(room)" class="bnt btn-mini btn-primary"><i class="icon-pencil"></i></a>
								</h3>
							</span>
							<span ng-if="room.edit">
								<input ng-model="roomCopy.Name">
								<a ng-click="cancelEditRoom(room)" class="bnt btn-mini btn-danger"><i class="icon-cancel"></i></a>
								<a ng-click="saveEditedRoom(room)" class="bnt btn-mini btn-success"><i class="icon-checkmark"></i></a>
								<img ng-show="roomCopy.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
							</span>
						</li>
					</ul>
				
					
	</div>		
</span>
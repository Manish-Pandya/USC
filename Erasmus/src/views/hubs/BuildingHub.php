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
				<div class="building"  ng-class="{'selected': building.showChildren}" ng-repeat="building in buildings" ng-click="reveal(building)">
					<h2>{{building.Name}}
						<div class="buttons">
							<a class="btn btn-success btn-large" ng-click="addRoomToBuilding(building)"><i class="icon-plus-2"></i>  Add Room</a>
							<a class="btn btn-danger btn-large" ng-click="addRoomToBuilding(building)"><i class="icon-remove"></i>  Deactivate Room</a>
						</div> 
					</h2>
					<span ng-show="building.showChildren">
					 

						<ul>
							<li ng-repeat="room in building.rooms" class="room">
								<span ng-hide="room.isNew">
								<h2 class="">Room {{room.Name}}</h2>
									<ul class="well ">
										<li class="row">
											<h3><span ng-hide="!room.PIs.length">Principal Investigators in room {{room.Name}}:</span><a ng-click="addPItoRoom(room)" class="btn btn-success btn-mini"><i class="icon-plus-2"></i>Add PI</a></h3>
											<ul>
												<li ng-show="!room.PIs.length">This room has no PIs</li>
												<li ng-repeat="PI in room.PIs" class="span12 PI ">

													<span ng-hide="PI.isNew">
														<h3>{{PI.Name}}<a class="btn btn-info btn-mini" href="PIHub.php?pi={{PI.KeyId}}"><i class="icon-null">&#xe164;</i>Edit in PI Hub</a></h3>
													</span>

													<span ng-show="PI.isNew" class="span12">
														<input style="margin-top:9px;" type="text" ng-model="customSelected" placeholder="Add a PI" typeahead="PI as PI.Name for PI in PIs | filter:{Name:$viewValue}">
														<a class="btn btn-mini btn-success" ng-click="saveNewPI(room,customSelected)">Add  <i class="icon-plus-2"></i></a>
														<a class="btn btn-mini btn-danger" ng-click="cancel(piDTO)">Cancel  <i class="icon-cancel-2"></i></a>
													</span>
													   

													<ul class="span6 contacts">
														<li><span ng-hide="!PI.SafteyContacts.length"><h4>{{PI.Name}}'s Safety Contacts</h4></span><a ng-click="addSafetyContacttoPI(PI)" class="btn btn-mini btn-success"><i class="icon-plus-2"></i>Add Safety Contact</a></li>
														<span ng-show="PI.Name">
															<li ng-show="!PI.SafteyContacts.length"><h4>{{PI.Name}} has no safety contacts.</h4></li>
														</span>
														<li ng-repeat="contact in PI.SafteyContacts">
															<ul>
																<li>{{contact.Name}}</li>
																<li>{{contact.Phone}}</li>
															</ul>
														</li>
													</ul>
													<ul class="span5 hazards" >
														<span ng-show="PI.Name">
															<li ng-show="!PI.Hazards.length"><h4>{{PI.Name}} has no hazards in this room.</h4></li>
														</span>
														<span ng-hide="!PI.Hazards.length">
															<li><h4>{{PI.Name}}'s Hazards in room {{room.Name}}:</h4></li>
															<li ng-repeat="hazard in PI.Hazards"><h5>{{hazard.Name}}</h5></li>
														</span>
													</ul>
													
												</li>
											</ul>
										</li>
									</ul>
								</span>
								<span ng-show="room.isNew">
									<ul class="well ">
										<li class="row">
											<input ng-model="room.Name"/>
											<a class="btn btn-success btn-mini" ng-click="saveRoom(building)"><i class="icon-checkmark"></i>  Save Room</a>
											<!--
											<h3>Principal Investigators in room {{room.Name}}:</h3>
											<ul>
												<input ng-model="room.Name"/>
												<li ng-repeat="PI in room.PIs" class="span12 PI "><h3>{{PI.Name}}<a class="btn btn-info btn-mini" href="PIHub.php?pi={{PI.KeyId}}"><i class="icon-asdfadsf">&#xe164;</i>Edit in PI Hub</a></h3>
													<ul class="span6 contacts">
														<li><h4>{{PI.Name}}'s Safety Contacts</h4></li>
														<li ng-repeat="contact in PI.SafteyContacts">{{contact.Name}}</li>
														<li ng-repeat="contact in PI.SafteyContacts">{{contact.Phone}}</li>
													</ul>
													<ul class="span5 hazards">
														<li><h4>{{PI.Name}}'s Hazards in room {{room.Name}}:</h4></li>
														<li ng-repeat="hazard in room.Hazards"><h5>{{hazard.Name}}</h5></li>
													</ul>
												</li>
											</ul>
										-->
										</li>
									</ul>
								</span>
							</li>
						</ul>
					</span>
				</div>
			</div>

		</span>
	</div>
</span>
<?php
require_once '../top_view.php';
?>
<script src="../../js/hazardAssessment.js"></script>

<div class="navbar">
	<ul class="nav pageMenu row-fluid" style="background: #e67e1d;">
		<li class="span12">			
			<h2 style="padding: 11px 0 5px 0; font-weight:bold; text-align:center">
				<img src="../../img/hazard-icon.png"  style="height:50px" />
				Laboratory Inspection Hazard Assessment
				<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
			</h2>
		</li>
	</ul>
</div>
<div data-ng-app="hazardAssesment">
<div class="container-fluid whitebg" style="padding-bottom:130px;" >
	<div class="" >
	<!-- recursive subhazard template -->
    <script type="text/ng-template" id="sub-hazard.html">
		<h4 style="display:inline-block;" class="hazardLi">
			<label class="checkbox inline">
				<input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
				<span class="metro-checkbox">{{child.HazardName}}</span>
			</label>
			<span ng-show="child.Children.length">
				<i class="icon-plus-2 modal-trigger-plus-2" ng-click="showSubHazards($event, child)"></i>
			</span>
			<span ng-show="child.IsPresent">
				<i class="icon-enter" ng-click="showRooms($event, child)"></i>
			</span>
		</h4>		
		<div ng-class="{hidden: !child.showSubHazardsModal}" class="subHazardModal" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px"> 
			<h3 class="orangeBg">{{child.HazardName}}<i style="float:right; margin-top:5px;" class="icon-cancel-2" ng-click="child.showSubHazardsModal = !child.showSubHazardsModal"></i></h3>
			<ul>
				<li ng-repeat="(key, child) in child.Children">
					<label class="checkbox inline">
						<input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
						<span class="metro-checkbox">{{child.HazardName}}<img ng-show="child.IsDirty" class="smallLoading" src="../../img/loading.gif"/></span>
					</label>
				</li>
			</ul>
		</div>	

		<div class="roomsModal" ng-class="{hidden: !child.showRoomsModal}" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px;">
			<h3 class="orangeBg">{{child.HazardName}}<i style="float:right; margin-top:5px;" class="icon-cancel-2" ng-click="child.showRoomsModal = !child.showRoomsModal"></i></h3>
			<ul>
				<li ng-repeat="(key, room) in child.PossibleRooms" >
					<label class="checkbox inline" ng-show="room.IsAllowed">
						<input ng-show="room.IsAllowed" type="checkbox" ng-change="handleRoom(room, child, hazard)" ng-model="room.ContainsHazard"/>
						<span class="metro-checkbox">{{room.RoomName}}<img ng-show="room.waitingForServer" class="" src="../../img/loading.gif"/></span>
					</label>

					<label class="checkbox inline disallowed" ng-hide="room.IsAllowed">
						<input  type="checkbox"  ng-model="room.ContainsHazard"  disabled>						
						<span class="metro-checkbox">{{room.RoomName}}<img ng-show="room.waitingForServer" class="" src="../../img/loading.gif"/></span>
					</label>

				</li>			
			</ul>
		</div>

		<ul ng-hide="!child.showRooms" class="subRooms">
			<li ng-repeat="(key, room) in child.PossibleRooms | filter: {ContainsHazard: true}" class="">
				{{room.RoomName}}
			</li>
		</ul>

		<ul>
			<li ng-repeat="child in child.Children" ng-show="child.IsPresent" id="id-{{child.Key_Id}}" class="hazardLi"><span data-ng-include="'sub-hazard.html'"></span></li>
		</ul>
    </script>

	    <div data-ng-controller="hazardAssessmentController">
	    <div id="editPiForm" class="row-fluid">
		<form class="form">
		     <div class="control-group span4">
		       <label class="control-label" for="name"><h3>Principal Investigator</h3></label>
		       <div class="controls">
		       <span ng-show="!PIs">
		         <input class="span12" style="background:white;border-color:#999"  type="text"  placeholder="Getting PIs..." disabled="disabled">
		       	<img class="" style="height:23px; margin:-73px 0 0 110px;" src="<?php echo WEB_ROOT?>img/loading.gif"/>
		       </span>
		       <span ng-hide="!PIs">
		       	<input style="" class="span12" typeahead-on-select='onSelectPi($item, $model, $label)' type="text" ng-model="customSelected" placeholder="Select PI" typeahead="pi as (pi.User.Name+' '+pi.User.Username) for pi in PIs | filter:$viewValue">
		       </span>
		      </div>
		      	<h3 ng-hide="!PI">Manage Data for Selected PI: <a href="../hubs/PIHub.php/#/rooms?pi={{PI.User.Key_Id}}" target="_blank">{{PI.User.Name}}</a></h3>
		     </div>

			<div class="span8">
		       <div class="controls">
		       <h3 class="span6">Building(s):</h3>
		       <h3 class="span6">Laboratory Rooms:
		       <div class="control-group roomSelectContainer" ng-hide="!buildings.length">
		       		<a class="btn btn-info btn-mini" ng-click="selectRooms = !selectRooms" data-toggle="dropdown" href="#">Select Rooms to Inspect</a>
			    </div>

			     </h3>

	       		<span ng-show="!buildings.length">
				       	<p style="display: inline-block; margin-top:5px;">
				       	<img class="" style="height:23px; margin:-4px 0 0 0px;" src="<?php echo WEB_ROOT?>img/loading.gif"/>
				       	Select a Principal Investigator.
				       	</p>
			       </span>
			       <span ng-hide="!buildings || !PI">
			       		<ul class="selectedBuildings">
			       			<li ng-repeat="(key, building) in buildings">
			       			<div class="span6">
			       				<h4 ng-class="{greyedOut: !building.IsChecked}"><!--<a class="btn btn-danger btn-mini" style="margin-right:5px;"><i class="icon-cancel-2" ng-click="removeBuilding(building)"></i></a>-->{{building.Name}}</h4>
			       			</div>
			       			<div class="roomsForBuidling span6">
				       			<ul>
				       				<li ng-class="{greyedOut: !room.IsSelected}" ng-repeat="(key, room) in building.Rooms">{{room.RoomName}}</li>
				       			</ul>
			       			</div>
			       			</li>
					       <span class="roomSelect" style="position:relative">
						      <ul ng-show="selectRooms && buildings" class="selectRooms">
						      	 <li style="float:right"><i class="icon-cancel-2" ng-click="selectRooms = !selectRooms"></i></li>
						       	 <li ng-repeat="(key, building) in buildings">						       	 	
						       	 	<label class="checkbox inline">
										<input ng-model='building.IsChecked' ng-init='true' type="checkbox" ng-change="checkBuilding(building)"/>
										<span class="metro-checkbox"><h4>{{building.Name}}</h4></span>
									</label>
									
						       	 	<ul>
							       	 	<li ng-repeat="(key, room) in building.Rooms" style="width:100%">
								       	 	<label class="checkbox inline">
												<input ng-model='room.IsSelected' type="checkbox" ng-change="selectRoom(room,building)"/>
												<span class="metro-checkbox smaller">{{room.RoomName}}</span>
											</label>
										</li>
									</ul>
						       	 </li>
						       	 <li><a a class="btn btn-warning" ng-click="getHazards()">Get Hazards</a></li>
						       	</ul>
					       </span>
			       		</ul>
			       </span>
			    </div>
			</div>	
			</form>
		</div>

	    <div class="loading" ng-show='!PI' >
		<span ng-hide="noPiSet">
		  <img class="" src="<?php echo WEB_ROOT?>img/loading.gif"/>
		  Getting Selected Principal Investigator...
		</span>
		</div>								

	    <form>
	    <span ng-show="hazardsLoading" class="loading">
	     <img style="width:100px"src="<?php echo WEB_ROOT?>img/loading.gif"/>
		  Getting Hazards
	    </span>

			<ul class="allHazardList">
				<li class="hazardList" ng-class="{narrow: hazard.hidden}" data-ng-repeat="hazard in hazards">
					<h1 class="hazardListHeader" id="{{hazard.cssId}}" ng-show="hazard.hidden" ng-click="hazard.hidden = !hazard.hidden">&nbsp;</h1>
					<span ng-hide="hazard.hidden">
				    <h1 ng-click="hazard.hidden = !hazard.hidden" class="hazardListHeader" id="{{hazard.cssId}}">{{hazard.HazardName}}</h1>
					<hr>
					
					<ul>
						<li>
							<a style="margin-bottom:15px;" class="btn btn-mini btn-info"ng-click="hazard.hideUnselected = !hazard.hideUnselected">
								<span ng-show="!hazard.hideUnselected">
									<i style="margin-right:8px !important;" class="icon-collapse"></i>View Only Hazards Present
								</span>
								<span ng-show="hazard.hideUnselected">
									<i style="margin-right:8px !important;" class="icon-full-screen"></i>View All Hazard Categories
								</span>
							</a>
						</li>
						<li ng-repeat="(key, child) in hazard.Children" class="hazardLi" id="id-{{hazard.Key_Id}}" ng-hide="!child.IsPresent && hazard.hideUnselected">
							<h4 style="display:inline-block;" class="hazardLi">
								<label class="checkbox inline">
									<input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
									<span class="metro-checkbox">{{child.HazardName}}<img ng-show="child.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
									<!--<pre>{{child | json}}</pre>-->
									</span>
								</label>
								<span ng-show="child.Children.length && child.IsPresent">
									<i class="icon-plus-2 modal-trigger-plus-2" ng-click="showSubHazards($event, child)"></i>
								</span>
								<span ng-show="child.IsPresent">
									<i class="icon-enter" ng-click="showRooms($event, child)"></i>
								</span>
							</h4>		
							<div ng-class="{hidden: !child.showSubHazardsModal}" class="subHazardModal" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px"> 
								<h3 class="orangeBg">{{child.HazardName}}<i style="float:right; margin-top:5px;" class="icon-cancel-2" ng-click="child.showSubHazardsModal = !child.showSubHazardsModal"></i></h3>
								<ul>
									<li ng-repeat="(key, child) in child.Children">
										<label class="checkbox inline">
											<input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
											<span class="metro-checkbox">{{child.HazardName}}</span>
										</label>
									</li>
								</ul>
							</div>	

							<div class="roomsModal" ng-class="{hidden: !child.showRoomsModal}" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px;">
								<h3 class="orangeBg">{{child.HazardName}}<i style="float:right; margin-top:5px;" class="icon-cancel-2" ng-click="child.showRoomsModal = !child.showRoomsModal"></i></h3>
								<ul>
									<li ng-repeat="(key, room) in child.PossibleRooms">
										<label class="checkbox inline">
											<input type="checkbox" ng-change="handleRoom(room, child, hazard)" ng-model="room.ContainsHazard"/>
											<span class="metro-checkbox">{{room.RoomName}}<img ng-show="room.waitingForServer" class="" src="../../img/loading.gif"/></span>
										</label>
									</li>
								</ul>
							</div>

							<ul ng-hide="!child.showRooms" class="subRooms">
								<li ng-repeat="(key, room) in child.PossibleRooms | filter: {ContainsHazard: true}" class="" ng-class="{'last':$last}">
									{{room.RoomName}}
								</li>
							</ul>
							<ul>
								<li ng-repeat="child in child.Children" ng-show="child.IsPresent" class="hazardLi" id="id-{{child.Key_Id}}">
									<span data-ng-include="'sub-hazard.html'"></span>				
								</li>
							</ul>
						</li>
					</ul>
					</span>
				</li>
			</ul>
		</form>

			<div class="span12">
					<!--<pre><strong>selected with helper function:</strong> {{selectedHazards() | json}}</pre>]-->
					<h2 data-ng-repeat="hazard in checked_hazards">{{hazard.Name}}</h2>
			</div>
		</div> 	
	</div>

</div>
<span ng-controller="footerController">
	
	<div ng-show="selectedFooter == 'reports'" class="selectedFooter">
		<h3>Archived Reports</h3>
		<i ng-click="close()" class="icon-cancel-2" style="float:right;"></i>
		<ul>
			<li><a target="_blank" href="InspectionConfirmation.php/confirmation#/details">Archived Report 1</a></li>
			<li><a target="_blank" href="InspectionConfirmation.php/confirmation#/details">Archived Report 1</a></li>
		</ul>
	</div>

	<div style="margin-left:25%;" ng-show="selectedFooter == 'contacts'" class="selectedFooter">
	<h3>Contacts</h3>
			<i ng-click="close()" class="icon-cancel-2" style="float:right;"></i>
		<div class="loading" ng-show='!doneLoading' >
		  <img class="" src="../../img/loading.gif"/>
		</div>
		<ul>
			<li ng-repeat="contact in contacts">{{contact.Name}}</li>
		</ul>
	</div>

	<div ng-show="selectedFooter == 'comments'" class="selectedFooter">
	</div>

<div id="footer" style="position:fixed; bottom:0; width:100%; background:white; left:0; z-index:10000; box-shadow:0 0 20px rgba(0,0,0,.5)">
	<ul class="container-fluid whitebg">
		<li><a ng-click="getArchivedReports()"><img src="../../img/clipboard.png"/><span>Archived Reports</span></a></li>
		<li><a href="../hubs/userhub.php"><img src="../../img/phone.png"/><span>Laboratory Contacts</span></a></li>
		<li><a><img src="../../img/speechBubble.png"/><span>Inspection Comments</span></a></li>
		<li><a href="InspectionChecklist.php"><img src="../../img/checkmarkFooter.png"/><span>Begin Inspection</a></span></li>
	</ul>
</div>
</span>


</div>
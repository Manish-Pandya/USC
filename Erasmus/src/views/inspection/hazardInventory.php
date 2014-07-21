<?php
require_once '../top_view.php';
?>
<script src="../../js/hazardInventory.js"></script>

<div class="navbar">
	<ul class="nav pageMenu row-fluid orangeBg">
		<li class="span12">			
			<h2 style="padding: 11px 0 5px 0; font-weight:bold; text-align:center">
				<img src="../../img/hazard-icon.png"  style="height:50px" />
				Laboratory Hazards & Equipment Inventory
				<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
			</h2>
		</li>
	</ul>
</div>
<div data-ng-app="hazardAssesment" data-ng-controller="hazardAssessmentController">
<div class="container-fluid whitebg" style="padding-bottom:130px;" >
	<div class="" >
	<!-- recursive subhazard template -->
    <script type="text/ng-template" id="sub-hazard.html">
			<label class="checkbox inline">
				<input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
				<span class="metro-checkbox targetHaz" once-text="child.Name"></span>
			</label>
			<span ng-if="child.ActiveSubHazards.length || child.HasChildren">
				<i class="icon-plus-2 modal-trigger-plus-2" ng-click="showSubHazards($event, child, $element)"></i>
			</span>
			<span ng-show="child.IsPresent">
				<i class="icon-enter" ng-click="showRooms($event, child, $element)"></i>
			</span>

			<div ng-class="{hidden: !child.showSubHazardsModal}" class="subHazardModal popUp skinny" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px;"> 
				<h3 class="orangeBg"><span>{{child.Name}}</span><i style="float:right; margin-top:5px;" class="icon-cancel-2" ng-click="child.showSubHazardsModal = !child.showSubHazardsModal"></i></h3>
				<ul>
					<li ng-repeat="(key, child) in child.ActiveSubHazards">
						<label class="checkbox inline">
							<input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
							<span class="metro-checkbox">{{child.Name}}<img ng-show="child.IsDirty" class="smallLoading" src="../../img/loading.gif"/></span>
						</label>
					</li>
				</ul>
			</div>	

			<div class="roomsModal popUp skinny" ng-class="{hidden: !child.showRoomsModal}" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px;width:{{child.calculatedOffset.w}}px">
				<h3 class="orangeBg"><span>{{child.Name}}</span><i style="float:right; margin-top:5px;" class="icon-cancel-2" ng-click="child.showRoomsModal = !child.showRoomsModal"></i></h3>
				<ul>
					<li ng-repeat="(key, room) in child.InspectionRooms">
						<label class="checkbox inline" ng-show="$parent.$parent.$parent.child.InspectionRooms[$index].ContainsHazard">
							<input ng-show="room.IsAllowed" type="checkbox" ng-change="handleRoom(room, child, hazard)" ng-model="room.ContainsHazard"/>
							<span class="metro-checkbox">{{room.Name}}<img ng-show="room.waitingForServer" class="" src="../../img/loading.gif"/></span>
						</label>

						<label class="checkbox inline disallowed" ng-hide="$parent.$parent.$parent.child.InspectionRooms[$index].ContainsHazard">
							<input  type="checkbox"  ng-model="room.ContainsHazard"  disabled>						
							<span class="metro-checkbox">{{room.Name}}<img ng-show="room.waitingForServer" class="" src="../../img/loading.gif"/></span>
						</label>
					</li>			
				</ul>
			</div>		
		<ul ng-hide="!child.showRooms" class="subRooms">
			<li>Rooms:</li>
			<li ng-repeat="(key, room) in child.InspectionRooms | filter: {ContainsHazard: true}" class="">
				{{room.Name}}
			</li>
		</ul>

		<ul>
			<li ng-repeat="child in child.ActiveSubHazards" ng-show="child.IsPresent" id="id-{{child.Key_Id}}" class="hazardLi"><span data-ng-include="'sub-hazard.html'"></span></li>
		</ul>
    </script>

	    <div>
	    <div id="editPiForm" class="row-fluid">
		<form class="form">
		     <div class="control-group span4">
		       <label class="control-label" for="name"><h3>Principal Investigator</h3></label>
		       <div class="controls">
		       <span ng-show="!PIs">
		         <input class="span12" style="background:white;border-color:#999"  type="text"  placeholder="Getting PIs..." disabled="disabled">
		       	<img class="" style="height:23px; margin:-36px 0 0 110px;" src="<?php echo WEB_ROOT?>img/loading.gif"/>
		       </span>
		       <span ng-hide="!PIs">
		       	<input style="" class="span4" typeahead-on-select='onSelectPi($item, $model, $label)' type="text" ng-model="customSelected" placeholder="Select a PI" typeahead="pi as (pi.User.Name) for pi in PIs | filter:$viewValue">
		       </span>
		      </div>
		      	<h3 ng-hide="!inspection"><a class="btn btn-info" href="../hubs/PIHub.php#/rooms?pi={{PI.Key_id}}&inspection={{inspection.Key_id}}" target="_blank">Manage Data for Selected PI</a></h3>
		     </div>
			<div class="span8">
		       <div class="controls">
		       <h3 class="span6">Building(s):</h3>
		       <h3 class="span6">
		       Laboratory Rooms:
		       	<div class="control-group roomSelectContainer" ng-hide="!buildings.length">
		       		<a style="white-space:normal;" class="btn btn-info btn-mini" ng-click="selectRooms = !selectRooms" data-toggle="dropdown" href="#">Select Rooms to Inspect</a>
			    </div>
		       </h3>
	       		<span ng-show="!buildings.length">
				       	<p ng-if="!noRoomsAssigned" style="display: inline-block; margin-top:5px;">
					       	Select a Principal Investigator.
				       	</p>
				       	 <P ng-if="noRoomsAssigned" style="display: inline-block; margin-top:5px;">
					    	<span once-text="PI.User.Name"></span> has no rooms <a class="btn btn-info" once-href="'../hubs/PIHub.php#/rooms?pi='+PI.Key_id+'&inspection='+inspection.Key_id">Add Rooms</a>
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
				       				<li ng-class="{greyedOut: !room.IsSelected}" ng-repeat="(key, room) in building.Rooms" once-text="room.Name"></li>
				       			</ul>
			       			</div>
			       			</li>
					       <span class="roomSelect" style="position:relative">
						      <ul ng-show="selectRooms && buildings" class="selectRooms">
						      	 <li style="float:right"><i class="icon-cancel-2" ng-click="selectRooms = !selectRooms"></i></li>
						       	 <li ng-repeat="(key, building) in buildings">						       	 	
						       	 	<label class="checkbox inline">
										<input ng-model='building.IsChecked' ng-init='true' type="checkbox" ng-change="checkBuilding(building)"/>
										<span class="metro-checkbox"><h4 once-text="building.Name"></h4></span>
									</label>
									
						       	 	<ul>
							       	 	<li ng-repeat="(key, room) in building.Rooms" style="width:100%">
								       	 	<label class="checkbox inline">
												<input ng-model='room.IsSelected' type="checkbox" ng-change="selectRoom(room,building)"/>
												<span class="metro-checkbox smaller" once-text="room.Name"></span>
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

	    <h2 class="alert alert-danger" ng-if="error">{{error}}</h2>

		<span ng-hide="noPiSet">
		  <img class="" src="<?php echo WEB_ROOT?>img/loading.gif"/>
		  Getting Selected Principal Investigator...
		</span>
		</div>								

	    <form>
	    <span ng-show="hazardsLoading" class="loading">
	     <img style="width:100px"src="<?php echo WEB_ROOT?>img/loading.gif"/>
		  Getting Hazards..
	    </span>
	   		<ul class="allHazardList">
				<li class="hazardList" ng-class="{narrow: hazard.hidden}" data-ng-repeat="hazard in hazards">
					<h1 class="hazardListHeader" once-id="'hazardListHeader'+hazard.Key_id" ng-show="hazard.hidden" ng-click="hazard.hidden = !hazard.hidden">&nbsp;</h1>
					<span ng-hide="hazard.hidden">
				    <h1 ng-click="hazard.hidden = !hazard.hidden" class="hazardListHeader" once-id="'hazardListHeader'+hazard.Key_id" once-text="hazard.Name"></h1>
					<hr>
					<ul>
						<li>
							<a style="margin-bottom:15px;" class="btn btn-mini btn-info" ng-click="hazard.hideUnselected = !hazard.hideUnselected">
								<span ng-show="!hazard.hideUnselected">
									<i style="margin-right:8px !important;" class="icon-collapse"></i>View Only Hazards Present
								</span>
								<span ng-show="hazard.hideUnselected">
									<i style="margin-right:8px !important;" class="icon-full-screen"></i>View All Hazard Categories
								</span>
							</a>
						</li>
						<li ng-repeat="(key, child) in hazard.ActiveSubHazards" class="hazardLi" id="id-{{hazard.Key_Id}}" ng-hide="!child.IsPresent && hazard.hideUnselected">
							<!--<h4 class="">-->
							<label class="checkbox inline">
								<input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
								<span class="metro-checkbox targetHaz" once-text="child.Name">
									<!--<span once-text="child.Name" class="nudge-up"></span>-->

									<img ng-show="child.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
								<!--<pre>{{child | json}}</pre>-->
								</span>
							</label>
							<!--</h4>-->		
							<span ng-if="child.ActiveSubHazards.length || child.HasChildren&& child.IsPresent ">
								<i class="icon-plus-2 modal-trigger-plus-2" ng-click="showSubHazards($event, child, $element)"></i>
							</span>
							<span ng-show="child.IsPresent">
								<i class="icon-enter" ng-click="showRooms($event, child, $element)"></i>
							</span>

							<div ng-class="{hidden: !child.showSubHazardsModal}" class="subHazardModal popUp skinny" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px"> 
								<h3 class="orangeBg"><span once-text="child.Name" class="nudge-up"></span><i style="float:right; margin-top:5px;" class="icon-cancel-2" ng-click="child.showSubHazardsModal = !child.showSubHazardsModal"></i></h3>
								<ul>
									<li ng-repeat="(key, child) in child.ActiveSubHazards">
										<label class="checkbox inline">
											<input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
											<span class="metro-checkbox" once-text="child.Name" ></span>
										</label>
										<div class="clearfix"></div>
									</li>
								</ul>
							</div>	

							<div class="roomsModal popUp skinny" ng-class="{hidden: !child.showRoomsModal}" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px;width:{{child.calculatedOffset.w}}px">
								<h3 class="orangeBg"><span once-text="child.Name" class="nudge-up"></span><i class="icon-cancel-2" ng-click="child.showRoomsModal = !child.showRoomsModal"></i></h3>
								<ul>
									<li ng-repeat="(key, room) in child.InspectionRooms">
										<label class="checkbox inline">
											<input type="checkbox" ng-change="handleRoom(room, child, hazard)" ng-model="room.ContainsHazard"/>
											<span class="metro-checkbox" once-text="room.Name"><img ng-show="room.waitingForServer" class="" src="../../img/loading.gif"/></span>
										</label>
										<div class="clearfix"></div>
									</li>
								</ul>
							</div>

							<ul ng-show="getShowRooms(child)" class="subRooms">
								<li>Rooms:</li>
								<li ng-repeat="(key, room) in child.InspectionRooms | filter: {ContainsHazard: true}" class="" ng-class="{'last':$last}" once-text="room.Name"></li>
							</ul>
							<ul>
								<li ng-repeat="child in child.ActiveSubHazards" ng-show="child.IsPresent" class="hazardLi" id="id-{{child.Key_Id}}">
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
					<h2 data-ng-repeat="hazard in checked_hazards" once-text="hazard.Name"></h2>
			</div>
		</div> 	
	</div>

</div>
<span ng-controller="footerController">
	
	<div ng-show="selectedFooter == 'reports'" class="selectedFooter" style="width:auto;">
		<i ng-click="close()" class="icon-cancel-2" style="float:right;"></i>
		<h2 style="text-decoration:underline">ARCHIVED REPORTS</h2>
		<h2>Principle Investigator: <span once-text="PI.User.Name"></span></h2>
		
		<div class="loading" ng-show='!previousInspections' >
		Loading Archived Reports...
		  <img class="" src="../../img/loading.gif"/>
		</div>
		<div  id="tableContainer" class="tableContainer">
		<table ng-if="previousInspections" class="table table-striped table-bordered" class="scrollTable">
		<thead class="fixedHeader">
				<th style="width:60px;">Year</th>
				<th style="width:170px;">Inspection Date</th>
				<th style="width:216px;">Inspector(s)</th>
				<th style="width:120px;">Hazards</th>
				<th style="width:160px">Inspection Report</th>
				<th style="width:204px">Close Out Date</th>
			</thead>
			<tbody class="scrollContent">
				<tr ng-repeat="(key, inspection) in previousInspections">
					<td style="width:61px;">{{inspection.year}}</td>
					<td style="width:167px;">{{inspection.startDate}}</td>
					<td  style="width:218px;">{{inspection.Inspectors[0].User.Name}}</td>
					<td style="width:119px;">hazards</td>
					<td style="width:156px;"><a href="../inspection/InspectionConfirmation.php#/report?inspection={{inspection.Key_id}}">Report</a></td>
					<td style="width:197px;">{{inspection.endDate}}<span ng-if="!inspection.endDate">Pending</span></td>
				</tr>
			</tbody>	
		</table>
		</div>
	</div>

	<div style="margin-left:25%;" ng-show="selectedFooter == 'contacts'" class="selectedFooter">
	<i ng-click="close()" class="icon-cancel-2" style="float:right;"></i>
		<h2 style="text-decoration:underline">Lab Contacts</h2>
		<h2>Principle Investigator: {{PI.User.Name}}</h2>
		
		<div class="loading" ng-show='!PI' >
		Loading Archived Reports...
		  <img class="" src="../../img/loading.gif"/>
		</div>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>Name</th>
					<th>Lab Phone</th>
					<th>Emergency Phone</th>
					<th>Email</th>
				</tr>
			</thead>
			<tbody>
				<tr ng-repeat="(key, contact) in PI.LabPersonnel">
					<td>{{contact.Name}}</td>
					<td>{{contact.Lab_phone}}</td>
					<td>{{contact.Emergency_phone}}</td>
					<td>{{contact.Email}}</td>
				</tr>
			</tbody>	
		</table>
	</div>

	<div ng-show="selectedFooter == 'comments'" class="selectedFooter" style="margin-left:50%">
		<textarea ng-model="newNote" rows="4" style="width:100%"></textarea>
		<a ng-click="saveNoteForInspection()" class="btn btn-success"><i class="icon-checkmark"></i>Save</a>
		<a ng-click="cancelSaveNote()" class="btn btn-danger"><i class="icon-cancel"></i>Cancel</a>
		<img ng-show="newNoteIsDirty" class="smallLoading" src="../../img/loading.gif"/>
	</div>

<div id="footer" style="position:fixed; bottom:0; width:100%; background:white; left:0; z-index:10000; box-shadow:0 0 20px rgba(0,0,0,.5)">
	<ul class="container-fluid whitebg" style="padding:0 70px !Important">
		<li><a ng-click="getArchivedReports(pi)"><img src="../../img/clipboard.png"/><span>Archived Reports</span></a></li>
		<li><a ng-click="selectedFooter = 'contacts'"><img src="../../img/phone.png"/><span>Laboratory Contacts</span></a></li>
		<li><a ng-click="openNotes()"><img src="../../img/speechBubble.png"/><span>Inspection Comments</span></a></li>
		<li><a href="InspectionChecklist.php#?inspection={{inspection.Key_id}}"><img src="../../img/checkmarkFooter.png"/><span>Begin Inspection</a></span></li>
	</ul>
</div>
</span>


</div>
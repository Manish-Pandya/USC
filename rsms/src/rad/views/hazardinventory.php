<?php require_once 'top_view.php' ?>
<span ng-app="00RsmsAngularOrmApp" ng-controller="HazardInventoryCtrl">

<div cg-busy="{promise:hazardsPromise,message:'Loading Hazards', backdrop:true}"></div>
<div class="container-fluid whitebg" style="padding-bottom:130px;" >
<pre>{{pi | json}}</pre>
	<div class="" >
	<!-- recursive subhazard template -->
    <script type="text/ng-template" id="sub-hazard.html">
			<label class="checkbox inline">
				<input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
				<span class="metro-checkbox targetHaz" once-text="child.Name"></span>
			</label>
			<!--
			<span ng-if="child.SubHazards.length || child.HasChildren">
				<i class="icon-plus-2 modal-trigger-plus-2" ng-click="showSubHazards($event, child, $element)"></i>
			</span>
			<span ng-show="child.IsPresent">
				<i class="icon-enter" ng-click="showRooms($event, child, $element)"></i>
			</span>

			<div ng-class="{hidden: !child.showSubHazardsModal}" class="subHazardModal popUp skinny" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px;"> 
				<h3 class="orangeBg"><span>{{child.Name}}</span><i style="float:right; margin-top:5px;" class="icon-cancel-2" ng-click="child.showSubHazardsModal = !child.showSubHazardsModal"></i></h3>
				<ul>
					<li ng-repeat="(key, child) in child.SubHazards">
						<label class="checkbox inline">
							<input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child)"/>
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

			<ul ng-if="getShowRooms(child)" class="subRooms">
				<li>Rooms:</li>	
				<li ng-repeat="(key, room) in child.InspectionRooms | filter: {ContainsHazard: true}" class="">
					{{room.Name}}
				</li>
			</ul>
			-->
			<ul>
				<li ng-repeat="child in child.getSubHazards()" ng-show="child.IsPresent" id="id-{{child.Key_Id}}" class="hazardLi"><span data-ng-include="'sub-hazard.html'"></span></li>
			</ul>
    </script>

	    <div>
	    <div id="editPiForm" class="row-fluid">
		<form class="form">
		     <div class="control-group span4">
		       <label class="control-label" for="name"><h3>Principal Investigator</h3></label>
		       <div class="controls">
		       <span ng-show="!pis">
		         <input class="span8" style="background:white;border-color:#999"  type="text"  placeholder="Getting PIs..." disabled="disabled">
		       </span>
		       <span ng-hide="!pis">
					<input ng-if="pis" class="span8" typeahead-on-select='onSelectPi($item)' type="text" ng-model="customSelected" placeholder="Select a PI" typeahead="pi as (pi.User.Name) for pi in pis | filter:$viewValue">
		       </span>
		      </div>
		      	<h3 ng-hide="!pi"><a class="btn btn-info" href="../hubs/PIHub.php#/rooms?pi={{pi.getKey_id()}}&inspection={{inspection.getKey_id()}}">Manage Data for Selected PI:  {{pi.getUser()}}</a></h3>
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
					    	<span once-text="pi.getUser().Name"></span> has no rooms <a class="btn btn-info" once-href="'../hubs/PIHub.php#/rooms?pi='+pi.Key_id+'&inspection='+inspection.Key_id">Add Rooms</a>
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
								       	 	<label class="checkbox inline smaller">
												<input ng-model='room.IsSelected' type="checkbox" ng-change="selectRoom(room,building)"/>
												<span class="metro-checkbox smaller" once-text="room.Name"></span>
											</label>
										</li>
									</ul>
						       	 </li>
						       	 <li><a a class="btn btn-warning" ng-click="resetInspection()">Get Hazards</a></li>
						       	 <li ng-if="noRoomsSelected" class="alert alert-danger">Please select one or more rooms.</li>
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

			<span ng-if="piLoading">
			  <img class="" src="<?php echo WEB_ROOT?>img/loading.gif"/>
			  Getting Selected Principal Investigator...
			</span>
		
		</div>								

	    <form>
	    <span ng-show="hazardsLoading" class="loading">
	     <img style="width:100px"src="<?php echo WEB_ROOT?>img/loading.gif"/>
		  Building Hazard List...
	    </span>
	   		<ul class="allHazardList" ng-if="pi">
				<li class="hazardList" ng-class="{narrow: hazard.hidden}" data-ng-repeat="hazard in hazards | orderBy : [name]">
					<h1 class="hazardListHeader" once-id="'hazardListHeader'+hazard.Key_id" ng-show="hazard.hidden" ng-click="hazard.hidden = !hazard.hidden">&nbsp;</h1>
					<span ng-hide="hazard.hidden">
				    <h1 ng-click="hazard.hidden = !hazard.hidden" class="hazardListHeader" once-id="'hazardListHeader'+hazard.Key_id" once-text="hazard.Name"></h1>
					<hr>
					<ul class="topChildren">
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
						<li ng-repeat="(key, child) in hazard.getSubHazards()" class="hazardLi topChild" id="id-{{hazard.Key_Id}}" ng-hide="!child.IsPresent && hazard.hideUnselected">
							<!--<h4 class="">-->
							<label class="checkbox inline">
								<input type="checkbox" ng-model="child.IsPresent" ng-change="handleHazardChecked(child, hazard)"/>
								<span class="metro-checkbox targetHaz" once-text="child.getName()">
									<!--<span once-text="child.Name" class="nudge-up"></span>-->

									<img ng-show="child.IsDirty" class="smallLoading" src="../../img/loading.gif"/>
								<!--<pre>{{child | json}}</pre>-->
								</span>
							</label>
							<!--</h4>-->	
								
							<span ng-if="child.HasChildren">
								<i class="icon-plus-2 modal-trigger-plus-2" ng-click="showSubHazards($event, child, $element)"></i>
							</span>
							<span ng-show="child.IsPresent">
								<i class="icon-enter" ng-click="showRooms($event, child, $element)"></i>
							</span>

							<div ng-if="child.HasChildren" ng-class="{hidden: !child.showSubHazardsModal}" class="subHazardModal popUp skinny" style="left:{{child.calculatedOffset.x}}px;top:{{child.calculatedOffset.y}}px"> 
								<h3 class="orangeBg"><span once-text="child.Name" class="nudge-up"></span><i style="float:right; margin-top:5px;" class="icon-cancel-2" ng-click="child.showSubHazardsModal = !child.showSubHazardsModal"></i></h3>
								<ul>
									<li ng-repeat="(key, child) in child.SubHazards">
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

							<ul ng-if="getShowRooms(child)" class="subRooms">
								<li>Rooms:</li>
								<li ng-repeat="(key, room) in child.InspectionRooms | filter: {ContainsHazard: true}" class="" ng-class="{'last':$last}" once-text="room.Name"></li>
							</ul>
							<ul>
								<li ng-repeat="child in child.SubHazards" ng-show="child.IsPresent" class="hazardLi" id="id-{{child.Key_Id}}">
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
					<h2 data-ng-repeat="hazard in checked_hazards | orderBy : [name]" once-text="hazard.getName()"></h2>
			</div>
		</div> 	
	</div>

</div>
</span>
<?php require_once 'bottom_view.php' ?>
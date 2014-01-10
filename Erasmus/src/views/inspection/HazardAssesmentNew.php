<?php
require_once '../top_view.php';
?>
<script src="../../js/hazardAssessment.js"></script>


<div class="navbar">
	<ul class="nav pageMenu" style="background: #e67e1d;">
		<li class="span12">
			<img src="../../img/hazard-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">Hazard Assessment</h2>	
		</li>
	</ul>
</div>
<div class="container-fluid whitebg" style="padding-bottom:130px;">
<!--
	<form method="post" action="InspectionChecklist.php">
		<h2><?php echo $_POST['pi']?>'s Hazards</h2>
		<div id="tree1"></div>
		<a href="InspectionChecklist.php" class="btn btn-large btn-primary">Begin Inspection<i style="font-size:45px; margin:3px 16px 0 -9px" class="icon-arrow-right"></i></a>
	</form>
-->
	<div class="" data-ng-app="hazardAssesment">

	 	<script type="text/ng-template" id="hazards-modal.html">

	 		<div class="modal-header">
	 			<h2>{{subhazard.Name}}</h2>
	 		</div>
	 		<div class="modal-body">
	 		<!--find the right spot for this 
	 			<li ng-repeat="room in subhazard.Rooms"><label class="checkbox inline" style="display:block;">
	    			<label class="checkbox inline">
						<input type="checkbox" ng-model="room.presentInThisRoom" ng-change="handleRooms(subhazard, room)" />
						<span class="metro-checkbox">{{room.Name}}</span>
					</label>
				</li> 	
				-->	
		 		<span data-ng-include="'modal-hazards.html'"></span>
	 		</span>
			</div>

	    </script>

	    <script type="text/ng-template" id="rooms-modal.html">

	    	<div class="modal-header">
	    		<h2>{{subhazard.Name}}</h2>
	    	</div>
	    	<div class="modal-body">
	    	<ul style="list-style:none;">

	    		<li ng-repeat="room in subhazard.Rooms"><label class="checkbox inline" style="display:block;">
	    			<label class="checkbox inline">
						<input type="checkbox" ng-model="room.presentInThisRoom" ng-change="handleRooms(subhazard, rooms)" />
						<span class="metro-checkbox">{{room.Name}}</span>
					</label>
				</li>

			</ul>
	    	</div>
	    </script>

	<!--revealSubhazards-->
	    <script type="text/ng-template" id="modal-hazards.html">

			<tabset vertical="true" type="{{navType}}">
		 			 <tab select="toggleSubhazardState(subhazard)" ng-repeat="subhazard in subhazard.SubHazards" active="subhazard.IsActive" disabled="subhazard.disabled">	
				     	<tab-heading>
					     <h4>
					     <ng-switch on="subhazard.isPresent">
					     	<i ng-switch-when="true" class="icon-checkmark" style="color: #333;background: white;margin-top: 3px;width: 18px;"></i>
					     </ng-switch>
					     	{{subhazard.Name}}
						</h4>
						<ng-switch on="subhazard.IsActive">
							<span ng-switch-when="true">
							<p style="margin-top:7px">Rooms:</p>
							<li class="" ng-repeat="room in subhazard.Rooms" style="margin-top:8px;">
								<label stop-event='click' class="checkbox inline" style="display:block;">
									<input type="checkbox" ng-model="room.presentInThisRoom" ng-change="handleRooms(subhazard, room)"/>
									<span class="metro-checkbox">{{room.Name}}</span>
								</label>
							</li>
							<div style="clear:both"></div>
							</span>
							</ng-switch>
						</tab-heading>
				     	<tabset vertical="true">
				     		<tab select="toggleSubhazardState(subhazard)" ng-repeat="subhazard in subhazard.SubHazards" active="subhazard.IsActive" disabled="subhazard.disabled">
						     <tab-heading>
							     <h4>
									 <ng-switch on="subhazard.isPresent">
								     	<i ng-switch-when="true" class="icon-checkmark" style="color: #333;background: white;margin-top: 3px;width: 18px;"></i>
								     </ng-switch></i>
								     {{subhazard.Name}}
								</h4>
								<ng-switch on="subhazard.IsActive">
									<span ng-switch-when="true">
									<p style="margin-top:7px">Rooms:</p>
									<li class="" ng-repeat="room in subhazard.Rooms" style="margin-top:8px;">
										<label stop-event='click' class="checkbox inline" style="display:block;">
											<input type="checkbox" ng-model="room.presentInThisRoom" ng-change="handleRooms(subhazard, room)" />
											<span class="metro-checkbox">{{room.Name}}</span>
										</label>
									</li>
									<div style="clear:both"></div>
									</span>
									</ng-switch>
								</tab-heading>
								<tabset vertical="true">
				     		<tab select="toggleSubhazardState(subhazard)" ng-repeat="subhazard in subhazard.SubHazards" active="subhazard.IsActive" disabled="subhazard.disabled">
						     			<tab-heading>
							     <h4>
							     	<ng-switch on="subhazard.isPresent">
								     	<i ng-switch-when="true" class="icon-checkmark" style="color: #333;background: white;margin-top: 3px;width: 18px;"></i>
								     </ng-switch></i>
								     {{subhazard.Name}}
								</h4>
								<ng-switch on="subhazard.IsActive">
									<span ng-switch-when="true">
									<p style="margin-top:7px">Rooms:</p>
									<li class="" ng-repeat="room in subhazard.Rooms" style="margin-top:8px;">
										<label stop-event='click' class="checkbox inline" style="display:block;">
											<input type="checkbox" ng-model="room.presentInThisRoom" ng-change="handleRooms(subhazard, room)" />
											<span class="metro-checkbox">{{room.Name}}</span>
										</label>
									</li>
									<div style="clear:both"></div>
									</span>
									</ng-switch>
								</tab-heading>
								<li ng-repeat="subhazard in subhazard.SubHazards">{{subhazard.Name}}</li>
				     		</tab>
				     	</tabset>
				     		</tab>
				     	</tabset>
				 	</tab>
	 		</tabset>
		</script>

		<script type="text/ng-template" id="modal-subs.html">
			<ul class="" style="display:inline-block; margin-left:10px; width:33%;margin-bottom:25px;height:250px;overflow:scroll;">
				<li ng-repeat="bottomHazard in currentChildren"><label class="checkbox inline" style="">
					<label class="checkbox inline" style="">
						<input type="checkbox" ng-model="bottomHazard.containsHotChildren" ng-change="subhazardChecked(bottomHazard, $event)" />
						<span id= class="metro-checkbox smaller">{{bottomHazard.Name}}</span>
					</label>
				</li>
			</ul>
		</script>

	    <script type="text/ng-template" id="sub-hazards.html">
	    <li>
	 		<ul class="subHazards">
				<li data-ng-repeat="subhazard in subhazard.SubHazards  | filter: { isPresent: 'true' }">
							<h4>
								<label class="checkbox inline">
									<input type="checkbox" ng-model="subhazard.isPresent" ng-change="handleHazardRelationship(hazard)"	/>
									<span class="metro-checkbox">{{subhazard.Name}}</span>
								</label>
								<ng-switch on="subhazard.isLeaf">
									<span ng-switch-when="false">
										<i class="icon-plus-4 modal-trigger-plus" ng-click="openModal(subhazard)"></i>
									</span>
								</ng-switch>
							</h4>					
						<ul style="margin-bottom:3px; border-bottom:1px solid #eee;">
						<ng-switch on="subhazard.isPresent">
							<span ng-switch-when="true">
								<p style="float:left; margin-top:7px; margin-right:5px">Rooms:</p>
									<li class="roomRepeat" ng-class="{last: $last}" ng-repeat="room in subhazard.Rooms   | filter: { presentInThisRoom: 'true' }" style="float:left; margin-top:8px;">{{room.Name}}</li>
									<div style="clear:both"></div>
								<ng-switch on="subhazard.isPresent">
									<span ng-switch-when="true"><span data-ng-include="'sub-hazards.html'"></span></span>
								</ng-switch>
							</span>
						</ng-switch>
						<div style="clear:both"></div>
					</ul>
					
<!--					<ng-switch on="subhazard.containsHotChildren">
											
						<span ng-switch-when="true">							
							<span data-ng-include="'sub-hazards.html'"></span>
						</span>
					</ng-switch>

					<ng-switch on="subhazard.isPresent">
						<span ng-switch-when="true">
							<span data-ng-include="'leaf-level-hazard.html'"></span>
						</span>
					</ng-switch>

					<ng-switch on="hazard.checked">
			        <span ng-switch-when="true">-->
						<!-- next level hazards
						<br>{{hazard.checked}}
						<span data-ng-include="'common_template'"></span>
				    </ng-switch>-->
				</li>
				<!--<button ng-click="modalHider($hasThisHazard)">confirm</button>-->
			</ul>
		</li>
	    </script>


	    <script type="text/ng-template" id="leaf-level-hazard.html">
	    	<label class="checkbox inline">
				<input type="checkbox" ng-model="subhazard.isPresent" ng-change="handleHazardRelationship(subhazard)" />
				<span class="metro-checkbox">{{subhazard.Name}}</span>
			</label>
			<p style="float:left; margin-top:7px; margin-right:5px">Rooms:</p>
	    	<ul>
				<li class="roomRepeat" ng-class="{last: $last}" ng-repeat="room in subhazard.Rooms" style="float:left; margin-top:8px;">{{room.room}}</li>
	    	</ul>
	    </script>

	    <div data-ng-controller="hazardAssessmentController">
	 <!--
	    <pre>
	    	{{hazards | json}}
	    </pre>
	-->
	    <form>
			<ul class="allHazardList">
				<li class="hazardList" data-ng-repeat="hazard in hazards">
					<h1 class="hazardListHeader" id="{{hazard.cssId}}">{{hazard.Name}}</h1>
					<hr>
					<ul>
						<li ng-repeat="subhazard in hazard.SubHazards">
							<!--<ul ng-switch on="subhazard.containsHotChildren">
								<li ng-switch-when="true">	-->					
									<ul class="topSub">
										<h4>
											<ng-switch on="subhazard.isLeaf">
												<span ng-switch-when="false">
													<label class="checkbox inline">
														<input type="checkbox" ng-model="subhazard.isPresent" ng-change="openModal(subhazard)"	/>
														<span class="metro-checkbox"><h4>{{subhazard.Name}}	</h4></span>
													</label>
													<i class="icon-plus-4 modal-trigger-plus" ng-click="openModal(subhazard)"></i>
												</span>
												<span ng-switch-when="true">
													<label class="checkbox inline">
														<input type="checkbox" ng-model="subhazard.isPresent" ng-change="setRooms(subhazard)"	/>
														<span class="metro-checkbox"><h4>{{subhazard.Name}}	</h4></span>
													</label>
												</span>
											</ng-switch>
										</h4>
										<ng-switch on="subhazard.isPresent">
											<span ng-switch-when="true">
												<p style="float:left; margin-top:7px; margin-right:5px">Rooms:</p>
													<li class="roomRepeat" ng-class="{last: $last}" ng-repeat="room in subhazard.Rooms   | filter: { presentInThisRoom: 'true' }" style="float:left; margin-top:8px;">{{room.Name}}</li>
													<div style="clear:both"></div>
												<ng-switch on="subhazard.isPresent">
													<span ng-switch-when="true"><span data-ng-include="'sub-hazards.html'"></span></span>
												</ng-switch>
											</span>
										</ng-switch>
									</ul>
								</li>

							<!--	<li ng-switch-when="false">
									<label class="checkbox inline">
										<input type="checkbox" ng-model="subhazard.isPresent" ng-change="handleHazardRelationship(subhazard)" />
										<span class="metro-checkbox">{{subhazard.Name}}falsotue</span>
									</label>
								</li>
-->
							</ul>

						</li>
					</ul>
					<ng-switch on="hazard.checked">
			        <span ng-switch-when="true">						<!-- second level hazards -->
						<span data-ng-include="'common_template'"></span>
					</span>
			    </ng-switch>
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
<div id="footer" style="position:fixed; bottom:0; width:100%; background:white; left:0; z-index:10000; box-shadow:0 0 20px rgba(0,0,0,.5)">
	<ul class="container-fluid whitebg">
		<li><a><img src="../../img/clipboard.png"/><span>Archived Reports</span></a></li>
		<li><a><img src="../../img/phone.png"/><span>Laboratory Contacts</span></a></li>
		<li><a><img src="../../img/speechBubble.png"/><span>Inspection Comments</span></a></li>
		<li><a href="InspectionChecklist.php"><img src="../../img/checkmarkFooter.png"/><span>Begin Inspection</a></span></li>
	</ul>
</div>
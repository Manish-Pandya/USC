<?php
require_once '../top_view.php';
?>
<script src="../../js/emergencyInfoHub.js"></script>

<span class="whiteBg" id="buildingHub"  ng-app="emergencyInfo" >
	<div class="navbar">
		<ul class="nav pageMenu row-fluid orangeBg">
			<li class="span12">			
				<h2 style="padding: 11px 0 5px 0; font-weight:bold; text-align:center">
					<img src="../../img/hazard-icon.png"  style="height:50px" />
					Emergency Infomration
					<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
				</h2>
			</li>
		</ul>
		<div style=>&nbsp;</div>
	</div>
	<div class="whiteBg" style="margin-top:-40px;">
		<span ng-controller="emergencyInfoController" id="emergency-info">
			<div id="buildings">
				
				<form class="row form-inline" style="margin-left:0">
					<label>Building</label>
					<input ng-if="Buildings" style="" type="text" typeahead-on-select='onSelectBuilding($item, $model, $label)' ng-model="selectedBuilding" placeholder="Select a Building" typeahead="building as building.Name for building in Buildings | filter:$viewValue">
					<input ng-if="!Buildings" style="" type="text" disabled="disabled" placeholder="Getting buildings...">
			       	<img ng-if="!Buildings" class="" style="height: 23px; margin: 3px 0 0 -60px; position: absolute;" src="<?php echo WEB_ROOT?>img/loading.gif"/>
					<label>Room</label>
					<input ng-if="building" style="" type="text" typeahead-on-select='onSelectRoom($item, $model, $label)' ng-model="selectedRoom" placeholder="Select a Room" typeahead="rooms as room.Name for room in building.Rooms | filter:$viewValue2">
					<input ng-if="!building" placeholder="Select a Building" disabled="disabled">

				</form>
				<h2 ng-if="building">Select a room to display EMERGENCY INFORMATION for {{building.Name}}</h2>
				<div class="roomDisplay" ng-if="room">
					<h2>Room {{room.Name}}</h2>
					
					<h3 style="text-decoration:underline; margin-top:20px;">EMERGENCY CONTACTS</h3>
					<ul>
						<li style="margin:10px auto; width:500px; list-style:none" class="lefterizer row" ng-repeat="pi in room.PrincipalInvestigators">
							<span style="width:200px; float:left; text-decoration:underline;">Principal Investigator:</span> <span>{{pi.User.Name}}</span><br>
							<span style="width:150px; float:left; text-decoration:underline;">Emergency Phone: </span><span style="width:265px; margin-left:-16px;">{{pi.User.Emergency_phone}}</span><br>
							<span style="float:left; width:160px; text-decoration:underline;">Department(s)</span><span><ul><li class="offset2" ng-repeat="department in pi.Departments">{{department.Name}}</li></ul></span>
						</li>

					</ul>

					<h3 style="text-decoration:underline; margin-top:20px;">Laboratory Safety Contact(s):</h3>
					<li style="margin:10px auto; width:700px; list-style:none;"  class="lefterizer row" ng-repeat="pi in room.PrincipalInvestigators">
						<ul>
							<li ng-repeat="contact in pi.LabPersonnel" style="text-align:left; list-style:none"><span><span  style="text-decoration:underline;">Name:</span><span style="width: 200px; display: inline-block;
margin-left: 10px;">{{contact.Name}}</span><span style="text-decoration:underline;">Emergency Phone</span><span style="width: 200px; display: inline-block; margin-left: 10px;">{{contact.Emergency_phone}}</span></li>
						</ul>
					</li>


					<div ng-hide="!gettingHazards" class="container loading" style="margin-left:70px; margin-top:20px;">
				      <img class="" src="../../img/loading.gif"/>
				      Building Hazard List...
				    </div>

			    <h2 ng-show="hazards" style=" margin-top:20px; text-decoration:underline">LABORATORY HAZARDS</h2>

				<ul class="allHazardList" style="width:960px; margin:30px auto;">
					<li class="hazardList" ng-if="hazard.IsPresent" ng-class="{narrow: hazard.hidden}" data-ng-repeat="hazard in hazards" style="width:350px;">
					<h1 class="hazardListHeader" id="{{hazard.cssId}}" ng-show="hazard.hidden" ng-click="hazard.hidden = !hazard.hidden">&nbsp;</h1>
					<span ng-hide="hazard.hidden">
				    <h1 ng-click="hazard.hidden = !hazard.hidden" class="hazardListHeader" id="{{hazard.cssId}}">{{hazard.Name}}</h1>
					<hr>
					<ul ng-show="hazard.SubHazards.length">
		    			<li ng-show="hazard.IsPresent" ng-repeat="hazard in hazard.SubHazards" style="font-size: 19px; margin-bottom: 15px; line-height: normal;">
		    				{{hazard.Name}} 
		    				<ul ng-show="hazard.SubHazards.length">
		    					<li ng-show="hazard.IsPresent" ng-repeat="hazard in hazard.SubHazards">({{hazard.Name}})</li>
		    				</ul>
		    			</li>
		    		</ul>
					</li>
					<div style='clear:both'>&nbsp;</div>
				</ul>
				<div style='clear:both'>&nbsp;</div>

				<!--<div class="well">{{room.Safety_contact_information}}</div>-->
					
	</div>		
</span>
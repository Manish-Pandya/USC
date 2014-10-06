<?php
require_once '../top_view.php';
?>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/piHub.js"></script>
<span ng-app="piHub" ng-controller="piHubMainController">
<div class="navbar">
<ul class="nav pageMenu bg-color-blue" style="min-height: 50px; background: #86b32d; color:white !important; padding: 4px 0 0 0; width:100%">
	<li class="span3" style="margin-left:0">
		<img src="<?php echo WEB_ROOT?>img/pi-icon.png" class="pull-left" style="height:50px" />
			<h2 style="padding: 11px 0 5px 15px; margin-left:63px;">PI Hub
			<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
		</h2>	
	</li>
	<div style="clear:both; height:0; font-size:0; ">&nbsp;</div>
</ul>
<div class="whitebg" style="padding:70px 70px;">
	<div id="editPiForm" class="">
		<form class="form">
		     <div class="control-group">
		       <label class="control-label" for="name"><h3 style="font-weight:bold">Select A Principal Investigator</h3></label>
		       <div class="controls">
		       <span ng-if="!PIs || !buildings">
		         <input class="span4" style="background:white;border-color:#999"  type="text"  placeholder="Getting PIs..." disabled="disabled">
		       	<img class="" style="height:23px; margin:-9px 0 0 -35px;" src="<?php echo WEB_ROOT?>img/loading.gif"/>
		       </span>
		       <span ng-if="PIs && buildings">
		       	<input style="" class="span4"  typeahead-on-select='onSelectPi($item, $model, $label)' type="text" ng-model="customSelected" placeholder="Select a PI" typeahead="pi as (pi.User.Name) for pi in PIs | filter:$viewValue">
		       </span>
		      </div>
		     </div>
		</form>
	</div>
	<span ng-if="PI">
		<div class="btn-group" id="piButtons" style="">
			<a href="UserHub.php#/pis?pi={{PI.User.Last_name}}piId={{PI.Key_id}}" id="editPI" class="btn btn-large btn-primary" style="margin-left: 0;"><i class="icon-pencil"></i>Edit PI</a>
			<a ng-click="setRoute('rooms')" id="editPI" class="btn btn-large btn-info"><i class="icon-enter"></i>PI's Laboratory Rooms</a>
			<a ng-click="setRoute('personnel')" class="btn btn-large btn-success"><i class="icon-user-2"></i>Manage Lab Personnel</a>
			<!--<a ng-click="setRoute('departments')" class="btn btn-large btn-primary"><i class="icon-tree-view"></i>Manage Deparments</a>-->
			<a ng-if="inspectionId" class="btn btn-large btn-danger" href="../inspection/HazardInventory.php#?inspectionId={{inspectionId}}&pi={{PI.Key_id}}">Return To Inpsection</a>
			<!--<a ng-click="setRoute('safetyContacts')" class="btn btn-large btn-success"><i class="icon-phone"></i>Manage Safety Contacts</a><!--<a href="#specialHazards" id="editPI" class="btn btn-large btn-warning">Manage Special Haz-->
		</div>
	</span>
	<h3 ng-hide="!PI" class="piHeader">Principle Investigator:  {{PI.User.Name}}</h3>
	<div class="loading" ng-show='!PI' >
		<span ng-hide="noPiSet">
		  <img class="" src="<?php echo WEB_ROOT?>img/loading.gif"/>
		  Getting Selected Principal Investigator...
		</span>
	</div>
	<span ng-hide="!PI">
		<ng-view></ng-view>
	</span>


<?php
require_once '../bottom_view.php';
?>
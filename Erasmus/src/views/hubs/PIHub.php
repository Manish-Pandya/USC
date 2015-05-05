<?php
require_once '../top_view.php';
?>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/piHub.js"></script>
<span ng-app="piHub" ng-controller="piHubMainController">
<div class="navbar">
<ul class="nav pageMenu bg-color-blue" style="min-height: 50px; background: #86b32d; color:white !important; padding: 4px 0 0 0; width:100%">
	<li class="span3" style="margin-left:0">
		<img src="<?php echo WEB_ROOT?>img/pi-icon.png" class="pull-left" style="height: 67px;margin-top: -11px;" />
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
		       		<i class="icon-spinnery-dealie spinner small asbolute" style="margin-left:-258px; margin-top:-5px;"></i>
		       </span>
		       <span ng-if="PIs && buildings">
		       	<input style="" class="span4"  typeahead-on-select='onSelectPi($item, $model, $label)' type="text" ng-model="customSelected" placeholder="Select a PI" typeahead="pi.User.Name+(pi.Is_active ? '': ' (Inactive)') as pi.User.Name+(pi.Is_active ? '': ' (Inactive)') for pi in PIs | filter:$viewValue">
		       </span>
		      </div>
		     </div>
		</form>
	</div>
	<span ng-if="PI">
		<div class="btn-group" id="piButtons" style="">
			<a href="UserHub.php#/pis?pi={{PI.Key_id}}" id="editPI" class="btn btn-large btn-primary left" style="margin-left: 0;" alt="Edit" title="Edit" title="Edit"><i class="icon-pencil"></i>Edit PI</a>
			<a ng-click="setRoute('rooms')" id="editPI" class="btn btn-large btn-info left"><i class="icon-enter"></i>PI's Laboratory Rooms</a>
			<a ng-click="setRoute('personnel')" class="btn btn-large btn-success left"><i class="icon-user-2"></i>Manage Lab Personnel</a>
			<a ng-if="inspectionId" class="btn btn-large btn-danger left" href="../inspection/HazardInventory.php#?inspectionId={{inspectionId}}&pi={{PI.Key_id}}">Return To Inspection</a>
		</div>
	</span>
	<h3 ng-hide="!PI" class="piHeader" ng-class="{'inactive': !PI.Is_active}">Principle Investigator:  {{PI.User.Name}} <span ng-if="!PI.Is_active">(Inactive)</span></h3>
	<ng-view></ng-view>


<?php
require_once '../bottom_view.php';
?>

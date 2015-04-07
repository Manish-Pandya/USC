<?php
require_once '../top_view.php';
?>
<script src="../../js/postInspection.js"></script>
<div ng-app="postInspections" ng-controller="mainController">
<div class="navbar">    		

	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="">
			<img src="../../img/checklist-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">Finalize Inspection
				<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
			</h2>	
		</li>
	</ul>
</div>

<div class="container-fluid whitebg" style="padding-top:80px; padding-bottom:30px;">
	<ul class="postInspectionNav row">
		<li><a ng-click="setRoute('/confirmation')" class="btn btn-large btn-success" ng-class="{selected: route=='/confirmation'}">Finalize Inspection</a></li>
		<li><a ng-click="setRoute('/report')" class="btn btn-large btn-info" ng-class="{selected: route=='/report'}">Inspection Report</a></li>
		<li><a ng-click="setRoute('/details')" class="btn btn-large btn-primary" ng-class="{selected: route=='/details'}">Inspection Details</a></li>
		<li><a href="InspectionChecklist.php#?inspection={{loc.inspection}}" class="btn btn-large btn-danger">Return to Inspection</a></li>
	</ul>
	<ng-view></ng-view>
</div>

</div>
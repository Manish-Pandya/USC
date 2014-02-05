<?php
require_once '../top_view.php';
?>
<script src="../../../js/postInspection.js"></script>
<div ng-app="postInspections" ng-controller="mainController">
<div class="navbar">    		

	<ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
		<li class="span12">
			<img src="../../../img/checklist-icon.png" class="pull-left" style="height:50px" />
			<h2  style="padding: 11px 0 5px 85px;">Finalize Inspection</h2>	
		</li>
	</ul>
</div>

<div class="container-fluid whitebg" style="padding-top:80px; padding-bottom:30px;">
	<ul class="postInspectionNav row">
		<li><a ng-click="setRoute('confirmation')" class="btn btn-large btn-success">Finalize Inspection</a></li>
		<li><a ng-click="setRoute('review')" class="btn btn-large btn-info">Inspection Review</a></li>
		<li><a ng-click="setRoute('details')" class="btn btn-large btn-primary">Inspection Details</a></li>
	</ul>
	<ng-view></ng-view>
</div>

</div>
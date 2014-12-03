<?php
require_once '../top_view.php';
?>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/locationHub.js"></script>
<span ng-app="locationHub" >
<div class="navbar">
	<ul class="nav pageMenu blueBg" style="min-height: 50px; color:white !important; padding: 4px 0 0 0; width:100%">
		<li class="span3" style="margin-left:0">
			<img src="<?php echo WEB_ROOT?>img/building-hub-large-icon.png" class="pull-left" style="height:50px" />
				<h2 style="padding: 11px 0 5px 15px; margin-left:63px;">Location Hub
				<a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>	
			</h2>	
		</li>
		<div style="clear:both; height:0; font-size:0; ">&nbsp;</div>
	</ul>
	<div class="whitebg" style="padding:70px 70px;" >

		<div class="btn-group" id="piButtons" ng-controller="routeCtrl" style="margin-left:-9px;">
			<a ng-click="setRoute('/rooms')" ng-class="{selected: location=='/rooms'}" id="editPI" class="btn btn-large btn-info">Manage Lab Rooms</a>
			<a ng-click="setRoute('/buildings')" ng-class="{selected: location=='/buildings'}" class="btn btn-large btn-success">Manage Buildings</a>
			<a ng-click="setRoute('/campuses')" ng-class="{selected: location=='/campuses'}" class="btn btn-large btn-primary">Manage Campuses</a>
		</div>

		<span ng-hide="locations">
			<ng-view></ng-view>
		</span>
	</div>
</span>

<?php
require_once '../bottom_view.php';
?>
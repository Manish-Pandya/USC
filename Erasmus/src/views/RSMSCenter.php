<?php
require_once 'top_view.php';
?>

<div class="row-fluid">

	<div class="span2 rsmsCenterIconContainer offset3" style=" margin-bottom:30px;">
		<a href="hubs/HazardHub.php" class="span12" style="background: #e67e1d;">
			<img class="" src="../img/hazard-icon.png" />
			<p>Hazard Hub</p>
		</a>
	</div>
	
	<div class="span2 rsmsCenterIconContainer" style=" margin-bottom:30px;">
		<a href="hubs/UserHub.php" class="span12 bg-color-green " style="background: #86b32d;">
			<img class="" src="../img/user-icon.png" />
			<p>User Hub</p>
		</a>
	</div>

	<div class="span2 rsmsCenterIconContainer" style=" margin-bottom:30px;">
		<a href="hubs/PIHub.php" class="span12 bg-color-blue" >
			<img class="pull-right" src="../img/pi-icon.png" />
			<p>PI Hub</p>
		</a>
	</div>
	
</div>

<div class="row-fluid">

	<div class="span2 rsmsCenterIconContainer offset3" style=" ">
		<a href="hubs/BuildingHub.php" class="span12" style="background: #49afcd;">
			<img class="" src="../img/building-hub-large-icon.png" />
			<p>Building Hub</p>
		</a>
	</div>

	<div class="span2 rsmsCenterIconContainer" style=" ">
		<a href="inspection/NewInspection.php" class="span12" style="background: #d00;">
			<img class="" src="../img/new-inspection-icon.png" />
			<p>New Inspection</p>
		</a>
	</div>
</div>
<?php 
require_once 'bottom_view.php';
?>
<?php
require_once 'top_view.php';
?>

<script>
	window.RSMSCenterConfig = {};
	<?php
	if( ApplicationConfiguration::get("module.Core.feature.impersonation", false) ){
		echo "window.RSMSCenterConfig.impersonation = true;"; 
	}
	?>
</script>

<script src="../js/homeApp.js"></script>
<div ng-app="homeApp" ng-controller="testController">
	<ng-view></ng-view>
</div>

<?php 
require_once 'bottom_view.php';
?>
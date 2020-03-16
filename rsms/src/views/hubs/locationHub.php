<?php
require_once '../top_view.php';
require_once '../../includes/modules/lab-inspection/js/room-type-constants.js.php';
?>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/location-hub.css"/>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/locationHub.js"></script>

<span ng-app="locationHub" id="location-hub" class="hub-theme-blue" ng-controller="routeCtrl">

    <hub-banner-nav
        hub-title="Location Hub"
        hub-subtitle="Room, Building, and Campus Management"
        hub-image="<?php echo WEB_ROOT?>img/building-hub-large-icon.png"
        hub-views="locationHubViews">
    </hub-banner-nav>

    <div class="spacer"></div>

    <span ng-hide="locations">
        <ng-view></ng-view>
    </span>
</span>

<?php
require_once '../bottom_view.php';
?>

<?php
require_once '../top_view.php';
require_once '../../includes/modules/lab-inspection/js/room-type-constants.js.php';
?>
<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/location-hub.css"/>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/locationHub.js"></script>

<span ng-app="locationHub" id="location-hub" class="hub-theme-blue">
    <div class="hub-banner no-print">
        <img class="title-icon" src="<?php echo WEB_ROOT?>img/building-hub-large-icon.png" />

        <span style="flex-direction: column; align-items: flex-start;">
            <h1>Location Hub</h1>
            <h4>Room, Building, and Campus Management</h4>
        </span>

        <ul class="banner-nav" ng-controller="routeCtrl">
            <li><a class="" ng-click="setRoute('/rooms')" ng-class="{'active-nav': location=='/rooms'}" >All Rooms</a></li>
            <li><a class="" ng-click="setRoute('/rooms/research-labs')" ng-class="{'active-nav': location=='/rooms/research-labs'}" >Research Labs</a></li>
            <li><a class="" ng-click="setRoute('/rooms/animal-facilities')" ng-class="{'active-nav': location=='/rooms/animal-facilities'}" >Animal Facilities</a></li>
            <li><a class="" ng-click="setRoute('/rooms/teaching-labs')" ng-class="{'active-nav': location=='/rooms/teaching-labs'}" >Teaching Labs</a></li>
            <li><span>|</span></li>
            <li><a class="" ng-click="setRoute('/buildings')" ng-class="{'active-nav': location=='/buildings'}" >Buildings</a></li>
            <li><a class="" ng-click="setRoute('/campuses')" ng-class="{'active-nav': location=='/campuses'}" >Campuses</a></li>

            <li>
                <a class="home-link" href="<?php echo WEB_ROOT;?>">
                    <i class="icon-home"></i>
                </a>
            </li>
        </ul>
    </div>

    <div class="spacer"></div>

    <span ng-hide="locations">
        <ng-view></ng-view>
    </span>
</span>

<?php
require_once '../bottom_view.php';
?>

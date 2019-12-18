<?php
require_once '../top_view.php';
require_once '../../includes/modules/lab-inspection/js/room-type-constants.js.php';
?>
<script type="text/javascript" src="<?php echo WEB_ROOT?>js/locationHub.js"></script>
<style>
    /* Location Hub - Rooms: Table column sizing */
    .locationTable tr .lhcol_edit { width:7%; }
    .locationTable tr .lhcol_building { width:18%; }
    .locationTable tr .lhcol_name { width:10%; }
    .locationTable tr .lhcol_type { width:2%; }
    .locationTable tr .lhcol_hazards { width:10%; }
    .locationTable tr .lhcol_purpose { width:10%; }
    .locationTable tr .lhcol_pidept { width:32%; }

    .locationTable tr .lhcol_campus { width:12%; }

    .lhcol_pidept .pi_filters span label { display: inline; }
    .lhcol_pidept .pi_filters span input { width: inherit !important; }
    .lhcol_pidept .pi_filters span { margin-right: 10%; }
    .lhcol_pidept .pi_search input { max-width: 250px; }

    h1 .room-type-icon {
        width: 25px;
        padding-right: 10px;

        /* Apply same opacity as 'grayed-out' class,
            but force color to black to treat icons
            and images the same */
        color: black;
        filter: grayscale(100%);
        opacity: .4;
    }

    h1 .room-type-icon i {
        width: 0.8em;
        font-size: 1em;

        /* Remove vertical alignment from bootstrap icon styling
            to avoid dancing to make centered icons */
        vertical-align: unset;
    }

    .locationTable {
        max-width: 100%;
        overflow-x: scroll;
    }

    .locationTable th h1 {
        display: inline-block;
    }

    .locationTable tr {
        max-width: 100%;
        border: none;
    }

    .locationTable tr th {
        font-size: .9vw;
    }

    .locationTable tr td {
        /*width: 1%;*/
        transition: all .3s;
        font-size: 16px;
    }

    .locationTable tr td ul {
        list-style: none;
    }

    .locationTable tr td li i:not(.icon-arrow-down) {
        /*width: auto;*/
        margin: 1px 0 10px 0px;
        cursor: pointer;
    }

    .locationTable tr td li i.icon-minus {
        color: #bd362f;
    }

    .locationTable tr td li.add-role {
        color: #5bb75b;
    }

    .locationTable tr td:last-child {
        /*width: 4%;*/
    }
</style>

<span ng-app="locationHub">
    <div class="hub-banner no-print blueBg">
        <img class="title-icon" src="<?php echo WEB_ROOT?>img/building-hub-large-icon.png" />

        <h1>Location Hub</h1>

        <ul class="banner-nav" ng-controller="routeCtrl">
            <li><a class="" ng-click="setRoute('/rooms')" ng-class="{selected: location=='/rooms'}" >All Rooms</a></li>
            <li><a class="" ng-click="setRoute('/rooms/research-labs')" ng-class="{selected: location=='/rooms/research-labs'}" >Research Labs</a></li>
            <li><a class="" ng-click="setRoute('/rooms/animal-facilities')" ng-class="{selected: location=='/rooms/animal-facilities'}" >Animal Facilities</a></li>
            <li><a class="" ng-click="setRoute('/rooms/teaching-labs')" ng-class="{selected: location=='/rooms/teaching-labs'}" >Teaching Labs</a></li>
            <li>|</li>
            <li><a class="" ng-click="setRoute('/buildings')" ng-class="{selected: location=='/buildings'}" >Buildings</a></li>
            <li><a class="" ng-click="setRoute('/campuses')" ng-class="{selected: location=='/campuses'}" >Campuses</a></li>
        </ul>

        <a class="home-link" href="<?php echo WEB_ROOT;?>">
            <i class="icon-home"></i>
        </a>
    </div>

    <div class="spacer"></div>

    <span ng-hide="locations">
        <ng-view></ng-view>
    </span>
</span>

<?php
require_once '../bottom_view.php';
?>

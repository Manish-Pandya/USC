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
    .locationTable tr .lhcol_pidept { width:34%; }

    .locationTable tr .lhcol_campus { width:12%; }

    .lhcol_pidept .pi_filters span label { display: inline; }
    .lhcol_pidept .pi_filters span input { width: inherit !important; }
    .lhcol_pidept .pi_filters span { margin-right: 10%; }
    .lhcol_pidept .pi_search input { max-width: 250px; }

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

<span ng-app="locationHub" >
<div class="navbar">
    <ul class="nav pageMenu blueBg" style="min-height: 50px; color:white !important; padding: 4px 0 0 0; width:100%">
        <li class="span3" style="margin-left:0">
            <img src="<?php echo WEB_ROOT?>img/building-hub-large-icon.png" class="pull-left" style="height:50px" />
                <h2 style="padding: 11px 0 5px 15px;">Location Hub
                <a style="float:right;margin: 11px 28px 0 0;" href="<?php echo WEB_ROOT;?>"><i class="icon-home" style="font-size:40px;"></i></a>
            </h2>
        </li>
        <div style="clear:both; height:0; font-size:0; ">&nbsp;</div>
    </ul>
</div>
        <div class="btn-group" id="piButtons" ng-controller="routeCtrl">
            <a class="btn btn-large btn-info" ng-click="setRoute('/rooms')" ng-class="{selected: location=='/rooms'}" >All Rooms</a>
            <a class="btn btn-large btn-info" ng-click="setRoute('/rooms/research-labs')" ng-class="{selected: location=='/rooms/research-labs'}" >Research Labs</a>
            <a class="btn btn-large btn-info" ng-click="setRoute('/rooms/animal-facilities')" ng-class="{selected: location=='/rooms/animal-facilities'}" >Animal Facilities</a>
            <a class="btn btn-large btn-info" ng-click="setRoute('/rooms/teaching-labs')" ng-class="{selected: location=='/rooms/teaching-labs'}" >Teaching Labs</a>
            <a class="btn btn-large btn-info" ng-click="setRoute('/buildings')" ng-class="{selected: location=='/buildings'}" >Buildings</a>
            <a class="btn btn-large btn-info" ng-click="setRoute('/campuses')" ng-class="{selected: location=='/campuses'}" >Campuses</a>
        </div>

        <span ng-hide="locations">
            <ng-view></ng-view>
        </span>
</span>

<?php
require_once '../bottom_view.php';
?>

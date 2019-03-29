<?php
require_once '../top_view.php';
require_once '../../RequireUserLoggedIn.php';
?>
<script src="../../js/lab/myLab.js"></script>
<script src="widgets/my-lab-widget.js"></script>

<style>
    .banner {
        padding: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.7);
        color:white;
    }

    .title-icon {
        margin: 5px 0 0 5px;
        font-size: 35px;
        width: auto;
        line-height: 43px;
    }

    .rsms-home-icon {
        float:right;
        margin: 15px 30px 0 0;
        color:white;
    }

    .rsms-home-icon i {
        font-size:40px;
    }

    ul.banner-nav {
        float: right;
        margin-top: -30px;
        max-width: 50%;
    }

    ul.banner-nav li {
        display:inline-block;
        margin-right:10px;
    }

    ul.banner-nav li a {
        font-weight: bold;
        font-size: 12px;
        color: #fff;
        display: block;
    }

    ul.banner-nav li a:hover {
        color: black;
    }

    my-lab-widget {
        display: block;
    }

    .widget-group {}

    .widgets-container {
        display: flex;
        flex-flow: row wrap;
        justify-content: space-between;
    }

    .widget {
        width: 49%;
        position: relative;
    }

    .widget.full {
        width: 100%;
    }

    .widget .widget-header {
        padding-bottom: 20px;
    }

    [class^="icon-"] {
        vertical-align: initial;
    }

    /* widget-specific styles */
    .widget input,
    .widget select {
        width: 100%;
    }

    .widget .content-container {
        /* Leave room for toolbar */
        margin-bottom: 70px;
    }

    .widget .toolbar-container {
        position: absolute;
        bottom: 10;
        right: 10;
        width: 95%;
        min-height: 70px;

    }

    .saving .toolbar-container {
        display:none;
    }

    .widget .toolbar-container .toolbar {
        text-align: right;
        position: absolute;
        bottom: 0;
        right: 0;
        left: 0;
    }

</style>

<div ng-app="myLab" ng-controller="myLabController">
    <div cg-busy="{promise:inspectionPromise,message:'Loading', backdrop:true,templateUrl:'../../rad/views/busy-templates/full-page-busy.html'}"></div>

    <div class="banner bg-color-greendark">
        <h1>
            My Laboratory
        </h1>
        <ul class="banner-nav">
        </ul>
    </div>

    <div ng-if="AllAlerts.length" class="alert alert-info my-lab-alert">
        <ul ng-repeat="widget in MyLabWidgets">
            <li ng-repeat="alert in widget.Alerts">
                <a ng-href="#{{widget.Group}}">{{alert}}</a>
            </li>
        </ul>
    </div>

    <div id="{{group}}" class="widget-group well full" ng-repeat="(group, widgets) in MyLabWidgets | groupBy: 'Group'">
        <h3 ng-show="false" ng-if="group && group != 'null'">{{group}}</h3>
        <div class="widgets-container full">
            <my-lab-widget ng-repeat="widget in widgets"
                api="widget_functions"
                widget="widget"
                content-template-name="{{widget.Template}}"
                data="widget.Data"
                alerts="widget.Alerts"
                full-width="widget.FullWidth"
                group-name="{{widget.Group}}"
                header-text="{{widget.Title}}"
                header-icon="{{widget.Icon}}"
                header-image="{{widget.Image}}">
            </my-lab-widget>
        </div>
    </div>
</div>

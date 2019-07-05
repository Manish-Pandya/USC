<?php
require_once '../top_view.php';
require_once '../../RequireUserLoggedIn.php';
?>
<script src="../../js/lab/myLab.js"></script>
<script src="widgets/my-lab-widget.js"></script>

<link type="text/css" rel="stylesheet" href="<?php echo WEB_ROOT?>stylesheets/mylab.css"/>

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
                stretch-content="widget.StretchContent"
                group-name="{{widget.Group}}"
                header-text="{{widget.Title}}"
                subheader-text="{{widget.Subtitle}}"
                header-icon="{{widget.Icon}}"
                header-image="{{widget.Image}}">
            </my-lab-widget>
        </div>
    </div>
</div>

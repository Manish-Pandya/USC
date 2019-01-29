<?php
require_once '../top_view.php';
?>
<script src="../../js/lab/myLab.js"></script>


<div ng-app="myLab" ng-controller="myLabController">
    <h1>
        My Laboratory
        <a href="../../rad/#/my-lab{{pi.Key_id}}" class="btn">
            <img src="../../img/radiation-large-icon.png" style="margin-right:5px;" />My Rad Lab
        </a>
    </h1>
    <div cg-busy="{promise:inspectionPromise,message:'Loading', backdrop:true,templateUrl:'../../rad/views/busy-templates/full-page-busy.html'}"></div>
    <div class="alert alert-info my-lab-alert">
        <h3>Your Annual Verification is due {{pi.Verifications[0].Due_date | dateToISO}}<a class="btn btn-large" style="margin: 0 10px;font-size: 18px;" href="../../verification/">View Verification</a></h3>
    </div>

    <div style="float: left; width: 100%;">
        <div class="well half" ng-if="rbf.getHasPermission([ R[Constants.ROLE.NAME.DEPARTMENT_CHAIR] ])">
            <h2><i style="margin-top: -5px;font-size: 22px;margin-right: 4px;" class="icon-clipboard-2"></i>Summary Reports</h2>
            <a class="btn btn-large blueBg" href="../../reports">View your Department's Inspection Summary Reports</a>
        </div>
    </div>

    <div class="well half">
        <h2><i style="margin-top: -5px;font-size: 22px;margin-right: 4px;" class="icon-search-2"></i>Pending Reports</h2>
        <h3 style="margin-top:10px;" ng-show="!openInspections.length">No pending reports at this time.</h3>
        <div class="fake-table" ng-show="openInspections.length">
            <div class="table-header">
                <h3>Inspection Date</h3>
                <h3>Inspector(s)</h3>
                <h3>Report</h3>
            </div>
            <div class="table-row" ng-repeat="inspection in openInspections = (pi.Inspections | openInspections)">
                <div>{{inspection.Date_started | dateToISO}}</div>
                <div>
                    <span ng-repeat="inspector in inspection.Inspectors">{{inspector.Name}}<span ng-if="!$last">, </span></span>
                </div>
                <div>
                    <a class="btn btn-info left" href="../inspection/InspectionConfirmation.php#/report?inspection={{inspection.Key_id}}"><i class="icon-clipboard-2"></i>Inspection Report</a>
                </div>
            </div>
        </div>
    </div>

    <div class="well half">
        <h2><i style="margin-top: -5px;font-size: 22px;margin-right: 4px;" class="icon-search-2"></i>Archived Reports</h2>
        <h3 style="margin-top:10px;" ng-show="!closedInspections.length">No archived reports at this time.</h3>
        <div class="fake-table" ng-show="closedInspections.length">
            <div class="table-header">
                <h3>Inspection Date</h3>
                <h3>Inspector(s)</h3>
                <h3>Report</h3>
            </div>
            <div class="table-row" ng-repeat="inspection in closedInspections = (pi.Inspections | closedInspections)">
                <div>{{inspection.Date_started | dateToISO}}</div>
                <div>
                    <span ng-repeat="inspector in inspection.Inspectors">{{inspector.Name}}<span ng-if="!$last">, </span></span>
                </div>
                <div>
                    <a class="btn btn-info left" href="../inspection/InspectionConfirmation.php#/report?inspection={{inspection.Key_id}}"><i class="icon-clipboard-2"></i>Inspection Report</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once '../top_view.php';
?>
<style>
    .hidey-thing{height:150px !important;}
</style>
<script src="../../js/manageInspections.js"></script>
<div ng-app="manageInspections" ng-controller="manageInspectionCtrl">
<div class="alert savingBox" ng-if="saving">
  <h1>
      <i style="color:white" class="icon-spinnery-dealie spinner large"></i>
      <span style="margin-left: 13px;display: inline-block;">Scheduling Inspection...</span>
  </h1>
</div>
<div class="navbar fixed">
    <ul class="nav pageMenu" style="min-height: 50px; background: #d00; color:white !important; padding: 2px 0 2px 0; width:100%">
        <li class="">
            <img src="../../img/manage-inspections-icon.png" class="pull-left" style="height:50px" />
            <h2  style="padding: 11px 0 5px 0px;">Manage Inspections
                <a style="float:right;margin: 11px 28px 0 0;" href="../RSMSCenter.php"><i class="icon-home" style="font-size:40px;"></i></a>
                <span style="float:right;" ng-if="filtered">{{filtered.length}} Inspections Displayed</span>
            </h2>
        </li>
    </ul>
</div>
    <div class="loading" ng-if="loading" style="position:fixed; margin-top:70px">
      <i class="icon-spinnery-dealie spinner large"></i>
      <span>Loading...</span>
    </div>
    <select ng-model="selectedYear" ng-if="dtos" ng-change="selectYear()" ng-options="year.Name for year in years" style="z-index: 1060;margin-top: -30px;position:fixed;">
          <option value="">-- select year --</option>
      </select>

    <table class="table table-striped table-bordered userList" scroll-table watch="filtered.length" style="margin-top:100px;">
        <thead>
            <tr><th colspan="7" style="padding:0"></th></tr>
            <tr>
                <th>
                    Investigator<br>
                    <input class="span2" ng-model="search.pi" placeholder="Filter by PI"/>
                </th>
                <th>
                    Campus<br>
                    <input class="span2" ng-model="search.campus" placeholder="Filter by Campus"/>
                </th>
                <th>
                    Building<br>
                    <input class="span2" ng-model="search.building" placeholder="Filter by Building"/>
                </th>
                <th>
                    Lab Room(s)<br>
                </th>
                <th>
                    Month Scheduled<br>
                    <input class="span2" ng-model="search.date" placeholder="Filter by Date"/>
                </th>
                <th>
                    EHS Inspector<br>
                    <input class="span2" ng-model="search.inspector" placeholder="Filter by Inspector"/>
                </th>
                <th>
                    Status<br>
                    <select ng-model="search.status" style="margin-bottom:0">
                        <option>Select a status</option>
                        <option value="{{Constants.INSPECTION.STATUS.NOT_SCHEDULED}}">{{Constants.INSPECTION.STATUS.NOT_SCHEDULED}}</option>
                        <option value="{{Constants.INSPECTION.STATUS.SCHEDULED}}">{{Constants.INSPECTION.STATUS.SCHEDULED}}</option>
                        <option value="{{Constants.INSPECTION.STATUS.PENDING}}">{{Constants.INSPECTION.STATUS.PENDING}}</option>
                        <option value="{{Constants.INSPECTION.STATUS.OVERDUE_FOR_INSPECTION}}">{{Constants.INSPECTION.STATUS.OVERDUE_FOR_INSPECTION}}</option>
                        <option value="{{Constants.INSPECTION.STATUS.COMPLETE}}">{{Constants.INSPECTION.STATUS.COMPLETE}}</option>
                    </select>
                </th>
            </tr>
        </thead>
        <tbody>

            <tr ng-repeat="dto in (filtered = (dtos | genericFilter:search:convenienceMethods))" ng-class="{inactive: dto.Inspections.Status.indexOf(Constants.INSPECTION.STATUS.OVERDUE_CAP)>-1 || dto.Inspections.Status.indexOf(Constants.INSPECTION.STATUS.OVERDUE_FOR_INSPECTION)>-1 ,'pending':dto.Inspections.Status==Constants.INSPECTION.STATUS.CLOSED_OUT && !dto.Inspections.Cap_complete,'complete':dto.Inspections.Status==Constants.INSPECTION.STATUS.CLOSED_OUT && dto.Inspections.Cap_complete}">
                <td><span once-text="dto.Pi_name"></span></td>
                <td><span once-text="dto.Campus_name"></span></td>
                <td><span once-text="dto.Building_name"></span></td>
                <td>
                    <ul ng-if="!dto.Inspection_rooms">
                        <li ng-repeat="room in dto.Building_rooms"><span once-text="room.Name"></span></li>
                    </ul>
                    <ul ng-if="dto.Inspection_rooms">
                        <li ng-repeat="room in dto.Inspection_rooms"><span once-text="room.Name"></span></li>
                    </ul>
                </td>
                <td>
                    <span ng-if="!dto.Inspection_id && rbf.getHasPermission([ R[Constants.ROLE.NAME.SAFETY_INSPECTOR],  R[Constants.ROLE.NAME.RADIATION_INSPECTOR]])">
                        {{Constants.INSPECTION.STATUS.NOT_SCHEDULED}}
                    </span>
                    <span ng-if="dto.Inspection_id">
                        <span ng-if="dto.Inspections.Date_started">
                            <span ng-repeat="month in months" ng-if="month.val==dto.Inspections.Schedule_month">{{month.string}}</span>
                        </span>
                        <select ng-if="!dto.Inspections.Date_started && rbf.getHasPermission([ R[Constants.ROLE.NAME.ADMIN],  R[Constants.ROLE.NAME.RADIATION_ADMIN]])" ng-model="dto.Schedule_month" ng-change="mif.scheduleInspection( dto, selectedYear )" >
                            <option value="">-- select month --</option>
                            <option ng-selected="month.val==dto.Inspections.Schedule_month" ng-repeat="month in months" value="{{month.val}}">{{month.string}}</option>
                        </select>
                    </span>

                    <select ng-if="!dto.Inspection_id && rbf.getHasPermission([ R[Constants.ROLE.NAME.ADMIN],  R[Constants.ROLE.NAME.RADIATION_ADMIN]])" ng-model="dto.Schedule_month" ng-change="mif.scheduleInspection( dto, selectedYear )" ng-options="month.val as month.string for month in months">
                        <option value="">-- select month --</option>
                    </select>

                </td>
                <td>
                    <select ng-model="dto.selectedInspector" ng-if="rbf.getHasPermission([ R[Constants.ROLE.NAME.ADMIN],  R[Constants.ROLE.NAME.RADIATION_ADMIN]]) && (!dto.Inspections || !dto.Inspections.Inspectors.length || dto.replaceInspector)" ng-change="mif.scheduleInspection( dto, selectedYear, dto.selectedInspector )">
                        <option value="">-- Select inspector --</option>
                        <option ng-repeat="inspector in inspectors" value="{{$index}}">{{inspector.User.Name}}</option>
                    </select>

                    <ul ng-if="dto.Inspections.Inspectors">
                        <li ng-repeat="inspector in dto.Inspections.Inspectors">
                            <span ng-if="!inspector.edit" once-text="inspector.User.Name"></span>
                            <span ng-if="inspector.edit && dtoCopy && rbf.getHasPermission([ R[Constants.ROLE.NAME.ADMIN],  R[Constants.ROLE.NAME.RADIATION_ADMIN]])">
                                <select ng-model="dtoCopy.replacementInspector" ng-change="mif.replaceInspector( dto, selectedYear, $index, dtoCopy.replacementInspector, inspector)">
                                    <option value="" disabled selected>Select an Inspector</option>
                                    <option ng-selected="innerInspector.Key_id == inspector.Key_id" ng-repeat="innerInspector in inspectors | onlyUnselected:dto.Inspections.Inspectors" value="{{innerInspector}}">{{innerInspector.User.Name}}</option>
                                </select>
                                <i class="icon-cancel-2 danger" style="margin-top:-1px;" ng-click="mif.cancelEditInspector(inspector)"></i>
                            </span>
                            <span ng-if="!inspector.edit && rbf.getHasPermission([ R[Constants.ROLE.NAME.ADMIN],  R[Constants.ROLE.NAME.RADIATION_ADMIN]])">
                                <i class="icon-pencil primary" title="Edit" title="Edit" ng-click="mif.editInspector(inspector, dto)"></i>
                                <i class="icon-remove danger" title="Remove" title="Remove" ng-click="mif.removeInspector(dto, selectedYear, inspector)"></i>
                                <i ng-if="$last" title="Add" alt="Add" class="icon-plus-2 success" ng-click="dto.addInspector = true"></i></a>
                            </span>
                        </li>
                        <li ng-if="dto.addInspector && rbf.getHasPermission([ R[Constants.ROLE.NAME.ADMIN],  R[Constants.ROLE.NAME.RADIATION_ADMIN]])">
                            <select ng-model="dto.addedInspector" ng-change="mif.addInspector( dto, selectedYear, dto.addedInspector )">
                                <option value="" disabled selected>Add an Inspector</option>
                                <option ng-repeat="innerInspector in inspectors | onlyUnselected:dto.Inspections.Inspectors" value="{{innerInspector}}">{{innerInspector.User.Name}}</option>
                            </select>
                            <i class="icon-cancel-2 danger" ng-click="dto.addInspector = false"></i>
                        </li>
                    </ul>
                    <span ng-if="!dto.Inspections.Inspectors && rbf.getHasPermission([ R[Constants.ROLE.NAME.SAFETY_INSPECTOR],  R[Constants.ROLE.NAME.RADIATION_INSPECTOR]])">
                        {{Constants.INSPECTION.STATUS.NOT_ASSIGNED}}
                    </span>

                </td>
                <td>
                    <span ng-if="!dto.Inspection_id">{{Constants.INSPECTION.STATUS.NOT_SCHEDULED}}</span>
                    <span ng-if="dto.Inspections.Status">
                        <span once-text="dto.Inspections.Status"></span>
                        <!--
                            {{dto.Inspections.Date_started}}<br>{{dto.Inspections.Key_id}}<br>
                        -->
                        <span ng-if="dto.Inspections.Status == Constants.INSPECTION.STATUS.SCHEDULED">
                                ({{dto.Inspections.Schedule_month | getMonthName}})
                        </span>

                        <span ng-if="dto.Inspections.Status == Constants.INSPECTION.STATUS.PENDING_CLOSEOUT">
                            <p>
                                (Report Sent: {{dto.Inspections.Notification_date | dateToISO | date:"MM/dd/yy"}})
                                <a target="_blank" style="margin-top: -4px; margin-left: 6px;padding: 4px 7px 6px 0px;" class="btn btn-info" href="InspectionConfirmation.php#/report?inspection={{dto.Inspections.Key_id}}"><i style="font-size: 21px;" class="icon-clipboard-2"></i></a>
                            </p>
                        </span>
                        <span ng-if="dto.Inspections.Status == Constants.INSPECTION.STATUS.CLOSED_OUT">
                            <p>
                                (CAP Submitted: {{dto.Inspections.Cap_submitted_date | dateToISO | date:"MM/dd/yy"}})
                                <a target="_blank" style="margin-top: -4px; margin-left: 6px;padding: 4px 7px 6px 0px;" class="btn btn-info" href="InspectionConfirmation.php#/report?inspection={{dto.Inspections.Key_id}}"><i style="font-size: 21px;" class="icon-clipboard-2"></i></a>
                            </p>
                        </span>
                        <span ng-if="dto.Inspections.Status == Constants.INSPECTION.STATUS.INCOMPLETE_REPORT">
                            <p>
                                (Started :{{dto.Inspections.Date_started | dateToISO | date:"MM/dd/yy"}})
                                <a target="_blank" style="margin-top: -4px; margin-left: 6px;padding: 4px 7px 6px 0px;" class="btn btn-danger" href="InspectionChecklist.php#?inspection={{dto.Inspections.Key_id}}"><i style="font-size:21px;margin:3px 2px 0" class="icon-zoom-in"></i></a>
                            </p>
                        </span>
                        <span ng-if="dto.Inspections.Status == Constants.INSPECTION.STATUS.OVERDUE_CAP || dto.Inspections.Status == Constants.INSPECTION.STATUS.PENDING_EHS_APPROVAL">
                            <span><br>(Due Date:{{dto.Inspections.Date_started | getDueDate | date:"MM/dd/yy"}})</span>
                            <a target="_blank" style="margin-top: -4px; margin-left: 6px;padding: 4px 7px 6px 0px;" class="btn btn-info" href="InspectionConfirmation.php#/report?inspection={{dto.Inspections.Key_id}}"><i style="font-size: 21px;"  class="icon-clipboard-2"></i></a>
                        </span>
                        <span ng-if="dto.Inspections.Status == Constants.INSPECTION.STATUS.OVERDUE_FOR_INSPECTION">
                            <span><br>(Scheduled for {{dto.Inspections.Schedule_month | getMonthName}}, {{dto.Inspections.Schedule_year}})</span>
                        </span>
                    </span>
                    <i class="icon-spinnery-dealie spinner small" style="position:absolute;margin: 3px;" ng-if="dto.IsDirty"></i>
                </td>
            </tr>
        </tbody>
    </table>
</div>

